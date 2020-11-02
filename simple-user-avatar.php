<?php
/**
 * Simple User Avatar
 *
 * Plugin Name: Simple User Avatar
 * Plugin URI: https://wordpress.org/plugins/simple-user-avatar/
 * Description: Add a <strong>user avatar</strong> using images from your Media Library.
 * Version: 2.4
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

        private static $plugin_version = '2.4';
        private static $is_notice_active = true;

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

            // Override WP function get_avatar()
            add_filter( 'get_avatar', [ $this, 'override_get_avatar' ], 5, 5 );

            // If notice is active, I create a new object
            if( static::$is_notice_active == true )
                new SimpleUserAvatar_notice();
        }

        public static function custom_admin_enqueue_scripts() {
            // WP enqueue media, required
            wp_enqueue_media();

            // CSS
            wp_enqueue_style( 'sua', plugins_url('css/style.css', __FILE__), [], static::$plugin_version, 'all' );

            // JS
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

        public static function override_get_avatar( $avatar, $id_or_email, $size, $default, $alt ) {
            // Get user ID
            if ( is_numeric($id_or_email) ) :
                $user_id = (int)$id_or_email;
            elseif ( is_string($id_or_email) ) :
                $user = get_user_by( 'email', $id_or_email );
                $user_id = (int)$user->id;
            elseif ( is_object($id_or_email) ) :
                $user_id = (int)$id_or_email->user_id;
            endif;

            // Get attachment_meta
            $attachment_id = get_user_meta( (int)$user_id, 'mm_sua_attachment_id', true );
            if( empty($attachment_id) )
                return $avatar;

            // Get attachment src
            $src = self::get_attachment_url( (int)$attachment_id, 'thumbnail' );
            if( is_null($src) )
                return $avatar;

            // Override WP urls
            $avatar = preg_replace( '/src=("|\').*?("|\')/', "src='{$src}'", $avatar );
            $avatar = preg_replace( '/srcset=("|\').*?("|\')/', "srcset='{$src}'", $avatar );

            return $avatar;
        }

        private static function get_attachment_url( $attachment_id = 0, $size = 'thumbnail' ) {
            // Get attachment_src
            $src = wp_get_attachment_image_src( (int)$attachment_id, $size );

            return ( isset($src[0]) && !empty($src[0]) && is_string($src[0]) ) ? esc_url( $src[0] ) : null ;
        }

    }

    class SimpleUserAvatar_notice extends SimpleUserAvatar {

        private static $transient_id = 'sua_notice_is_expired';

        public static function init() {
            new self;
        }

        public function __construct() {
            // HTML render of notice
            add_action( 'admin_notices', [ $this, 'custom_admin_notice' ] );

            // Close notice
            add_action( 'admin_post_close_notice', [ $this, 'close_notice' ] );
        }

        public static function custom_admin_notice() {
            $notice_is_expired = get_transient( static::$transient_id );

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
                        <p><?php printf( __( 'Dear <strong>%s</strong>,<br /><strong>thanks</strong> for using my plugin <strong>Simple User Avatar</strong>!<br />To <strong>support</strong> the development, also in the future, I invite you to support me. Even a small amount, such as <strong>1$</strong>, will be greatly appreciated.<br /><br />Thank you very much,<br />Matteo', 'simple-user-avatar' ), $current_user->display_name ); ?></p>
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

        public static function close_notice() {
            if ( wp_verify_nonce( $_POST['_wpnonce'], get_bloginfo('name') ) ) :
                // Number of days
                $days = 30;

                // Transient settings
                $transient = static::$transient_id;
                $value = 1;
                $expiration = ( (60 * 60) * 24 ) * $days;

                set_transient( $transient, $value, $expiration );
            endif;

            exit( wp_safe_redirect( $_POST['_wp_http_referer'] ) );
        }

    }

    add_action( 'plugins_loaded', [ 'SimpleUserAvatar', 'init' ] );

endif;
