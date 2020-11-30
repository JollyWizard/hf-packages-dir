<?php

use \Symfony\Component\Yaml\Yaml;

if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

/*
    Use to log any post meta rewrites to the log that will be displayed at end of command.
*/
function replace_post_meta($ID, $meta_key, $newval, &$output_log)
{
    $oldval = get_post_meta($ID, $meta_key, true);
    update_post_meta($ID, $meta_key, $newval, $oldval);
    $log = array(
      'ID' => $ID
    , 'meta_key' => $meta_key
    , 'old_value' => $oldval
    , 'new_value' => $newval
    );
    array_push($output_log, $log);
}

function clean_timestamp($ID, &$output_log) {
    $meta_key = 'hf-session-date';
    $datestring_1 = get_post_meta($ID, $meta_key, true);

    if ($datestring_1 != null)
    {
        // Convert text to timestamp.
        $timestamp_1 = strtotime($datestring_1);

        // Format as per: https://www.php.net/manual/en/function.date.php
        $datestring_2 = date('YYYY-mm-dd', $timestamp_1);

        //@TODO: Bring back this check, but correct.
        //if ($datestring_1 != $datestring_2)
        {
            replace_post_meta($ID, $meta_key, $datestring_2, $output_log);
        }
    }
}

// @TODO: Move to `JW\WP_CLI\`.
function ensure_log_dir()
{
    $dir_path = 'wp-content/logs/';
    if (!is_dir($dir_path))
    {
        mkdir($dir_path, 0777, true);
    }
    return $dir_path;
}

// @TODO: Move to `JW\WP_CLI\`.
function save_log(&$output_log)
{
    //if (count($output_log)  0)
    {
        $yaml = Yaml::dump($output_log);
        $filename = ensure_log_dir()."log.hf-cleanup.".date('Y-m-d_Hi').".yml";
        file_put_contents($filename, $yaml);
    }
}

function normalize_session($ID, &$output_log)
{
	clean_timestamp($ID, $output_log);
	// fill in misisng numbers.
	// normalize post numbers to `xxx.y`
	// fill in missing dates.
}

/**
 * Normalize session data.
 */
 // @TODO: Convert to easy plugin; move log function to helper class.
 $wp_cli_command_hf_cleanup = function() {
    $args = array(
      'post_type' => 'session'
    , 'nopaging' => 'true'
    , 'order' => 'ASC'
    );
    $wp_query = new WP_Query($args);

    $output_log = array();

    while ( $wp_query->have_posts() ) : $wp_query->the_post();
        normalize_session(get_the_ID(), $output_log);
    endwhile;

    $log_keys = array(
        'ID'
    ,	'meta_key'
    ,	'old_value'
    , 'new_value'
    );

    WP_CLI\Utils\format_items('table', $output_log, $log_keys);
    save_log($output_log);
    WP_CLI::success( "hf-cleanup::Complete" );
};
WP_CLI::add_command( 'hf-cleanup', $wp_cli_command_hf_cleanup );
