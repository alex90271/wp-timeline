<?php
/*
Plugin Name: WP Timeline
Description: Adds a timeline tab to wordpress. Allows creating a timeline using a custom post type
Version: 2024.7.13
Author: Alex Alder
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
add_action('admin_menu', 'wptl_register_sub_page');

wp_enqueue_script('timelinejs', plugin_dir_url(__FILE__) . 'js/timeline/js/timeline.js');
wp_enqueue_style('timelinejs-styles', plugin_dir_url(__FILE__) . 'js/timeline/css/timeline.css', array(), 1);
wp_enqueue_style('timeline-post-styles', plugin_dir_url(__FILE__) . 'styles/wptl_stylesheet.css', array(), 3);

add_shortcode('timeline', 'wptl_shortcode');

add_option('timeline_asc_desc', $value = 'ASC', $autoload = 'yes');
$asc_desc_param = array(
    'asc_desc' => get_option('timeline_asc_desc')
);
add_option('timeline_horz_vert', $value = 'horizontal', $autoload = 'yes');
$asc_desc_param = array(
    'horz_vert' => get_option('timeline_horz_vert')
);

add_action('admin_post_update_timeline_horz_vert','horz_vert_do_update');
add_action('admin_post_update_timeline_asc_desc','asc_desc_do_update');

/**
 * Deactivation hook.
 */

?>