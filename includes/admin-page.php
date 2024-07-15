<?php
// Add admin menu
function cetd_admin_menu() {
    add_menu_page('Excel Table Display', 'Excel Tables', 'manage_options', 'cetd-admin', 'cetd_admin_page');
    add_submenu_page('cetd-admin', 'Edit Table 1 Data', 'Edit Table 1 Data', 'manage_options', 'cetd-edit-table1', 'cetd_edit_data_page');
    add_submenu_page('cetd-admin', 'Edit Table 2 Data', 'Edit Table 2 Data', 'manage_options', 'cetd-edit-table2', 'cetd_edit_data_page');
}
add_action('admin_menu', 'cetd_admin_menu');

// Admin page content
function cetd_admin_page() {
    ?>
    <div class="wrap">
        <h1>Excel Table Display Settings</h1>
        <?php settings_errors('cetd_messages'); ?>
        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('cetd_upload_excel', 'cetd_nonce'); ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><label for="table">Table</label></th>
                    <td>
                        <select name="table" id="table">
                            <option value="table1">Table 1</option>
                            <option value="table2">Table 2</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="excel_file">Excel File</label></th>
                    <td><input type="file" name="excel_file" id="excel_file" accept=".xlsx, .xls"></td>
                </tr>
            </table>
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button button-primary" value="Upload Excel">
            </p>
        </form>
    </div>
    <?php
}

// Handle file upload
function cetd_handle_upload() {
    if (isset($_POST['submit']) && check_admin_referer('cetd_upload_excel', 'cetd_nonce')) {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            add_settings_error('cetd_messages', 'cetd_message', 'File upload failed. Please try again.', 'error');
            return;
        }

        $file = $_FILES['excel_file'];
        $table = sanitize_text_field($_POST['table']);

        $allowed_types = array('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel');
        $file_type = wp_check_filetype(basename($file['name']))['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            add_settings_error('cetd_messages', 'cetd_message', 'Invalid file type. Please upload an Excel file (.xlsx or .xls).', 'error');
            return;
        }

        $upload_dir = CETD_UPLOAD_DIR;
        $filename = $table . '_data.' . pathinfo($file['name'], PATHINFO_EXTENSION);
        $file_path = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            add_settings_error('cetd_messages', 'cetd_message', 'Excel file uploaded successfully.', 'updated');
        } else {
            add_settings_error('cetd_messages', 'cetd_message', 'Error saving file. Please try again.', 'error');
        }
    }
}
add_action('admin_init', 'cetd_handle_upload');