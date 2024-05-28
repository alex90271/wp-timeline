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
		'name' => _x('Timeline', 'Timeline General Name', 'wptl'),
		'singular_name' => _x('Timeline Items', 'Timeline Singular Name', 'wptl'),
		'add_new' => _x('Add New', 'Add New Timeline Item', 'wptl'),
		'all_items' => __('All Timeline Items', 'wptl'),
		'add_new_item' => __('Add New Timeline Item', 'wptl'),
		'edit_item' => __('Edit Timeline Item', 'wptl'),
		'new_item' => __('New Timeline Items', 'wptl'),
		'view_item' => __('View Timeline Item', 'wptl'),
		'search_items' => __('Search Timeline Items', 'wptl'),
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
		'taxonomies' => [''],
		'supports' => array('title'),
		'rewrite' => true,
		'query_var' => true,
		'menu_icon' => 'dashicons-clock'
	);

	register_post_type('timeline', $args);

	flush_rewrite_rules();

}


// Setup the timeline edit page
$timeline_meta_boxes = array(
	array(
		'title' => __('Date', 'wptl'),
		'name' => 'wptl_timeline-date',
		'type' => 'inputtext',
		'extra' => __('Date of the event', 'wptl')
	),
	array(
		'title' => __('Body', 'wptl'),
		'name' => 'wptl_timeline-body',
		'type' => 'inputtext',
		'extra' => __('', 'wptl')
	),
	array(
		'title' => __('Document', 'wptl'),
		'name' => 'wptl_timeline-pdf',
		'type' => 'upload',
		'extra' => __('PDF files only', 'wptl')
	),
	array(
		'title' => __('Media', 'wptl'),
		'name' => 'wptl_timeline-media',
		'type' => 'inputtext',
		'extra' => __('Accepts youtube video ID. For example: <i><strong>dQw4w9WgXcQ</strong></i> in (www.youtube.com/watch?v=<i><strong>dQw4w9WgXcQ</strong></i>)', 'wptl')
	),
	array(
		'title' => __('Featured Image', 'wptl'),
		'name' => 'wptl_timeline-img',
		'type' => 'upload',
		'extra' => __('Image files only', 'wptl')
	),
	array(
		'title' => __('Fun Fact', 'wptl'),
		'name' => 'wptl_timeline-fact',
		'type' => 'inputtext',
		'extra' => __('', 'wptl')
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
// add table column in edit page
function wptl_show_timeline_column($columns)
{
	$columns = array(
		'cb' => '<input type="checkbox" />',
		'title' => 'Title',
		'date' => 'Last Edited'
	);
	return $columns;
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


/* Base function that returns a nice array of all the requested timeline. */
function wptl_get_timeline_items_array()
{

	$timeline_items = array();

	$timeline_items_q = new WP_Query(
		array(
			'post_type' => 'timeline',
			'order' => 'ASC',
			'paged' => '',
			'posts_per_page' => -1,
		)
	);

	while ($timeline_items_q->have_posts()) {

		$pub = array();

		$timeline_items_q->the_post();

		$pub['id'] = $timeline_items_q->post->ID;
		$pub['title'] = get_the_title();
		$pub['date'] = get_post_meta($pub['id'], 'wptl_timeline-date', true);
		$pub['body'] = get_post_meta($pub['id'], 'wptl_timeline-body', true);
		$pub['pdf_url'] = get_post_meta($pub['id'], 'wptl_timeline-pdf', true);
		$pub['media'] = get_post_meta($pub['id'], 'wptl_timeline-media', true);
		$pub['img'] = get_post_meta($pub['id'], 'wptl_timeline-img', true);
		$pub['fact'] = get_post_meta($pub['id'], 'wptl_timeline-fact', true);
		$timeline_items[] = $pub;

	}

	return $timeline_items;
}
/* The second base function that takes the raw timeline data and puts it
 * into nice html tags.
 *
 * Also needs the $options because some options are formatting related
 */
function wptl_get_timeline_items_formatted()
{

	// get the timeline data
	$timeline_items = wptl_get_timeline_items_array();

	$output = '';

	foreach ($timeline_items as $pub) {
		// Create the links string

		global $fact, $media, $pdf;

		if (!empty($pub['pdf_url'])) {
			$pdf = '<a class="btn btn-primary elementor-button" href="' . $pub['pdf_url'] . '"> ' . __('View PDF', 'wptl') . '</a>';
		}
		if (!empty($pub['media'])) {
			$media = '<iframe class="timeline__iframe" src="https://www.youtube.com/embed/' . $pub['media'] . '" title="YouTube video player" frameborder="0"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
				referrerpolicy="strict-origin-when-cross-origin" allowfullscreen="true">';
		}
		if (!empty($pub['fact'])) {
			$fact = '<div class="timeline__fun-fact">
				<p class="timeline__fun-fact-heading"><strong>Fun Fact</strong></p>
				<p><i>' . $pub['fact'] . '</i></p></div>';
		}

		$links_str = '
					<div class="timeline__item">
						<div class="timeline__content">
							<h1>' . $pub['date'] . '</h1>
							<h2>' . $pub['title'] . '</h2>
							<div class="timeline__body">
								<div class="timeline__body-text">
									' . $pub['body'] . '
								</div>
								'. $media . '
							</div>
							' . $pdf . '
							' . $fact . '
						</div>
					</div>
				';


		$output .= $links_str;
	}

	return $output;
}

function wptl_shortcode()
{

	return '   
		<style>
	  
		.timeline {
			padding: 5%;
		}

		.timeline__fun-fact {
			background: #f5f5f5;
			border-radius: 10px;
			padding: 5px;
			padding: 5px 10px 2.5px 10px;
			border: 1px solid #ccc;
			margin-top: 10px;
		}

		.timeline__fun-fact-heading::before {
			background-image: url("' . plugin_dir_url(__FILE__) . 'images/info-square.svg");
			display: inline-block;
			content: "";
			background-repeat: no-repeat;
			background-size: 16px 16px;
			width: 16px;
			height: 16px;
			transform: translateY(0.250em);
			margin-right: 10px;
			margin-left: 10px;
		}

		.timeline__fun-fact-heading {
			border: 1px solid #ccc;
			border-radius: 75px;
			display: flex;
			max-width: 112px;
			text-align: center;
			padding: 2.5px
		}

		.timeline__iframe {
			border-radius: 5px;
			margin-bottom: 10px;
			border: 1px #aaa solid;
			width: auto;
			height: auto;
			overflow: hidden;
			min-height: 200px;
			min-width: 300px;

		}

		.timeline__body a {
			width: 100%;
			height: 100%;
		}

		.timeline__body {
			display: flex;
			flex-direction: row;
		}

		.timeline__body-text {
			.btn {
				max-width: 250px;
				max-height: 40px;
				margin: auto;
				position: absolute;
				bottom: 25%;
			}
		}
	</style>
	<script>
		timeline(document.querySelectorAll(".timeline"), {
			forceVerticalMode: 1300,
			mode: "vertical",
			visibleItems: 15
		});
	</script>
	<div class="timeline">
		<div class="timeline__wrap">
			<div class="timeline__items">' . wptl_get_timeline_items_formatted() . '</div>
		</div>
	</div>';
}

?>