<?php
/**
 * Simple User Avatar
 *
 * Plugin Name: Simple User Avatar
 * Plugin URI: https://wordpress.org/plugins/simple-user-avatar/
 * Description: Add a <strong>user avatar</strong> using images from your Media Library.
 * Version: 3.1
 * Author: Matteo Manna
 * Author URI: https://matteomanna.com/
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: simple-user-avatar
 */

// Injection prevention
if ( ! defined( 'ABSPATH' ) )
    exit;

/**
 * Global defines
 *
 * @since   2.8
 */
if ( ! defined( 'SUA_PLUGIN_VERSION' ) )
    define( 'SUA_PLUGIN_VERSION', 3.1 );

if ( ! defined( 'SUA_USER_META_KEY' ) )
    define( 'SUA_USER_META_KEY', 'mm_sua_attachment_id' );

if ( ! defined( 'SUA_TRANSIENT_NAME' ) )
    define( 'SUA_TRANSIENT_NAME', 'sua_notice_is_expired' );

// Public Class
require_once plugin_dir_path( __FILE__ ) . 'public/class-sua-public.php';

// Admin Class
if ( is_admin() )
    require_once plugin_dir_path( __FILE__ ) . 'admin/class-sua-admin.php';
