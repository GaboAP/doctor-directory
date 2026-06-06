<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Database {

    /**
     * Runs on plugin activation.
     * Creates the doctors table if it doesn't exist.
     */
    public static function create_table() {
        global $wpdb;

        $table_name      = $wpdb->prefix . DD_TABLE_NAME;
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id          INT(11)      NOT NULL AUTO_INCREMENT,
            full_name   VARCHAR(150) NOT NULL,
            email       VARCHAR(150) NOT NULL,
            address     TEXT         NOT NULL,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY uq_email (email)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * Returns the full prefixed table name.
     * Use this everywhere instead of hardcoding the table name.
     */
    public static function get_table_name() {
        global $wpdb;
        return $wpdb->prefix . DD_TABLE_NAME;
    }
}