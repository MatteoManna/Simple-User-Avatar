<?php
if ( !class_exists('SimpleUserAvatar_Public') ) :

    /**
     * PHP class SimpleUserAvatar_Public
     *
     * @since   2.8
     */
    class SimpleUserAvatar_Public {

        public function __construct() {

            // Override WordPress function get_avatar();
            add_filter( 'get_avatar', [ $this, 'get_avatar_filter' ], 5, 5 );

        }


        public static function init() {

            new self;

        }


        /**
         * Override of the original WordPress function get_avatar();
         *
         * @since   1.0
         */
        public function get_avatar_filter( string $avatar, $id_or_email, int $size, string $default, string $alt ) {

            // Get user ID, if is numeric
            if ( is_numeric($id_or_email) ) {

                $user_id = (int)$id_or_email;

            // If is string, maybe the user email
            } elseif ( is_string($id_or_email) ) {

                // Find user by email
                $user = get_user_by( 'email', $id_or_email );

                // If user doesn't exists or this is not an ID
                if ( !isset($user->ID) || !is_numeric($user->ID) ) {
                    return $avatar;
                }

                $user_id = (int)$user->ID;

            // If is an object
            } elseif ( is_object($id_or_email) ) {

                // If this is not an ID
                if ( !isset($id_or_email->user_id) || !is_numeric($id_or_email->user_id) ) {
                    return $avatar;
                }

                $user_id = (int)$id_or_email->user_id;

            }

            // Get attachment ID from user meta
            $attachment_id = get_user_meta( $user_id, SUA_USER_META_KEY, true );
            if ( empty($attachment_id) || !is_numeric($attachment_id) ) {
                return $avatar;
            }

            // Get attachment image src
            $attachment_src = wp_get_attachment_image_src( $attachment_id, 'medium' );
            if ( $attachment_src === false ) {
                return $avatar;
            }
            
            // Get attachment image srcset
            $attachment_srcset = wp_get_attachment_image_srcset( $attachment_id );
            if( $attachment_srcset === false ) {
                return $avatar;
            }

            // Override WordPress urls
            $avatar = preg_replace( '/src=("|\').*?("|\')/', "src='{$attachment_src[0]}'", $avatar );
            $avatar = preg_replace( '/srcset=("|\').*?("|\')/', "srcset='{$attachment_srcset}'", $avatar );

            return $avatar;

        }

    }

    add_action( 'plugins_loaded', [ 'SimpleUserAvatar_Public', 'init' ] );

endif;
