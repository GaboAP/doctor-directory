<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class DD_Doctor {

    /**
     * Get all doctors, with optional search term.
     */
    public static function get_all( $search = '' ) {
        global $wpdb;
        $table = DD_Database::get_table_name();

        if ( ! empty( $search ) ) {
            return $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * FROM {$table}
                     WHERE full_name LIKE %s OR email LIKE %s OR address LIKE %s
                     ORDER BY full_name ASC",
                    '%' . $wpdb->esc_like( $search ) . '%',
                    '%' . $wpdb->esc_like( $search ) . '%',
                    '%' . $wpdb->esc_like( $search ) . '%'
                )
            );
        }

        return $wpdb->get_results( "SELECT * FROM {$table} ORDER BY full_name ASC" );
    }

    /**
     * Get a single doctor by ID.
     */
    public static function get_by_id( $id ) {
        global $wpdb;
        $table = DD_Database::get_table_name();

        return $wpdb->get_row(
            $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", intval( $id ) )
        );
    }

    /**
     * Insert a new doctor. Returns inserted ID or false.
     */
    public static function create( $data ) {
        global $wpdb;
        $table = DD_Database::get_table_name();

        $inserted = $wpdb->insert(
            $table,
            array(
                'full_name' => sanitize_text_field( $data['full_name'] ),
                'email'     => sanitize_email( $data['email'] ),
                'address'   => sanitize_textarea_field( $data['address'] ),
            ),
            array( '%s', '%s', '%s' )
        );

        return $inserted ? $wpdb->insert_id : false;
    }

    /**
     * Update an existing doctor. Returns true or false.
     */
    public static function update( $id, $data ) {
        global $wpdb;
        $table = DD_Database::get_table_name();

        $result = $wpdb->update(
            $table,
            array(
                'full_name' => sanitize_text_field( $data['full_name'] ),
                'email'     => sanitize_email( $data['email'] ),
                'address'   => sanitize_textarea_field( $data['address'] ),
            ),
            array( 'id' => intval( $id ) ),
            array( '%s', '%s', '%s' ),
            array( '%d' )
        );

        // $result is false on error, 0 if no rows changed (still success)
        return $result !== false;
    }

    /**
     * Delete a doctor by ID. Returns true or false.
     */
    public static function delete( $id ) {
        global $wpdb;
        $table = DD_Database::get_table_name();

        return $wpdb->delete(
            $table,
            array( 'id' => intval( $id ) ),
            array( '%d' )
        );
    }

    /**
     * Server-side validation. Returns array of errors, empty if valid.
     */
    public static function validate( $data ) {
        $errors = array();

        if ( empty( trim( $data['full_name'] ) ) ) {
            $errors['full_name'] = 'Full name is required.';
        }

        if ( empty( trim( $data['email'] ) ) ) {
            $errors['email'] = 'Email address is required.';
        } elseif ( ! is_email( $data['email'] ) ) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if ( empty( trim( $data['address'] ) ) ) {
            $errors['address'] = 'Physical address is required.';
        }

        return $errors;
    }
}