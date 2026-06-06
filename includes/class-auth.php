<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Auth {

    /**
     * Check if the current user can manage the doctor directory.
     * WordPress already protects admin pages via 'manage_options',
     * but this helper can be used programmatically anywhere in the plugin.
     */
    public static function current_user_can_manage() {
        return is_user_logged_in() && current_user_can( 'manage_options' );
    }

    /**
     * Abort with WP's permission error if user is not authorized.
     * Use at the top of any sensitive operation.
     */
    public static function require_manage_permission() {
        if ( ! self::current_user_can_manage() ) {
            wp_die(
                __( 'You do not have permission to access this page.', 'doctor-directory' ),
                __( 'Access Denied', 'doctor-directory' ),
                array( 'response' => 403, 'back_link' => true )
            );
        }
    }
}