<?php
/*
Plugin Name: WP Timeline
Description: Adds a timeline tab to wordpress. Allows creating a timeline using a custom post type
Version: 0.5.30.2024
Author: UFS
*/

wp_enqueue_script('wptl-js', plugins_url('/js/wptl.js', __FILE__), array('jquery'));
wp_enqueue_style('thickbox');

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

wp_enqueue_script('timelinejs', plugin_dir_url(__FILE__) . 'js/timeline/dist/js/timeline.min.js');
wp_enqueue_style('timelinejs-styles', plugin_dir_url(__FILE__) . 'js/timeline/dist/css/timeline.min.css');
wp_enqueue_style('timeline-post-styles', plugin_dir_url(__FILE__) . 'styles/styles.css');


add_shortcode('timeline', 'wptl_shortcode');

?>