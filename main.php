<?php
/*
Plugin Name: WP Timeline
Description: Adds a timeline tab to wordpress. Allows creating a timeline using a custom post type
Version: 0.5.30.2024
Author: UFS
*/

function wptl_scripts()
{
    wp_enqueue_media();
    wp_enqueue_script('wptl-js', plugins_url('/js/wptl.js', __FILE__));
}

function wptl_styles()
{
    wp_enqueue_style('thickbox');
}
function timeline_admin_page()
{
    ?>
    <div class="wrap">
        <h2>Timeline Settings</h2>
        <div>Use the shortcode [timeline] to display</div>
    </div>
    <?php
}
function timeline_admin_menu()
{
    add_menu_page('Timeline Settings', 'Timeline', 'manage_options', 'timeline-admin-page', 'timeline_admin_page', 'dashicons-clock', 7);
}

/*add_action('admin_menu','timeline_admin_menu');*/
add_action('admin_print_scripts', 'wptl_scripts');
add_action('admin_print_styles', 'wptl_styles');

require_once ('wptl-functions.php');
require_once ('wptl-timeline.php');

add_filter('manage_edit-timeline_columns', 'wptl_show_timeline_column');
add_filter('wp_sprintf', function ($fragment) {
    $fragment = ('%z' === $fragment) ? '' : $fragment;
    return $fragment;
});

add_action('save_post', 'wptl_save_option_meta');
add_action('init', 'wptl_create_timeline');
add_action('add_meta_boxes', 'wptl_add_timeline_options');

wp_enqueue_script('timelinejs', plugin_dir_url(__FILE__) . 'js/timeline/dist/js/timeline.min.js', array('jquery'));
wp_enqueue_style('timeline-styles', plugin_dir_url(__FILE__) . 'js/timeline/dist/css/timeline.min.css');

add_shortcode('timeline', 'wptl_shortcode');

?>