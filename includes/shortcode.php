<?php

function cetd_table_shortcode($atts) {
    $atts = shortcode_atts(array(
        'table' => 'table1', // Default to table1
    ), $atts);

    // Ensure the table attribute is either 'table1' or 'table2'
    if (!in_array($atts['table'], array('table1', 'table2'))) {
        return 'Invalid table specified.';
    }

    $data = cetd_get_excel_data($atts['table']);

    if (!$data) {
        return 'No data available.';
    }

    $output = '<div class="cetd-table-container">';
    $output .= '<table class="cetd-table">';
    
    // Add headers
    $output .= '<thead><tr>';
    foreach ($data['headers'] as $header) {
        $output .= '<th>' . esc_html($header) . '</th>';
    }
    $output .= '</tr></thead>';

    // Add rows
    $output .= '<tbody>';
    foreach ($data['rows'] as $row) {
        $output .= '<tr>';
        foreach ($row as $index => $cell) {
            $output .= '<td data-label="' . esc_attr($data['headers'][$index]) . '">' . esc_html($cell) . '</td>';
        }
        $output .= '</tr>';
    }
    $output .= '</tbody>';

    $output .= '</table>';
    $output .= '</div>';

    return $output;
}
add_shortcode('excel_table', 'cetd_table_shortcode');
