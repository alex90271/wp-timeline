<?php
function timeline_shortcode()
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
        background-image: url("src/images/info-square.svg");
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
        flex-direction: row-reverse;
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
<div class="timeline">
    <div class="timeline__wrap">
        <div class="timeline__items">
            <div class="timeline__item">
                <div class="timeline__content">
                    <h1>December 2015</h1>
                    <h2>NCEP Production Suite Review: First presentation on unifying the production suite</h2>
                    <div class="timeline__body">
                        <div>
                            <iframe class="timeline__iframe"
                            src="/wp-content/plugins/timeline-js/pdfviewer#/wp-content/uploads/2024/01/UMAC_Final_Report_20151207-v14-1.pdf"
                            title="YouTube video player" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" allowfullscreen="true">
                        </iframe>
                        </div>
                        <div class="timeline__body-text">
                            <p>Presented by Hendrik Tolman, director of the Environmental Modeling Center (EMC), a
                                center at
                                NCEP. This presentation focused on data analysis for products and model coupling.</p>
                            <a class="btn btn-primary"
                            href="/wp-content/plugins/timeline-js/pdfviewer#/wp-content/uploads/2024/01/UMAC_Final_Report_20151207-v14-1.pdf"
                                target="_blank">View
                                the presentation here</a>
                        </div>
                    </div>
                    <div class="timeline__fun-fact">
                        <p class="timeline__fun-fact-heading"><strong>Fun Fact</strong></p>
                        <p><i> Initially, GFDLs FV3 was chosen over NCARs MPAS
                                dycore, but new
                                work is currently in
                                place to include MPAS as a second dycore</i></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<script>
    timeline(document.querySelectorAll(".timeline"), {
        forceVerticalMode: 1300,
        mode: "vertical",
        visibleItems: 4
    });
</script>
';
}

// how to print each meta box type
function wptl_print_option($opt)
{

    switch ($opt['type']) {
        case "inputtext":
            wptl_print_option_input_text($opt);
            break;
        case "upload":
            wptl_print_meta_upload($opt);
            break;
    }
}

// nonce Verification	
function wptl_set_nonce()
{
    wp_nonce_field(plugin_basename(__FILE__), 'wptl_timelinenonce');
}

// text => name, title, value, default
function wptl_print_option_input_text($args)
{

    ?>

    <input type="text" name="<?php echo $args['name']; ?>" id="<?php echo $args['name']; ?>"
        value="<?php echo $args['value']; ?>" style="width:100%" />
    <p>
        <?php echo $args['extra']; ?>
    </p>

    <?php

}

// text => name, title, value
function wptl_print_meta_upload($args)
{

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

}

/* save option function that trigger when saveing each post
 */
function wptl_save_option_meta($post_id)
{

    // Verification
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
    if (!isset($_POST['wptl_timelinebonce']))
        return;
    if (!wp_verify_nonce($_POST['wptl_timelinebonce'], plugin_basename(__FILE__)))
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