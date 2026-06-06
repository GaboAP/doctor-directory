<?php
if (! defined('ABSPATH')) exit;

class DD_Admin_Menu
{

    public static function init()
    {
        add_action('admin_menu',            array(__CLASS__, 'register_menu'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_assets'));
        add_action('init',                  array(__CLASS__, 'register_ajax_handlers'));
    }

    public static function register_menu()
    {
        add_menu_page(
            'Doctor Directory',        // Page title
            'Doctor Directory',        // Menu label
            'manage_options',          // Capability required (Admins only)
            'doctor-directory',        // Menu slug
            array(__CLASS__, 'render_list_page'), // Default page = list
            'dashicons-heart',         // Icon (WP built-in)
            30                         // Position in sidebar
        );

        add_submenu_page(
            'doctor-directory',
            'All Doctors',
            'All Doctors',
            'manage_options',
            'doctor-directory',        // Same slug = same page as parent
            array(__CLASS__, 'render_list_page')
        );

        add_submenu_page(
            'doctor-directory',
            'Add New Doctor',
            'Add New',
            'manage_options',
            'doctor-directory-add',
            array(__CLASS__, 'render_form_page')
        );
    }

    public static function handle_form_submit()
    {
        if (!isset($_POST['dd_submit'])) return;

        // Use WP's native check instead of DD_Auth at this hook
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to perform this action.', 'Access Denied', ['response' => 403]);
        }

        if (!isset($_POST['dd_nonce']) || !wp_verify_nonce($_POST['dd_nonce'], 'dd_save_doctor')) {
            wp_die('Security check failed.');
        }

        $id = isset($_POST['doctor_id']) ? intval($_POST['doctor_id']) : 0;
        $is_edit = $id > 0;

        $values = [
            'full_name' => $_POST['full_name'] ?? '',
            'email'     => $_POST['email'] ?? '',
            'address'   => $_POST['address'] ?? '',
        ];

        $errors = DD_Doctor::validate($values);

        if (empty($errors)) {

            if ($is_edit) {
                $success = DD_Doctor::update($id, $values);
                $msg = $success ? 'Doctor updated successfully.' : 'Update failed.';

                if ($success) {
                    wp_redirect(admin_url('admin.php?page=doctor-directory&message=' . urlencode($msg)));
                    exit;
                }
            } else {
                $new_id = DD_Doctor::create($values);
                $success = $new_id !== false;

                if (!$success) {
                    $db_error = $GLOBALS['wpdb']->last_error;
                    global $dd_form_errors, $dd_form_values;
                    $dd_form_values = $values;
                    $dd_form_errors = strpos($db_error, 'Duplicate entry') !== false
                        ? ['email' => 'This email address is already registered.']
                        : ['email' => 'Could not save the doctor. Please try again.'];
                    return;
                }

                $msg = 'Doctor added successfully.';
                wp_redirect(admin_url('admin.php?page=doctor-directory&message=' . urlencode($msg)));
                exit;
            }
        }

        // pass validation errors to form
        global $dd_form_errors, $dd_form_values;
        $dd_form_errors = $errors;
        $dd_form_values = $values;
    }

    public static function render_list_page()
    {
        require_once DD_PLUGIN_DIR . 'admin/page-list.php';
    }

    public static function render_form_page()
    {
        require_once DD_PLUGIN_DIR . 'admin/page-form.php';
    }

    public static function enqueue_assets($hook)
    {
        if (strpos($hook, 'doctor-directory') === false) return;

        // Styles
        wp_enqueue_style(
            'dd-admin-style',
            DD_PLUGIN_URL . 'assets/css/admin-style.css',
            array(),
            DD_VERSION
        );

        // Scripts — order matters, each declares its dependencies
        wp_enqueue_script(
            'dd-validation',
            DD_PLUGIN_URL . 'assets/js/dd-validation.js',
            array('jquery'),
            DD_VERSION,
            true
        );

        wp_enqueue_script(
            'dd-modal',
            DD_PLUGIN_URL . 'assets/js/dd-modal.js',
            array('jquery'),
            DD_VERSION,
            true
        );

        wp_enqueue_script(
            'dd-search',
            DD_PLUGIN_URL . 'assets/js/dd-search.js',
            array('jquery', 'dd-modal'),
            DD_VERSION,
            true
        );

        // Pass PHP data to JS — only needed by dd-search
        wp_localize_script('dd-search', 'DD_Ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce'    => wp_create_nonce('dd_nonce'),
        ));
    }
    public static function register_ajax_handlers()
    {
        add_action('wp_ajax_dd_live_search', array(__CLASS__, 'ajax_live_search'));
    }

    public static function ajax_live_search()
    {
        // Verify nonce
        if (! isset($_GET['nonce']) || ! wp_verify_nonce($_GET['nonce'], 'dd_nonce')) {
            wp_send_json_error('Security check failed.');
        }

        $search  = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';
        $doctors = DD_Doctor::get_all($search);

        ob_start();

        if (empty($doctors)) : ?>
            <div class="dd-empty-state">
                <span class="dashicons dashicons-heart dd-empty-icon"></span>
                <p>No doctors found for that search.</p>
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
                    <?php foreach ($doctors as $doctor) : ?>
                        <tr>
                            <td><strong><?php echo esc_html($doctor->full_name); ?></strong></td>
                            <td><?php echo esc_html($doctor->email); ?></td>
                            <td><?php echo esc_html($doctor->address); ?></td>
                            <td><?php echo esc_html(date('M j, Y', strtotime($doctor->created_at))); ?></td>
                            <td class="dd-actions">
                                <a href="<?php echo admin_url('admin.php?page=doctor-directory-add&id=' . $doctor->id); ?>" class="button button-small">Edit</a>
                                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=doctor-directory&action=delete&id=' . $doctor->id), 'dd_delete_doctor'); ?>"
                                    class="button button-small button-link-delete dd-delete-btn"
                                    data-name="<?php echo esc_attr($doctor->full_name); ?>">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="dd-count"><?php echo count($doctors); ?> doctor(s) found.</p>
<?php endif;

        $html = ob_get_clean();
        wp_send_json_success($html);
    }
}

DD_Admin_Menu::init();
