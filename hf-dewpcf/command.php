<?php

namespace hf\dewpcf;

if ( ! class_exists( 'WP_CLI' ) ) {
    return;
}

/**
	If the `$wpcf_key` is present, replace it with the `$hf_key`, translate the timestamp to human readable form, and log.
*/
function convert_meta_timestamp ($ID, $wpcf_key, $hf_key, &$output_log) {
    $deleted = false;
    $val = get_post_meta($ID, $wpcf_key, true);

    if ($val != null)
    {
        $wpcf = intval($session[$wpcf_key]);
        $datestring = gmdate("YYYY-mm-dd", $wpcf);
        update_post_meta($ID, $hf_key, $datestring);

        //Mark deleted in output table.
        //$deleted = delete_post_meta($session['ID'], $wpcf_key);

        // Log change to CLI table.
        $log = array(
          'post-id'     =>  $ID
        , 'wpcf-key'    =>  $wpcf_key
        , 'wpcf-value'  =>  $wpcf
        , 'hf-key'      =>  $hf_key
        , 'hf-val'      =>  $val
        , 'wpcf-deleted' => $deleted
        );
        array_push($output, $log);
    }
}

/**
	If the `$wpcf_key` is present, replace it with the `$hf_key`, same value, and log.
*/
function copy_meta($ID, $wpcf_key, $hf_key, &$output)
{
    $deleted = false;
    $val = get_post_meta($ID, $wpcf_key, true);
    if ($val != null)
    {
        // Generate key and update value.
        update_post_meta($ID, $hf_key, $val);

        // Delete old value.
        $deleted = delete_post_meta($ID, $wpcf_key);

        // Log change to CLI table.
        $log = array(
          'post-id'      => $ID
        , 'wpcf-key'     => $wpcf_key
        , 'wpcf-value'   => $val
        , 'hf-key'       => $hf_key
        , 'hf-val'       => $val
        , 'wpcf-deleted' => $deleted
        );
        array_push($output, $log);
    }
}

/**
 * Package Main Function: Converts the legacy `wpcf-*` fields to text friendly `hf-*` fields.
 */
 $wp_cli_command_hf_dewpcf = function() {
    $args = array(
      'post_type' => 'session'
    , 'nopaging' => 'true'
    , 'order' => 'ASC'
    );
    $wp_query = new \WP_Query($args);

    $wpcf_copy_mappings = array(
        'wpcf-number'   => 'hf-session-number'
    ,   'wpcf-duration' => 'hf-session-duration'
    );

    $wpcf_timestamp_mappings = array(
        'wpcf-date-of-the-session-recording' => 'hf-session-date'
    );

    $output_log = array();

    while ( $wp_query->have_posts() ) : $wp_query->the_post();

        $ID = get_the_ID();

        foreach($wpcf_copy_mappings as $wpcf_key => $hf_key)
        {
            copy_meta($ID, $wpcf_key, $hf_key, $output_log);
        }

        foreach($wpcf_timestamp_mappings as $wpcf_key => $hf_key)
        {
            convert_meta_timestamp($ID, $wpcf_key, $hf_key, $output_log);
        }

    endwhile;

    $log_keys = array(
      'post-id'
    , 'wpcf-key'
    , 'wpcf-value'
    , 'hf-key'
    , 'hf-val'
    , 'wpcf-deleted'
    );
    $all_keys = join(',',$log_keys);

    \WP_CLI\Utils\format_items('table', $output_log, $all_keys);

    \WP_CLI::success( "hf-cleanup::Complete" );
};
\WP_CLI::add_command( 'hf-dewpcf', $wp_cli_command_hf_dewpcf );
