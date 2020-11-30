<?php

/*\
=== WP_CLI | PACKAGE | NAMESPACES
\*/
namespace hf\sessionbackup;

use \Symfony\Component\Yaml\Yaml;
use \HF\Common as HF;

use function \JW\WP_CLI_Utils\WP_Command;
use function \JW\WP_CLI_Utils\Build_Path;

/*\
=== WP_CLI | PACKAGE | SETUP
\*/
WP_Command(__NAMESPACE__)->Register();

/*\
=== WP_CLI | PACKAGE | LIBRARY
\*/

/**
  Export all session files to the export target area.
*/
function Command_Function ()
{
  $WP_Command = WP_Command(__NAMESPACE__);
  $sessions = HF\hf_session_ids();

  // This is will compile all files to.
  $toc = Array();

  $export_dir_title = 'export.' . (new \DateTime())->format('Y-m-d_Hi');
  $export_dir = $WP_Command->ContentPath($export_dir_title);

  if(!file_exists($export_dir)) mkdir($export_dir, 0777, true);

  \WP_CLI::log("USING EXPORT DIR: " . $export_dir);

  \WP_CLI::confirm('');

  foreach ($sessions as $s)
  {

    // Pull the id from the input data.
    $id = $s['id'];

    $p = get_post($id);

    $slug = $p->post_name;

    $index = Array(
      'id' => $id
    , 'title' => $p->post_title
    , 'author' => get_the_author_meta("display_name", $p->post_author)
    , 'slug' => $p->post_name
    , 'date' => $p->post_date
    , 'modified' => $p->post_modified
    );

    $contents = $p->post_content;

    $meta = get_post_meta($id);
    $meta = array_filter($meta, HF\MetaFilters::$is_visible, ARRAY_FILTER_USE_KEY );

    $index['meta'] = $meta;

    $index_yml = Yaml::dump($index);
    $meta_yml = Yaml::dump($meta);

    //----

    $export_file_index = "$slug.index.yml";
    $export_file_contents = "$slug.content.txt";
    $export_file_meta = "$slug.meta.yml";

    $export_path_index = Build_Path($export_dir, $export_file_index);
    $export_path_contents = Build_Path($export_dir, $export_file_contents);
    $export_path_meta = Build_Path($export_dir, $export_file_meta);

    \WP_CLI::log("EXPORTING : " . $export_file_index);
    file_put_contents($export_path_index, $index_yml);

    \WP_CLI::log("EXPORTING : " . $export_file_contents );
    file_put_contents($export_path_contents, $contents);

    \WP_CLI::log("EXPORTING : " . $export_file_meta );
    file_put_contents($export_path_meta, $meta_yml);

    // The entry for the table of contents file.
    $entry = Array(
      'id' => $id
    , 'slug' => $p->post_name
    , 'index-file' => $export_file_index
    , 'content-file' => $export_file_contents
    , 'meta_file' => $export_file_meta
    );
    $toc[$id] = $entry;
  }

  $export_file_toc = Build_Path($export_dir, "_toc.yml");
  $toc_yml = Yaml::dump($toc);
  file_put_contents($export_file_toc, $toc_yml);
}
