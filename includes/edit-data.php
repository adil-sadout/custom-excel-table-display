<?php
// In includes/edit-data.php

function cetd_edit_data_page() {
    $table = (isset($_GET['page']) && $_GET['page'] === 'cetd-edit-table1') ? 'table1' : 'table2';
    $data = cetd_get_excel_data($table);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cetd_edit_data'])) {
        error_log("Form submitted for editing $table data");
        $result = cetd_save_edited_data($table, $_POST['data']);
        if ($result === true) {
            echo '<div class="updated"><p>Data updated successfully.</p></div>';
            $data = cetd_get_excel_data($table); // Refresh data after saving
        } else {
            echo '<div class="error"><p>Error updating data: ' . esc_html($result) . '</p></div>';
        }
    }

    if (!$data || !is_array($data) || empty($data['headers']) || empty($data['rows'])) {
        echo '<div class="wrap"><p>No data available or data is in incorrect format. Please upload a valid Excel file first.</p></div>';
        return;
    }

    ?>
    <div class="wrap">
        <h1>Edit <?php echo esc_html(ucfirst($table)); ?>'s Data</h1>
        <form method="post">
            <?php wp_nonce_field('cetd_edit_data', 'cetd_edit_nonce'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <?php foreach ($data['headers'] as $header): ?>
                            <th><?php echo esc_html($header); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['rows'] as $row_index => $row): ?>
                        <tr>
                            <?php foreach ($row as $col_index => $cell): ?>
                                <td>
                                    <input class="input-class" type="text" name="data[<?php echo esc_attr($row_index); ?>][<?php echo esc_attr($col_index); ?>]" value="<?php echo esc_attr($cell); ?>">
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <p class="submit">
                <input type="submit" name="cetd_edit_data" class="button button-primary" value="Save Changes">
            </p>
        </form>
    </div>
    <?php
}

// Save edited data
function cetd_save_edited_data($table, $data) {
    error_log("Attempting to save edited data for table: $table");

    if (!wp_verify_nonce($_POST['cetd_edit_nonce'], 'cetd_edit_data')) {
        error_log("Nonce verification failed");
        return 'Security check failed';
    }

    if (!is_array($data) || empty($data)) {
        error_log("Invalid data format: " . print_r($data, true));
        return 'Invalid data format';
    }

    $file_path = CETD_UPLOAD_DIR . $table . '_data.xlsx';
    
    if (!file_exists($file_path)) {
        error_log("Excel file not found: $file_path");
        return 'Excel file not found. Please upload a file first.';
    }

    require_once CETD_PLUGIN_DIR . 'vendor/autoload.php';
    
    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $excel_data = cetd_get_excel_data($table);
        if (!is_array($excel_data) || empty($excel_data['headers'])) {
            error_log("Invalid Excel data structure: " . print_r($excel_data, true));
            return 'Invalid Excel data structure';
        }

        $headers = $excel_data['headers'];
        foreach ($headers as $col_index => $header) {
            $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index + 1) . '1';
            $sheet->setCellValue($cell, $header);
        }

        // Set data
        foreach ($data as $row_index => $row) {
            if (!is_array($row)) {
                error_log("Invalid row data: " . print_r($row, true));
                continue;
            }
            foreach ($row as $col_index => $cell) {
                $cell_coordinate = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col_index + 1) . ($row_index + 2);
                $sheet->setCellValue($cell_coordinate, $cell);
            }
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save($file_path);
        error_log("Data saved successfully to: $file_path");
        return true;
    } catch (Exception $e) {
        error_log("Error saving Excel file: " . $e->getMessage());
        return 'Error saving data: ' . $e->getMessage();
    }
}