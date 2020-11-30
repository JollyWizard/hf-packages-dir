<?php

// Must come before `use` statements.
namespace hf\soundcloudsync;

use \Symfony\Component\Yaml\Yaml;

if ( ! class_exists( 'WP_CLI' ) )
{
    return;
}

function log($str)
{
    \WP_CLI::log($str);
}

function queryMatchResults($query_args)
{
    $r = [];

    $query = new \WP_Query($query_args);
    while ( $query->have_posts() ) : $query->the_post();
        $info = [
            'id' => get_the_ID()
        ,	'title' => get_the_title()
        ];

        array_push($r, $info);
    endwhile;

    return $r;
}

function findPostsWithSoundcloudUrl($sc_row)
{
    $r = Array();
    $sc_url = $sc_row["webpage_url"];

    $args = array(
      'post_type' => 'session'
    , 'nopaging' => 'true'
    , 'meta_query' => array (
            array(
                'key'=>'soundcloud_url'
                , 'value'=>$sc_url
                , 'compare'=>'LIKE'
            )
        )
    , 'order' => 'ASC'
    );

    return queryMatchResults($args);
}

function findPostWithMatchingBaseName(&$sc_row)
{
    $r = [];

    $slug = $sc_row['webpage_url_basename'];

    $args = array(
      'post_type' => 'session'
    , 'name' => $slug
    );

    $results = queryMatchResults($args);
    if (count ($results) > 0 ) array_push($r, ...$results);

    return $r;
}

function sortMatchResults($results, $baseId, $base, &$yesList, &$noList)
{
    if ( count($results) > 0 )
    {
        foreach ($results as $result)
        {
            $matchString = json_encode($result);
            log("[$baseId] matches: $matchString");

            $base["match_to"] = $matchString;
            $yesList[] = $base;
        }
    }
    else
    {
        $noList[] = $base;
    }
}

/*
    This is the first processing stage, where the prexisting rows are separated from
    the rows that need processing.

    `workorder['exists']` : will contain the input rows that are already attached to sessions.
    The post id will be added at.

    `workorder['missing']` : will contain the input rows that are not attached.
 */
function filterAlready(&$workorder)
{
    $input = &$workorder['input'];
    $already = &$workorder['already'];
    $todo = &$workorder['todo'];

    \WP_CLI::log("== Checking list for existing urls.");

    foreach ($input as $key => $value)
    {
        $row = (array) $value;
        $matches = findPostsWithSoundcloudUrl($row);

        sortMatchResults($matches, $row['webpage_url_basename'], $row, $already, $todo);
    }

    $todoCount = count($todo);
    $alreadyCount = count($already);
    log("TODO: $todoCount");
    log("ALREADY: $alreadyCount");
}

/*
    This function is responsible for sorting the results into the action lists.
*/
function match_todo_list(&$workorder)
{
    $todo = &$workorder['todo'];
    $matched = &$workorder['matched'];
    $unmatched = &$workorder['unmatched'];

    foreach ($todo as $key => $value)
    {
        $row = (array) $value;

        $matches = findPostWithMatchingBaseName($row);

        sortMatchResults($matches, $row['webpage_url_basename'], $row, $matched, $unmatched);
    }
}

/**
 *	Main function of this command.
 */
$wp_cli_command_hf_soundcloudsync = function()
{
    $JSONtext = file_get_contents(__DIR__ . '/dump.filtered.json');
    $ParsedJSON = json_decode ($JSONtext);

    $workorder = [
      "input"=>$ParsedJSON
    , "already"=>[]
    , "todo"=>[]
    , "matched"=>[]
    , "unmatched"=>[]
    , "output"=>[]
    ];

    filterAlready($workorder);
    log("= Already Existing Keys... " .count($workorder['already']) );
    \WP_CLI\Utils\format_items("table", $workorder['already'], ["webpage_url_basename", "match_to"]);

    log("= Keys to Process... ". count($workorder['todo']) );
    \WP_CLI\Utils\format_items("table", $workorder['todo'], ["webpage_url_basename"]);

    log("= Processing Keys");
    match_todo_list($workorder);


    /* Parse `hf-session-number` from soundcloud title. */

    //\WP_CLI\Utils\format_items("table", $ParsedJSON, Array("title", "webpage_url"));

    \WP_CLI::success( "hf-soundcloudsync::Complete" );
};

\WP_CLI::add_command( 'hf-soundcloudsync', $wp_cli_command_hf_soundcloudsync );
