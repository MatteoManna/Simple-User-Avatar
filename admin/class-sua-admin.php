<?php
if ( !class_exists( 'SimpleUserAvatar_Admin' ) ) :

    /**
     * PHP class SimpleUserAvatar_Admin
     *
     * @since   2.8
     */
    class SimpleUserAvatar_Admin {

        private $notice_pages = [ 'users.php', 'profile.php', 'user-new.php' ];
        private $notice_months_expiration = 3;

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

        }


        public static function init() {

            new self;

        }


        /**
         * Load CSS and Javascript for wp-admin
         *
         * @since   1.0
         */
        public function custom_admin_enqueue_scripts() {

            // Get current user
            global $current_user;

            // Enqueue WordPress Media Library
            wp_enqueue_media();

            // CSS style for Admin
            wp_enqueue_style( 'sua', plugins_url( 'css/style.css', __FILE__ ), [], SUA_PLUGIN_VERSION, 'all' );

            // Javascript for Admin
            wp_enqueue_script( 'sua', plugins_url( 'js/scripts.js', __FILE__ ), [], SUA_PLUGIN_VERSION, true );

            // Get default avatar URL by user_email
            $l10n = [
                'default_avatar_src' => $this->get_default_avatar_url_by_email( $current_user->user_email, 96 ),
                'default_avatar_srcset' => $this->get_default_avatar_url_by_email( $current_user->user_email, 192 ) . ' 2x'
            ];
            wp_localize_script( 'sua', 'sua_obj', $l10n );

        }


        /**
         * Default WordPress avatar URL by user email
         *
         * @since   2.8
         */
        private function get_default_avatar_url_by_email( $user_email = '', $size = 96 ) {

            // Check the email provided
            if ( empty($user_email) || !filter_var($user_email, FILTER_VALIDATE_EMAIL) ) {
                return null;
            }

            // Sanitize Email and get md5
            $user_email = sanitize_email( $user_email );
            $md5_user_email = md5( $user_email );

            // SSL Gravatar URL
            $url = "https://secure.gravatar.com/avatar/{$md5_user_email}.jpg";

            // Add size
            $url = add_query_arg( 's', $size, $url );

            return esc_url( $url );

        }


        /**
         * Add table in user profile
         *
         * @since   1.0
         */
        public function render_custom_user_profile_fields( $user ) {

            // Get user meta
            $attachment_id = get_user_meta( $user->ID, SUA_USER_META_KEY, true );
            ?>
            <input type="number" name="<?php echo SUA_USER_META_KEY; ?>" class="sua__attachment--id" value="<?php echo $attachment_id; ?>" />
            <table class="form-table">
                <tbody>
                    <tr>
                        <th>
                            <label for="btn-media-add"><?php _e('Profile picture', 'simple-user-avatar'); ?></label>
                        </th>
                        <td>
                            <figure class="sua__attachment--figure">
                                <?php echo get_avatar( $user->ID, 96, '', $user->display_name ); ?>
                                <figcaption class="sua__attachment--figcaption"><?php _e("You're seeing the default profile picture.", 'simple-user-avatar'); ?></figcaption>
                            </figure>
                            <div>
                                <button class="button" id="btn-media-add"><?php _e('Select', 'simple-user-avatar'); ?></button>
                                <button class="button" id="btn-media-remove"><?php _e('Remove', 'simple-user-avatar'); ?></button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php

        }


        /**
         * Update the user_meta when user save
         *
         * @since   1.0
         */
        public function update_custom_user_profile_fields( $user_id ) {

            // If user don't have permissions
            if ( !current_user_can( 'edit_user', $user_id ) ) {
                return false;
            }

            // Delete old user meta
            delete_user_meta( $user_id, SUA_USER_META_KEY );

            // Validate POST data and, if exists, add it
            if ( isset($_POST[SUA_USER_META_KEY]) && is_numeric($_POST[SUA_USER_META_KEY]) ) {
                add_user_meta( $user_id, SUA_USER_META_KEY, (int)$_POST[SUA_USER_META_KEY] );
            }

            return true;

        }


        /**
         * Admin notice to donate for this plugin
         *
         * @since   2.6
         */
        public function custom_admin_notice() {

            // Get the transient
            $notice_is_expired = get_transient( SUA_TRANSIENT_NAME );

            if ( !empty($notice_is_expired) && is_numeric($notice_is_expired) && $notice_is_expired == 1 ) {

                // Notice dismissed, nothing to see!

            } else {

                // Get Current user
                global $current_user, $pagenow;

                // Show the notice, if the page is in private array
                if ( in_array( $pagenow, $this->notice_pages ) ) {

                    // Set the nonce field
                    $wp_nonce_field = wp_nonce_field( SUA_TRANSIENT_NAME, '_wpnonce', true, false );
                    $wp_nonce_field = preg_replace( '/id=("|\').*?("|\')/', '', $wp_nonce_field );
                    ?>

                    <div class="notice notice-info">
                        <form method="post" action="<?php echo admin_url( 'admin-post.php' ); ?>">
                            <p>
                                <?php
                                printf(
                                    __( 'Dear <strong>%s</strong>,<br />thank you for using my plugin <a href="%s" title="Simple User Avatar" target="_blank">Simple User Avatar</a>! To <strong>support</strong> the development, also in the future, I invite you to <strong>support me</strong>. Even a small amount, such as <strong>1$</strong> for one coffee &#x2615, will be greatly appreciated.<br />Thank you very much, Matteo.', 'simple-user-avatar' ),
                                    sanitize_text_field( $current_user->display_name ),
                                    esc_url( 'https://wordpress.org/plugins/simple-user-avatar/' )
                                );
                                ?>
                            </p>
                            <p>
                                <a href="https://www.paypal.com/donate/?cmd=_donations&business=matteomanna87%40gmail%2ecom" class="button button-primary" target="_blank"><?php _e( 'Donate now', 'simple-user-avatar' ); ?></a>
                                <button type="submit" class="button"><?php printf( __('Hide for %d months', 'simple-user-avatar' ), $this->notice_months_expiration ); ?></button>
                            </p>
                            <input type="hidden" name="action" value="close_notice" />
                            <?php echo $wp_nonce_field; ?>
                        </form>
                    </div>

                    <?php
                }

            }

        }


        /**
         * Save the hide command for the notice
         * Set the transient
         *
         * @since   2.6
         */
        public function post_close_notice() {

            // Verify nonce
            if ( wp_verify_nonce( $_POST['_wpnonce'], SUA_TRANSIENT_NAME ) ) {

                // Transient settings
                $transient = SUA_TRANSIENT_NAME;
                $value = 1;
                $expiration = (((60 * 60) * 24) * 30) * $this->notice_months_expiration;

                // Set the transient
                set_transient( $transient, $value, $expiration );

            }

            exit( wp_safe_redirect( $_POST['_wp_http_referer'] ) );

        }

    }

    add_action( 'plugins_loaded', [ 'SimpleUserAvatar_Admin', 'init' ] );

endif;
