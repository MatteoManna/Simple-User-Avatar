<?php
if ( ! class_exists( 'SimpleUserAvatar_Public' ) ) :

    /**
     * PHP class SimpleUserAvatar_Public
     *
     * @since   2.8
     */
    class SimpleUserAvatar_Public {

        public function __construct() {

            // Override WP function get_avatar()
            add_filter( 'get_avatar', [ $this, 'get_avatar_filter' ], 5, 5 );

        }


        public static function init() {

            new self;

        }


        /**
         * Ovverride of the original WP function get_avatar();
         *
         * @since   1.0
         */
        public function get_avatar_filter( $avatar, $id_or_email, $size, $default, $alt ) {

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
            $attachment_id = get_user_meta( $user_id, SUA_USER_META_KEY, true );
            if ( empty($attachment_id) )
                return $avatar;

            // Get attachment url
            $attachment_url = wp_get_attachment_url( $attachment_id );
            if ( empty($attachment_url) )
                return $avatar;

            // Override WP urls
            $avatar = preg_replace( '/src=("|\').*?("|\')/', "src='{$attachment_url}'", $avatar );
            $avatar = preg_replace( '/srcset=("|\').*?("|\')/', "srcset='{$attachment_url}'", $avatar );

            return $avatar;

        }

    }

    add_action( 'plugins_loaded', [ 'SimpleUserAvatar_Public', 'init' ] );

endif;
