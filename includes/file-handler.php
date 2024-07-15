<?php
require_once CETD_PLUGIN_DIR . 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

function cetd_get_excel_data($gender) {
    $file_path = CETD_UPLOAD_DIR . $gender . '_data.xlsx';
    if (!file_exists($file_path)) {
        $file_path = CETD_UPLOAD_DIR . $gender . '_data.xls';
        if (!file_exists($file_path)) {
            error_log("Excel file not found for gender: $gender");
            return false;
        }
    }

    try {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file_path);
        $worksheet = $spreadsheet->getActiveSheet();
        $data = $worksheet->toArray();

        if (empty($data)) {
            error_log("Empty data in Excel file for gender: $gender");
            return false;
        }

        $headers = array_shift($data); // Get the first row as headers
        if (empty($headers)) {
            error_log("No headers found in Excel file for gender: $gender");
            return false;
        }

        return array(
            'headers' => $headers,
            'rows' => $data
        );
    } catch (Exception $e) {
        error_log("Error reading Excel file for gender $gender: " . $e->getMessage());
        return false;
    }
}