<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Admin_Menu {

    public static function init() {
        add_action( 'admin_menu',    array( __CLASS__, 'register_menu' ) );
        add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_assets' ) );
    }

    public static function register_menu() {
        add_menu_page(
            'Doctor Directory',        // Page title
            'Doctor Directory',        // Menu label
            'manage_options',          // Capability required (Admins only)
            'doctor-directory',        // Menu slug
            array( __CLASS__, 'render_list_page' ), // Default page = list
            'dashicons-heart',         // Icon (WP built-in)
            30                         // Position in sidebar
        );

        add_submenu_page(
            'doctor-directory',
            'All Doctors',
            'All Doctors',
            'manage_options',
            'doctor-directory',        // Same slug = same page as parent
            array( __CLASS__, 'render_list_page' )
        );

        add_submenu_page(
            'doctor-directory',
            'Add New Doctor',
            'Add New',
            'manage_options',
            'doctor-directory-add',
            array( __CLASS__, 'render_form_page' )
        );
    }

    public static function render_list_page() {
        require_once DD_PLUGIN_DIR . 'admin/page-list.php';
    }

public static function render_form_page() {
    // Process form BEFORE loading the view
    if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['dd_submit'] ) ) {
        if ( ! isset( $_POST['dd_nonce'] ) || ! wp_verify_nonce( $_POST['dd_nonce'], 'dd_save_doctor' ) ) {
            wp_die( 'Security check failed.' );
        }

        $id      = isset( $_POST['doctor_id'] ) ? intval( $_POST['doctor_id'] ) : 0;
        $is_edit = $id > 0;
        $values  = array(
            'full_name' => isset( $_POST['full_name'] ) ? $_POST['full_name'] : '',
            'email'     => isset( $_POST['email'] )     ? $_POST['email']     : '',
            'address'   => isset( $_POST['address'] )   ? $_POST['address']   : '',
        );

        $errors = DD_Doctor::validate( $values );

        if ( empty( $errors ) ) {
            if ( $is_edit ) {
                $success = DD_Doctor::update( $id, $values );
                $msg     = $success ? 'Doctor updated successfully.' : 'Could not update the doctor.';
            } else {
                $new_id  = DD_Doctor::create( $values );
                $success = $new_id !== false;
                $msg     = $success ? 'Doctor added successfully.' : 'Could not add the doctor. Email may already exist.';
            }

            if ( $success ) {
                wp_redirect( admin_url( 'admin.php?page=doctor-directory&message=' . urlencode( $msg ) ) );
                exit;
            }
        }

        // If errors, pass them to the view via globals
        global $dd_form_errors, $dd_form_values;
        $dd_form_errors = $errors;
        $dd_form_values = $values;
    }

    require_once DD_PLUGIN_DIR . 'admin/page-form.php';
}

    public static function enqueue_assets( $hook ) {
        // Only load our assets on our plugin pages
        if ( strpos( $hook, 'doctor-directory' ) === false ) return;

        wp_enqueue_style(
            'dd-admin-style',
            DD_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            DD_VERSION
        );

        wp_enqueue_script(
            'dd-admin-scripts',
            DD_PLUGIN_URL . 'assets/js/admin-scripts.js',
            array( 'jquery' ),   // jQuery como dependencia (ya viene en WP)
            DD_VERSION,
            true                 // Load in footer
        );

        // Pass PHP data to JS (for AJAX later)
        wp_localize_script( 'dd-admin-scripts', 'DD_Ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => wp_create_nonce( 'dd_nonce' ),
        ) );
    }
}

DD_Admin_Menu::init();