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
		'supports' => array('title', 'editor'),
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
		'title' => __('Document', 'wptl'),
		'name' => 'wptl_timeline-pdf',
		'type' => 'upload',
		'extra' => __('PDF files only', 'wptl')
	),
	array(
		'title' => __('Link', 'wptl'),
		'name' => 'wptl_timeline-link',
		'type' => 'inputtext',
		'extra' => __('Accepts any URL. Must include https://', 'wptl')
	),
	array(
		'title' => __('Video', 'wptl'),
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
		'date' => 'Event Date'
	);
	return $columns;
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
			'order' => get_option('timeline_asc_desc'),
			'orderby' => 'date',
			'posts_per_page' => -1,
		)
	);

	while ($timeline_items_q->have_posts()) {

		$pub = array();

		$timeline_items_q->the_post();

		$pub['id'] = $timeline_items_q->post->ID;
		$pub['title'] = get_the_title();
		$pub['date'] = get_post_datetime();
		$pub['pdf_url'] = get_post_meta($pub['id'], 'wptl_timeline-pdf', true);
		$pub['media'] = get_post_meta($pub['id'], 'wptl_timeline-media', true);
		$pub['img'] = get_post_meta($pub['id'], 'wptl_timeline-img', true);
		$pub['fact'] = get_post_meta($pub['id'], 'wptl_timeline-fact', true);
		$pub['link'] = get_post_meta($pub['id'], 'wptl_timeline-link', true);
		$pub['body'] = get_post_field('post_content', $pub['id']);
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

		$fact;
		$media;
		$pdf;
		$img;
		$link;

		if (!empty($pub['img'])) {
			$img = '<img class="timeline__img" src="' . $pub['img'] . '"><img/>';
		} else {
			$img = null;
		}


		if (!empty($pub['pdf_url'])) {
			$pdf = '<a class="abtn abtn-primary" href="' . $pub['pdf_url'] . '"> ' . __('Read more', 'wptl') . '</a>';
		} else {
			$pdf = null;
		}

		if (!empty($pub['link'])) {
			if (!empty($pub['pdf_url'])) {
				$link = '<a class="abtn abtn-secondary" href="' . $pub['link'] . '"> ' . __('Read more', 'wptl') . '</a>';
			} else {
				$link = '<a class="abtn abtn-primary" href="' . $pub['link'] . '"> ' . __('Read more', 'wptl') . '</a>';
			}
		} else {
			$link = null;
		}

		if (!empty($pub['media'])) {
			$media = '<iframe class="timeline__iframe" src="https://www.youtube.com/embed/' . $pub['media'] . '" title="YouTube video player" frameborder="0"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
				referrerpolicy="strict-origin-when-cross-origin" allowfullscreen="true"></iframe>';
		} else {
			$media = null;
		}


		if (!empty($pub['fact'])) {
			$fact = '<div class="timeline__fun-fact">
				<p class="timeline__fun-fact-heading"><strong>Fun Fact</strong></p>
				<p><i>' . $pub['fact'] . '</i></p></div>';
		} else {
			$fact = null;
		}

		$links_str = '
					<div id="timeline_element_' . $pub['id'] . '" class="timeline__item">
						<div class="timeline__content">
							<h1>' . wptl_convert_date($pub['date']) . '</h1>
							<h2>' . $pub['title'] . '</h2>
							' . $img . '
							<div class="timeline__body">
								<div class="timeline__body-text">
									' . $pub['body'] . '
								</div>
								' . $media . '
							</div>
							<p class="timeline__links">' . $pdf . $link . '</p>
							<p>' . $fact . '</p>
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
	<div class="timeline">
		<div class="timeline__wrap">
			<div class="timeline__items">' . wptl_get_timeline_items_formatted() . '</div>
		</div>
	</div>
	<script>
	timeline(document.querySelectorAll(".timeline"), {
		forceVerticalMode: 986,
		mode: "'. get_option('timeline_horz_vert').'",
	});
	</script>';
}

?>