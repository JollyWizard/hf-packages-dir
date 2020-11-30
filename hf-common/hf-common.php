<?php

// PHP namespace must precede package contents and `use` statements.
namespace hf\common;

/*\
=== WP_CLI | PACKAGE | LIBRARY
|||
 |  This module provides helpers related to the higher forces wordpress data.
|||
\*/

/**
  Run a query using the provided `\WP_Query($args)`, and extract the list of post ids and titles.

  @TODO: add optional callback parameter(s) which is used to add other fields.
*/
function WP_Query_Results($wp_query_args)
{
	$r = [];

	$query = new \WP_Query($wp_query_args);
	while ( $query->have_posts() ) : $query->the_post();
		$info = [
		  'id' => get_the_ID()
		, 'title' => get_the_title()
		];

		array_push($r, $info);
	endwhile;

	return $r;
}

/**
  Get all the ids for all posts with type `session`.
*/
function hf_session_ids()
{
  $args = array(
    'post_type' => 'session'
  , 'nopaging' => true	// all results.
  );
  return WP_Query_Results($args);
}

/**
  These are filters that can be used on meta keys, i.e. `array_filter($meta_kvs, MetaFilter::___)`.
*/
class MetaFilters
{

  /**
	Wordpress internal meta key.  Has key that start with `_`.
  */
  static function is_hidden_meta_key ($meta_key)
  {
	return preg_match("/^[_].*$/", $meta_key);
  }

  /**
	Not a Wordpress internal meta key.
  */
  static function is_visible_meta_key ($meta_key)
  {
	return !self::is_hidden_meta_key($meta_key);
  }

  /**
    This library starts most keys with `hf-*`.
  */
  static function is_hf_meta_key ($meta_key)
  {
	return preg_match("/^hf-.*$/", $meta_key);
  }

  /**
    Soundclound data has its own prefix, `soundcloud-*`.
  */
  static function is_soundcloud($meta_key)
  {
	return preg_match("/^soundcloud-.*$/", $meta_key);
  }

  static $is_hidden = __CLASS__."::is_hidden_meta_key";
  static $is_visible = __CLASS__."::is_visible_meta_key";

  static $is_hf = __CLASS__."::is_hf_meta_key";

  static $is_soundcloud = __CLASS__."::is_soundcloud_meta_key";

} // end class MetaFilters
