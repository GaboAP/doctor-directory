<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$doctor   = null;
$is_edit  = false;
$errors   = array();
$values   = array( 'full_name' => '', 'email' => '', 'address' => '' );

// Are we editing?
if ( isset( $_GET['id'] ) && intval( $_GET['id'] ) > 0 ) {
    $doctor  = DD_Doctor::get_by_id( intval( $_GET['id'] ) );
    $is_edit = true;

    if ( $doctor ) {
        $values = array(
            'full_name' => $doctor->full_name,
            'email'     => $doctor->email,
            'address'   => $doctor->address,
        );
    }
}

// Handle form submission
if ( $_SERVER['REQUEST_METHOD'] === 'POST' && isset( $_POST['dd_submit'] ) ) {

    // Verify nonce
    if ( ! isset( $_POST['dd_nonce'] ) || ! wp_verify_nonce( $_POST['dd_nonce'], 'dd_save_doctor' ) ) {
        wp_die( 'Security check failed.' );
    }

    $values = array(
        'full_name' => isset( $_POST['full_name'] ) ? $_POST['full_name'] : '',
        'email'     => isset( $_POST['email'] )     ? $_POST['email']     : '',
        'address'   => isset( $_POST['address'] )   ? $_POST['address']   : '',
    );

    // Server-side validation
    $errors = DD_Doctor::validate( $values );

    if ( empty( $errors ) ) {
        if ( $is_edit && $doctor ) {
            $success = DD_Doctor::update( $doctor->id, $values );
            $message = $success ? 'Doctor updated successfully.' : 'Could not update the doctor.';
        } else {
            $new_id  = DD_Doctor::create( $values );
            $success = $new_id !== false;
            $message = $success ? 'Doctor added successfully.' : 'Could not add the doctor. Email may already exist.';
        }

        if ( $success ) {
            // Redirect to list with success message
            wp_redirect( admin_url( 'admin.php?page=doctor-directory&message=' . urlencode( $message ) ) );
            exit;
        }
    }
}

// Show success message passed via redirect
if ( isset( $_GET['message'] ) ) {
    echo '<div class="notice notice-success is-dismissible"><p>' . esc_html( urldecode( $_GET['message'] ) ) . '</p></div>';
}
?>

<div class="wrap dd-wrap">
    <h1><?php echo $is_edit ? 'Edit Doctor' : 'Add New Doctor'; ?></h1>
    <a href="<?php echo admin_url( 'admin.php?page=doctor-directory' ); ?>" class="page-title-action">← Back to List</a>
    <hr class="wp-header-end">

    <div class="dd-form-card">
        <form method="post" action="" id="dd-doctor-form" novalidate>
            <?php wp_nonce_field( 'dd_save_doctor', 'dd_nonce' ); ?>

            <!-- Full Name -->
            <div class="dd-field <?php echo isset( $errors['full_name'] ) ? 'dd-field--error' : ''; ?>">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input
                    type="text"
                    id="full_name"
                    name="full_name"
                    value="<?php echo esc_attr( $values['full_name'] ); ?>"
                    placeholder="Dr. John Smith"
                >
                <span class="dd-error-msg" id="err-full_name">
                    <?php echo isset( $errors['full_name'] ) ? esc_html( $errors['full_name'] ) : ''; ?>
                </span>
            </div>

            <!-- Email -->
            <div class="dd-field <?php echo isset( $errors['email'] ) ? 'dd-field--error' : ''; ?>">
                <label for="email">Email Address <span class="required">*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?php echo esc_attr( $values['email'] ); ?>"
                    placeholder="doctor@hospital.com"
                >
                <span class="dd-error-msg" id="err-email">
                    <?php echo isset( $errors['email'] ) ? esc_html( $errors['email'] ) : ''; ?>
                </span>
            </div>

            <!-- Address -->
            <div class="dd-field <?php echo isset( $errors['address'] ) ? 'dd-field--error' : ''; ?>">
                <label for="address">Physical Address <span class="required">*</span></label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    placeholder="123 Main St, City, Country"
                ><?php echo esc_textarea( $values['address'] ); ?></textarea>
                <span class="dd-error-msg" id="err-address">
                    <?php echo isset( $errors['address'] ) ? esc_html( $errors['address'] ) : ''; ?>
                </span>
            </div>

            <div class="dd-form-actions">
                <button type="submit" name="dd_submit" class="button button-primary button-large">
                    <?php echo $is_edit ? 'Update Doctor' : 'Add Doctor'; ?>
                </button>
                <a href="<?php echo admin_url( 'admin.php?page=doctor-directory' ); ?>" class="button button-large">Cancel</a>
            </div>
        </form>
    </div>
</div>