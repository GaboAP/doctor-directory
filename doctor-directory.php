<?php
/**
 * Plugin Name: Doctor Directory
 * Plugin URI:  https://github.com/GaboAP/doctor-directory
 * Description: A national-level doctor directory with full CRUD management.
 * Version:     1.0.0
 * Author:      Gabriel Omar Almendras Peña
 * License:     GPL-2.0+
 * Text Domain: doctor-directory
 */

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Plugin constants
define( 'DD_VERSION',     '1.0.0' );
define( 'DD_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'DD_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'DD_TABLE_NAME',  'doctors' ); // will be prefixed by $wpdb

// Load core files
require_once DD_PLUGIN_DIR . 'includes/class-db.php';
require_once DD_PLUGIN_DIR . 'includes/class-doctor.php';
require_once DD_PLUGIN_DIR . 'admin/menu.php';
require_once DD_PLUGIN_DIR . 'includes/class-auth.php';

// Activation hook — creates the DB table
register_activation_hook( __FILE__, array( 'DD_Database', 'create_table' ) );

//Actions
add_action('admin_init', ['DD_Admin_Menu', 'handle_form_submit']);