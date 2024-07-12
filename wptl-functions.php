<?php

// how to print each meta box type
function wptl_print_option($args)
{
    switch ($args['type']) {
        case "inputtext":
            ?>
            <input type="text" name="<?php echo $args['name']; ?>" id="<?php echo $args['name']; ?>"
                value="<?php echo $args['value']; ?>" style="width:100%" />
            <p>
                <?php echo $args['extra']; ?>
            </p>
            <?php

            break;
        case "upload":

            $src = '';

            if (!empty($args['value'])) {
                $src = $args['value'];
            }

            ?>
            <input name="<?php echo $args['name']; ?>" type="hidden" id="upload_image_attachment_id"
                value="<?php echo esc_html($args['value']); ?>" />
            <input id="upload_image_text_meta" type="text" value="<?php echo $src; ?>" style="width:80%" />
            <input class="upload_image_button_meta" type="button" value="Upload" />

            <p>
                <?php echo $args['extra']; ?>
            </p>
            <?php

            break;
        case "reqinputtext":
            ?>
            <input type="text" required name="<?php echo $args['name']; ?>" id="<?php echo $args['name']; ?>"
                value="<?php echo $args['value']; ?>" style="width:100%" />
            <p>
                <?php echo $args['extra']; ?>
            </p>
            <?php

            break;
    }
}

/**
 * Adds a submenu page under a custom post type parent.
 */
function wptl_register_sub_page()
{
    add_submenu_page(
        'edit.php?post_type=timeline',
        'Timeline Settings',
        'Timeline Settings',
        'manage_options',
        'wptl_submenu',
        'wptl_submenu_callback'
    );
}

/**
 * Display callback for the submenu page.
 */
function wptl_submenu_callback()
{

    $timeline_asc_desc = get_option('timeline_asc_desc');
    $timeline_horz_vert = get_option('timeline_horz_vert');
    ?>
    <div class="wrap">
        <h2>Timeline Settings and Notes</h2>
        <h3>Timeline Order</h3>
        <p>Currently set to: <?php echo $timeline_asc_desc ?></p>
        <form action="admin-post.php" name='update_timeline_asc_desc_form' method="post">
            <input type="hidden" name="action" value="update_timeline_asc_desc" />
            <select id="timeline_asc_desc" name="timeline_asc_desc" value=''>
                <option value="<?php echo $timeline_asc_desc ?>">Select an option</option>
                <option value="ASC">Ascending</option>
                <option value="DESC">Descending</option>
            </select>
            <?php
            submit_button()
                ?>
        </form>
        <br/>
        <h3>Timeline Orientation</h3>
        <p>Currently set to: <?php echo $timeline_horz_vert ?></p>
        <form action="admin-post.php" name='update_timeline_horz_vert_form' method="post">
            <input type="hidden" name="action" value="update_timeline_horz_vert" />
            <select id="timeline_horz_vert" name="timeline_horz_vert" value=''>
                <option value="<?php echo $timeline_horz_vert ?>">Select an option</option>
                <option value="horizontal">Horizontal</option>
                <option value="vertical">Vertical</option>
            </select>
            <?php
            submit_button()
                ?>
        </form>
    </div>
    <?php
}

function asc_desc_do_update() {
    update_option('timeline_asc_desc', $_POST['timeline_asc_desc']);
    wp_redirect('admin.php?page=wptl_submenu');
}
function horz_vert_do_update() {
    update_option('timeline_horz_vert', $_POST['timeline_horz_vert']);
    wp_redirect('admin.php?page=wptl_submenu');
}

function wptl_convert_date($date)
#if the day =1 then only return the month
{
    if ($date->format('d') == "01") {
        return $date->format('F Y');
    } else {
        return $date->format('F d, Y');
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

        if ($opt['type'] != 'inputtext' & $opt['type'] != 'reqinputtext') {
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

// nonce Verification	
function wptl_set_nonce()
{
    wp_nonce_field(plugin_basename(__FILE__), 'wptl_timelinenonce');
}


/* save option function that trigger when saveing each post
 */
function wptl_save_option_meta($post_id)
{

    // Verification
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!isset($_POST['wptl_timelinenonce']))
        return;
    if (!wp_verify_nonce($_POST['wptl_timelinenonce'], plugin_basename(__FILE__)))
        return;

    if ($_POST['post_type'] == 'timeline') {
        if (!current_user_can('edit_post', $post_id))
            return;

        wptl_save_timeline_option_meta($post_id);
    }

}

/* function that save the meta to database if new data is exists and is not
 * equals to old one.
 */
function wptl_save_meta_data($post_id, $new_data, $old_data, $name)
{

    if ($new_data == $old_data) {
        add_post_meta($post_id, $name, $new_data, true);

    } else if (!$new_data) {
        delete_post_meta($post_id, $name, $old_data);

    } else if ($new_data != $old_data) {
        update_post_meta($post_id, $name, $new_data, $old_data);

    }
}

/* Takes all key,value pairs from $changes and if the key is present in
 * $base, sets $base[key] equal to $changes[key].
 */
function wptl_array_merge($base, $changes)
{
    if (!is_array($base) || !is_array($changes)) {
        return;
    }

    foreach ($changes as $key => $value) {
        if (array_key_exists($key, $base)) {
            $base[$key] = $value;
        }
    }
    return $base;
}



?>