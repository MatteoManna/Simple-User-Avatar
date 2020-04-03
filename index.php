<?php
/*
Plugin Name: Simple User Avatar
Description: Add a <strong>user avatar</strong> using images from your Media Library.
Author: Matteo Manna
Version: 2.2
Author URI: https://matteomanna.com/
Text Domain: simple-user-avatar
License: GPL2
*/

if( !defined('ABSPATH') ) exit; // Injection prevention

function mm_sua_load_textdomain() {
    load_plugin_textdomain( 'simple-user-avatar', false, basename( dirname( __FILE__ ) ).'/languages' );
}
add_action( 'init', 'mm_sua_load_textdomain' );

function mm_sua_admin_head_scripts() {
    wp_enqueue_media();
    wp_enqueue_style( 'sua-css-style', plugins_url('css/style.css', __FILE__), array(), null) ;
    wp_enqueue_script( 'sua-js-custom', plugins_url('js/scripts.js', __FILE__), array(), '2.1', true );
}
add_action( 'admin_enqueue_scripts', 'mm_sua_admin_head_scripts' );

/**
 * @param $user_id
 * @return bool
 */
function mm_sua_update_custom_user_profile( $user_id ) {
    if( !current_user_can('edit_user', (int)$user_id) ) return false;

    delete_user_meta( (int)$user_id, 'mm_sua_attachment_id' ); //delete meta

    if( //validate POST data
        isset($_POST['mm_sua_attachment_id'])
        && is_numeric($_POST['mm_sua_attachment_id'])
        && $_POST['mm_sua_attachment_id'] > 0
    ) :
        add_user_meta( (int)$user_id, 'mm_sua_attachment_id', (int)$_POST['mm_sua_attachment_id'] ); //add user meta
    else:
        return false;
    endif;

    return true;
}
add_action( 'personal_options_update', 'mm_sua_update_custom_user_profile' );
add_action( 'edit_user_profile_update', 'mm_sua_update_custom_user_profile' );

/**
 * @param $user
 */
function mm_sua_add_custom_user_profile_fields( $user ) {
    $mm_sua_attachment_id = (int)get_user_meta( (int)$user->ID, 'mm_sua_attachment_id', true );
    ?>
    <table class="form-table">
        <tbody>
            <tr>
                <th>
                    <label for="mm-sua-add-media"><?php _e('Avatar', 'simple-user-avatar'); ?></label>
                </th>
                <td>
                    <input type="number" name="mm_sua_attachment_id" class="mm-sua-attachment-id" value="<?php echo $mm_sua_attachment_id; ?>" />
                    <div class="mm-sua-attachment-image">
                        <?php echo get_avatar($user->ID); ?>
                    </div>
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
add_action( 'show_user_profile', 'mm_sua_add_custom_user_profile_fields' );
add_action( 'edit_user_profile', 'mm_sua_add_custom_user_profile_fields' );

/**
 * @param int $attachment_id
 * @param string $size
 * @return mixed
 */
function mm_sua_get_attachment_url( $attachment_id = 0, $size = 'thumbnail' ) {
    $image = wp_get_attachment_image_src( (int)$attachment_id, $size );
    return ( isset($image[0]) ) ? $image[0] : null ;
}

/**
 * @param $plugin
 */
function mm_sua_redirect_after_activation( $plugin ) {
    if( $plugin == plugin_basename( __FILE__ ) ) exit( wp_redirect( admin_url('profile.php') ) );
}
add_action( 'activated_plugin', 'mm_sua_redirect_after_activation' );

/**
 * @param string $avatar
 * @param $id_or_email
 * @return mixed|string
 */
function mm_sua_get_new_avatar( $avatar = '', $id_or_email ) {
    $user_id = 0;

    if ( is_numeric($id_or_email) ) :
        $user_id = (int)$id_or_email;
    elseif ( is_string($id_or_email) ) :
        $user = get_user_by( 'email', $id_or_email );
        $user_id = (int)$user->id;
    elseif ( is_object($id_or_email) ) :
        $user_id = (int)$id_or_email->user_id;
    endif;

    if ( $user_id == 0 ) return $avatar;

    $mm_sua_attachment_id = (int)get_user_meta( (int)$user_id, 'mm_sua_attachment_id', true );
    $image = mm_sua_get_attachment_url( (int)$mm_sua_attachment_id, 'thumbnail' );
    if( is_null($image) ) $avatar = '';

    $avatar = preg_replace( '/src=("|\').*?("|\')/i', 'src="'. $image .'"', $avatar );
    $avatar = preg_replace( '/srcset=("|\').*?("|\')/i', 'srcset="'. $image .'"', $avatar );

    return $avatar;
}
add_filter( 'get_avatar', 'mm_sua_get_new_avatar', 5, 5 );
