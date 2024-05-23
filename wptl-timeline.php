<?php


//////////////////////////////////////////////
//
// ADMIN Section Functions
//
//////////////////////////////////////////////

// Create basic outline for timeline type
function wptl_create_timeline()
{

	$labels = array(
		'name' => _x('timeline', 'timeline General Name', 'wptl'),
		'singular_name' => _x('timeline Item', 'timeline Singular Name', 'wptl'),
		'add_new' => _x('Add New', 'Add New timeline Name', 'wptl'),
		'all_items' => __('All timeline', 'wptl'),
		'add_new_item' => __('Add New timeline', 'wptl'),
		'edit_item' => __('Edit timeline', 'wptl'),
		'new_item' => __('New timeline', 'wptl'),
		'view_item' => __('View timeline', 'wptl'),
		'search_items' => __('Search timeline', 'wptl'),
		'not_found' => __('Nothing found', 'wptl'),
		'not_found_in_trash' => __('Nothing found in Trash', 'wptl'),
		'parent_item_colon' => ''
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => false,
		'exclude_from_search' => true,
		'show_ui' => true,
		"show_in_nav_menus" => true,
		'menu_position' => 7,
		'capability_type' => 'post',
		'hierarchical' => false,
		'taxonomies'   => [ '' ],
		'supports' => array('title'),
		'rewrite' => true,
		'query_var' => true,
		'menu_icon' => 'dashicons-clock'
	);

	register_post_type('timeline1', $args);

	flush_rewrite_rules();

}

// add table column in edit page
function wptl_show_timeline_column($columns)
{
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Title',
		'date' => 'Last Edited',
		'wptl_timeline-date' => 'Content Date'
	);
	return $columns;
}

// Setup the timeline edit page
$timeline_meta_boxes = array(
	array(
		'title' => __('Authors', 'wptl'),
		'name' => 'wptl_timeline-authors',
		'type' => 'inputtext',
		'extra' => __('List of authors on the paper.', 'wptl')
	),
	array(
		'title' => __('Published Date', 'wptl'),
		'name' => 'wptl_timeline-date',
		'type'       => 'inputtext',
		'extra' => __('The date', 'wptl')
	),
	array(
		'title' => __('Body Text', 'wptl'),
		'name' => 'wptl_timeline-body',
		'type'       => 'inputtext',
		'extra' => __('', 'wptl')
	),
	array(
		'title' => __('Document', 'wptl'),
		'name' => 'wptl_timeline-pdf',
		'type' => 'upload',
		'extra' => __('Accepts PDF files', 'wptl')
	),
	array(
		'title' => __('Media', 'wptl'),
		'name' => 'wptl_timeline-media',
		'type' => 'inputtext',
		'extra' => __('Accepts video links', 'wptl')
	),
	array(
		'title' => __('Fun Fact', 'wptl'),
		'name' => 'wptl_timeline-fact',
		'type'       => 'inputtext',
		'extra' => __('A fun fact to highlight', 'wptl')
	)
);

function wptl_add_timeline_options()
{

	global $timeline_meta_boxes;

	foreach ($timeline_meta_boxes as $opt) {
		add_meta_box(
			'wptl_metabox-' . $opt['title'],
			__($opt['title']),
			'wptl_add_timeline_option_content',
			'timeline',
			'normal',
			'high',
			$opt
		);
	}

}

function wptl_add_timeline_option_content($post, $option)
{
	$option = $option['args'];

	wptl_set_nonce();

	$option['value'] = get_post_meta($post->ID, $option['name'], true);
	wptl_print_option($option);

}

function wptl_save_timeline_option_meta($post_id)
{

	global $timeline_meta_boxes;

	// save
	foreach ($timeline_meta_boxes as $opt) {

		if ($opt['type'] != 'inputtext') {
			$new_data = wp_get_attachment_url($_POST[$opt['name']]);
		} else if (isset($_POST[$opt['name']])) {
			$new_data = stripslashes($_POST[$opt['name']]);
		} else {
			$new_data = '';
		}

		$old_data = get_post_meta($post_id, $opt['name'], true);
		wptl_save_meta_data($post_id, $new_data, $old_data, $opt['name']);

	}

}


//////////////////////////////////////////////
//
// DISPLAY Section Functions
//
//////////////////////////////////////////////


$wptl_options = array(
	'category' => '',
	'numbered' => 'false',
	'limit' => -1,
	'reverse' => 'false',
	'show_links' => 'true',
	'page_num' => '',
	'num_per_page' => -1,
);

/* Base function that returns a nice array of all the requested timeline.
 *
 * Each item in the array contains (if the values are stored):
 *  id
 *  title
 *  authors
 *  conference
 *  pdf_url
 *  bibtex_url
 *
 */
function wptl_get_timeline_items_array($options)
{

	$timeline_items = array();

	$order = (strtolower($options['reverse']) == 'true') ? 'ASC' : 'DESC';

	// query for the timeline
	$timeline_items_q = new WP_Query(
		array(
			'post_type' => 'timeline',
			'timeline-category' => $options['category'],
			'order' => $order,
			'paged' => $options['page_num'],
			'posts_per_page' => $options['num_per_page'],
		)
	);

	$count = 0;
	while ($timeline_items_q->have_posts()) {
		if ($count == $options['limit']) {
			// only display that many
			break;
		}

		$pub = array();

		$timeline_items_q->the_post();

		$pub['id'] = $timeline_items_q->post->ID;
		$pub['title'] = get_the_title();
		$pub['authors'] = get_post_meta($pub['id'], 'wptl_timeline-option-authors', true);
		$pub['date'] = get_post_meta($pub['id'], 'wptl_timeline-date', true);
		$pub['body'] = get_post_meta($pub['id'], 'wptl_timeline-body', true);
		$pub['pdf_url'] = get_post_meta($pub['id'], 'wptl_timeline-pdf', true);
		$pub['media'] = get_post_meta($pub['id'], 'wptl_timeline-media', true);
		$pub['fact'] = get_post_meta($pub['id'], 'wptl_timeline-fact', true);
		$timeline_items[] = $pub;
		$count++;
	}

	return $timeline_items;
}
/* The second base function that takes the raw timeline data and puts it
 * into nice html tags.
 *
 * Also needs the $options because some options are formatting related
 */
function wptl_get_timeline_items_formatted($options)
{

	// get the timeline data
	$timeline_items = wptl_get_timeline_items_array($options);

	$output = '';

	foreach ($timeline_items as $pub) {
		// Create the links string
		$links = array();
		if (strtolower($options['show_links']) == 'true') {
			if (!empty($pub['date'])) {
				$link = '<a class="wptl-button-link" href="' . $pub['date'] . '"><button class="wptl-button"> ' . __('BibTex', 'wptl') . '</button></a>';
				array_push($links, $link);
			}
			if (!empty($pub['body'])) {
				$link = '<a class="wptl-button-link" href="' . $pub['body'] . '"><button class="wptl-button"> ' . __('PPT', 'wptl') . '</button></a>';
				array_push($links, $link);
			}
			if (!empty($pub['pdf_url'])) {
				$link = '<a class="wptl-button-link" href="' . $pub['pdf_url'] . '"><button class="wptl-button"> ' . __('Website', 'wptl') . '</button></a>';
				array_push($links, $link);
			}
			if (!empty($pub['media'])) {
				$link = '<a class="wptl-button-link" href="https://doi.org/' . $pub['media'] . '"><button class="wptl-button"> ' . $pub['DOI'] . '</button></a>';
				array_push($links, $link);
			}
			if (!empty($pub['fact'])) {
				$link = '<a class="wptl-button-link" href="https://doi.org/' . $pub['fact'] . '"><button class="wptl-button"> ' . $pub['DOI'] . '</button></a>';
				array_push($links, $link);
			}
			$links_str = '<p class="wptl-links">' . implode(' | ', $links) . '</p>';
		}
		$output .= '<tr><td>' . $links_str . '</td></tr>';
	}
}


/* Function to call in template to get array of timeline.
 */
function wptl_get_timeline($options = array())
{
	global $wptl_options;

	$all_options = wptl_array_merge($wptl_options, $options);

	return wptl_get_timeline_items_array($all_options);
}

/* Function to call in template to get formatted timeline.
 */
function wptl_get_timeline_formatted($options = array())
{
	global $wptl_options;

	$all_options = wptl_array_merge($wptl_options, $options);

	return wptl_get_timeline_items_formatted($all_options);
}

?>