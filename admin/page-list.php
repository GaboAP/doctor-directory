<?php
if ( ! defined( 'ABSPATH' ) ) exit;

// Handle delete action here (before any HTML output)
if ( isset( $_GET['action'] ) && $_GET['action'] === 'delete' && isset( $_GET['id'] ) ) {
    // Verify nonce for security
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'dd_delete_doctor' ) ) {
        wp_die( 'Security check failed.' );
    }

    $deleted = DD_Doctor::delete( intval( $_GET['id'] ) );

    if ( $deleted ) {
        echo '<div class="notice notice-success is-dismissible"><p>Doctor deleted successfully.</p></div>';
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>Could not delete the doctor.</p></div>';
    }
}

// Handle search
$search  = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : '';
$doctors = DD_Doctor::get_all( $search );
?>

<div class="wrap dd-wrap">
    <h1 class="wp-heading-inline">Doctor Directory</h1>
    <a href="<?php echo admin_url( 'admin.php?page=doctor-directory-add' ); ?>" class="page-title-action">Add New</a>
    <hr class="wp-header-end">

    <!-- Search form -->
    <form method="get" action="" class="dd-search-form">
        <input type="hidden" name="page" value="doctor-directory">
        <p class="search-box">
            <input
                type="search"
                name="s"
                id="dd-search-input"
                value="<?php echo esc_attr( $search ); ?>"
                placeholder="Search by name, email or address..."
                class="dd-search-input"
            >
            <button type="submit" class="button">Search</button>
            <?php if ( $search ) : ?>
                <a href="<?php echo admin_url( 'admin.php?page=doctor-directory' ); ?>" class="button">Clear</a>
            <?php endif; ?>
        </p>
    </form>

    <!-- Doctors table -->
    <?php if ( empty( $doctors ) ) : ?>
        <div class="dd-empty-state">
            <span class="dashicons dashicons-heart dd-empty-icon"></span>
            <p><?php echo $search ? 'No doctors found for that search.' : 'No doctors yet. Add your first one!'; ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped dd-table">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Address</th>
                    <th>Added</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ( $doctors as $doctor ) : ?>
                    <tr>
                        <td><strong><?php echo esc_html( $doctor->full_name ); ?></strong></td>
                        <td><?php echo esc_html( $doctor->email ); ?></td>
                        <td><?php echo esc_html( $doctor->address ); ?></td>
                        <td><?php echo esc_html( date( 'M j, Y', strtotime( $doctor->created_at ) ) ); ?></td>
                        <td class="dd-actions">
                            <a href="<?php echo admin_url( 'admin.php?page=doctor-directory-add&id=' . $doctor->id ); ?>" class="button button-small">Edit</a>
                            <a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=doctor-directory&action=delete&id=' . $doctor->id ), 'dd_delete_doctor' ); ?>"
                               class="button button-small button-link-delete dd-delete-btn"
                               data-name="<?php echo esc_attr( $doctor->full_name ); ?>">
                                Delete
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p class="dd-count"><?php echo count( $doctors ); ?> doctor(s) found.</p>
    <?php endif; ?>
</div>