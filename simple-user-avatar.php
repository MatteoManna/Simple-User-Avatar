<?php
/**
 * Simple User Avatar
 *
 * Plugin Name: Simple User Avatar
 * Plugin URI: https://wordpress.org/plugins/simple-user-avatar/
 * Description: Add a <strong>user avatar</strong> using images from your Media Library.
 * Version: 2.7
 * Author: Matteo Manna
 * Author URI: https://matteomanna.com/
 * License: GPL2
 * License URI: http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain: simple-user-avatar
 */

// Injection prevention
if ( !defined( 'ABSPATH' ) )
    exit;

if ( !class_exists( 'SimpleUserAvatar' ) ) :

    class SimpleUserAvatar {

        private static $plugin_version  = '2.7';
        private static $transient_name    = 'sua_notice_is_expired';

        public static function init() {
            new self;
        }

        public function __construct() {
            // Admin scripts
            add_action( 'admin_enqueue_scripts', [ $this, 'custom_admin_enqueue_scripts' ] );

            // HTML render profile fields
            add_action( 'show_user_profile', [ $this, 'render_custom_user_profile_fields' ] );
            add_action( 'edit_user_profile', [ $this, 'render_custom_user_profile_fields' ] );

            // Update profile fields
            add_action( 'personal_options_update', [ $this, 'update_custom_user_profile_fields' ] );
            add_action( 'edit_user_profile_update', [ $this, 'update_custom_user_profile_fields' ] );

            // HTML render of notice
            add_action( 'admin_notices', [ $this, 'custom_admin_notice' ] );

            // Post call to close notice
            add_action( 'admin_post_close_notice', [ $this, 'post_close_notice' ] );

            // Override WP function get_avatar()
            add_filter( 'get_avatar', [ $this, 'override_get_avatar' ], 5, 5 );
        }

        /**
         *
         * WP enqueue media, required
         * Then CSS and Javascript
         */
        public static function custom_admin_enqueue_scripts() {
            wp_enqueue_media();
            wp_enqueue_style( 'sua', plugins_url('css/style.css', __FILE__), [], static::$plugin_version, 'all' );
            wp_enqueue_script( 'sua', plugins_url('js/scripts.js', __FILE__), [], static::$plugin_version, true );
        }

        public static function render_custom_user_profile_fields( $user ) {
            // Get user meta
            $attachment_id = get_user_meta( (int)$user->ID, 'mm_sua_attachment_id', true );
            ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="mm-sua-add-media"><?php _e('Avatar', 'simple-user-avatar'); ?></label>
                        </th>
                        <td>
                            <input type="number" name="mm_sua_attachment_id" class="mm-sua-attachment-id" value="<?php echo $attachment_id; ?>" />
                            <figure class="mm-sua-attachment-image">
                                <?php echo get_avatar( $user->ID ); ?>
                            </figure>
                            <div class="wp-media-buttons">
                                <button class="button mm-sua-add-media" id="mm-sua-add-media"><?php _e('Select', 'simple-user-avatar'); ?></button>
                                <button class="button mm-sua-remove-media"><?php _e('Remove', 'simple-user-avatar'); ?></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php
        }

        public static function update_custom_user_profile_fields( $user_id ) {
            // If user don't have permissions
            if ( !current_user_can('edit_user', (int)$user_id) )
                return false;

            // Delete user meta
            delete_user_meta( (int)$user_id, 'mm_sua_attachment_id' );

            // Validate POST data and, if exists, add
            if (
                isset($_POST['mm_sua_attachment_id'])
                && is_numeric($_POST['mm_sua_attachment_id'])
                && $_POST['mm_sua_attachment_id'] > 0
            )
                add_user_meta( (int)$user_id, 'mm_sua_attachment_id', (int)$_POST['mm_sua_attachment_id'] ); //add user meta

            return true;
        }

        public static function custom_admin_notice() {
            $notice_is_expired = get_transient( static::$transient_name );

            if (
                !empty($notice_is_expired)
                && is_numeric($notice_is_expired)
                && $notice_is_expired == 1
            ) :
                // Notice dismissed, nothing to see
            else:
                // Show the notice
                global $current_user;

                // Get current user
                wp_get_current_user();
                ?>
                <div class="notice notice-info mm-sua-notice">
                    <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                        <p><?php printf( __( 'Dear <strong>%s</strong>,<br /><strong>thanks</strong> for using my plugin <strong>Simple User Avatar</strong>! To <strong>support</strong> the development, also in the future, I invite you to support me. Even a small amount, such as <strong>1$</strong>, will be greatly appreciated. Thank you very much, Matteo.', 'simple-user-avatar' ), $current_user->display_name ); ?></p>
                        <div>
                            <a href="https://www.paypal.com/donate/?cmd=_donations&business=matteomanna87%40gmail%2ecom" class="button button-primary" target="_blank"><?php _e( 'Donate now', 'simple-user-avatar' ); ?></a>
                            <button type="submit" class="button"><?php _e( 'Close', 'simple-user-avatar' ); ?></button>
                        </div>
                        <input type="hidden" name="action" value="close_notice" />
                        <?php wp_nonce_field( get_bloginfo('name'), '_wpnonce' ); ?>
                    </form>
                </div>
                <?php
            endif;
        }

        public static function post_close_notice() {
            if ( wp_verify_nonce( $_POST['_wpnonce'], get_bloginfo('name') ) ) :
                // Number of days
                $days = 30;

                // Transient settings
                $transient = static::$transient_name;
                $value = 1;
                $expiration = ( (60 * 60) * 24 ) * $days;

                set_transient( $transient, $value, $expiration );
            endif;

            exit( wp_safe_redirect( $_POST['_wp_http_referer'] ) );
        }

        public static function override_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
            // Get user ID
            if ( is_numeric($id_or_email) ) :
                $user_id = (int)$id_or_email;
            elseif ( is_string($id_or_email) ) :
                $user = get_user_by( 'email', $id_or_email );
                $user_id = (int)$user->ID;
            elseif ( is_object($id_or_email) ) :
                $user_id = (int)$id_or_email->user_id;
            endif;

            // Get attachment_meta
            $attachment_id = get_user_meta( (int)$user_id, 'mm_sua_attachment_id', true );
            if( empty($attachment_id) )
                return $avatar;

            // Get attachment url
            $attachment_url = wp_get_attachment_url( (int)$attachment_id );
            if( empty($attachment_url) )
                return $avatar;

            // Override WP urls
            $avatar = preg_replace( '/src=("|\').*?("|\')/', "src='{$attachment_url}'", $avatar );
            $avatar = preg_replace( '/srcset=("|\').*?("|\')/', "srcset='{$attachment_url}'", $avatar );

            return $avatar;
        }

    }

    add_action( 'plugins_loaded', [ 'SimpleUserAvatar', 'init' ] );

endif;
