<?php
/*
Plugin Name: Execution of All Things
Plugin URI: http://www.navidazimi.com/projects/wp-exec/
Description: This plugin facilitates users with the ability to create custom HTML tags which can be embedded inside posts or pages for dynamic content. You should consider this a transparent HTML-to-PHP API layer. For more information visit <a href="http://www.navidazimi.com/projects/wp-exec">here</a>. You get 10 points if you caught the Rilo Kiley reference.
Author: Navid Azimi
Author URI: http://www.navidazimi.com
Version: 0.5
*/

###############################################################################
#							CURRENT SUPPORTED TAGS
###############################################################################
#
# date
# usage: <exec type="date" />
# optional: format, date, gmt
#
# archives
# usage: <exec type="archives" />
# optional: mode, limit, format, before, after, count
#
# categories
# usage: <exec type="categories" />
# optional: optionall, all, sort_column, sort_order, file, list, optiondates,
#			optioncount, hide_empty, use_desc_for_title, children, child_of,
#			feed, feed_img, exclude, hierarchical
#
# function
# usage: <exec type="function" name="..." />
# optional: params, internal
#
# include
# usage: <exec type="include" id="#" />
# optional: field
#
# bloginfo
# usage: <exec type="bloginfo" show="..." />
# optiona: none
#
# links
# usage: <exec type="links" />
# optional:
#
###############################################################################

/*
 * This is a wrapper function which initiates the callback for the custom tag embedding.
 */
function wpexec_embed_callback( $content )
{
	return preg_replace_callback("|<exec type=[\"']?([a-zA-Z0-9]+)[\"']?( +(\w+)=[\"']?([^\"'/]+)[\"' ]?)* />|", 'wpexec_handler', $content); //'
}

/*
 * This function handles the real meat by handing off the work to helper functions.
 */
function wpexec_handler( $matches )
{
	// 0th and 1st element always exist
	$type = $matches[1];

	switch( $type )
	{
		case "date":		return wpexec_date($matches);		break;
		case "archives":	return wpexec_archives($matches);	break;
		case "categories":	return wpexec_categories($matches);	break;
		case "function":	return wpexec_function($matches);	break;
		case "include":		return wpexec_include($matches);	break;
		case "bloginfo":	return wpexec_bloginfo($matches);	break;
		case "links":		return wpexec_links($matches);		break;
	}

	return;
}

add_filter('the_content', 'wpexec_embed_callback', 1);

###############################################################################
############################ FUNCTIONS BEGIN HERE #############################
###############################################################################

#
# function: wpexec_date
# description: allows embedding the current time in any format
# usage: <exec type="date" />
# optional: format, date, gmt
#
function wpexec_date( $matches )
{
	// default values
	$format = "";
	$date = "mysql";
	$gmt = false;

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "format":	$format = $value;	break;
			case "date":	$date = $value;		break;
			case "gmt":
				if( $value == "false" || $value == "no" ) $gmt = false;
				else $gmt = true;
				break;
		}
	}
	if( $format == "" ) $format = get_settings('date_format') ." \a\\t ". get_settings('time_format');
	//current_time($date, $gmt)
	return date($format);
}

#
# function: wpexec_archives
# description: allows embedding archive links based on the WordPress built-in archives
# usage: <exec type="archives" />
# optional: mode, limit, format, before, after, count
#
function wpexec_archives( $matches )
{
	// default values
	$mode = "monthly";
	$limit = "";
	$format = "html";
	$before = "";
	$after = "";
	$count = 0;

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "mode":	$mode = $value;		break;
			case "limit":	$limit = $value;	break;
			case "format":	$format = $value;	break;
			case "before":	$before = $value;	break;
			case "after":	$after = $value;	break;
			case "count":
				if( $value == "false" || $value == "no" ) $count = 0;
				else $count = 1;
				break;
		}
	}

	ob_start();
	wp_get_archives("type=$mode&limit=$limit&format=$format&before=$before&after=$after&show_post_count=$count");
	$output = ob_get_contents();
	ob_clean();
	return $output;
}

#
# function: wpexec_categories
# description: allows embedding different posts or pages dynamically inside another
# usage: <exec type="categories" />
# optional: optionall, all, sort_column, sort_order, file, list, optiondates,
#			optioncount, hide_empty, use_desc_for_title, children, child_of,
#			feed, feed_img, exclude, hierarchical
#
function wpexec_categories( $matches )
{
	// default values
	$optionall = 0;
	$all = "ALL";
	$sort_column = "ID";
	$sort_order = "asc";
	$file = "index.php";
	$list = 1;
	$optiondates = 0;
	$optioncount = 0;
	$hide_empty = 1;
	$use_desc_for_title = 1;
	$children = 1;
	$child_of = 0;
	$feed = "";
	$feed_img = "";
	$exclude = "";
	$hierarchical = 1;

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "optionalall":
				if( $value == "false" || $value == "no" ) $optionall = false;
				else $optionall = true;
				break;
			case "all":			$all = $value;			break;
			case "sort_column":	$sort_column = $value;	break;
			case "sort_order":	$sort_order = $value;	break;
			case "file":		$file = $value;			break;
			case "list":
				if( $value == "false" || $value == "no" ) $list = 0;
				else $list = 1;
				break;
			case "optiondates":
				if( $value == "false" || $value == "no" ) $optiondates = 0;
				else $optiondates = 1;
				break;
			case "optioncount":
				if( $value == "false" || $value == "no" ) $optioncount = 0;
				else $optioncount = 1;
				break;
			case "hide_empty":
				if( $value == "false" || $value == "no" ) $hide_empty = 0;
				else $hide_empty = 1;
				break;
			case "use_desc_for_title":
				if( $value == "false" || $value == "no" ) $use_desc_for_title = 0;
				else $use_desc_for_title = 1;
				break;
			case "children":
				if( $value == "false" || $value == "no" ) $children = 0;
				else $children = 1;
				break;
			case "child_of":
				if( $value == "false" || $value == "no" ) $child_of = 0;
				else $child_of = 1;
				break;
			case "feed":		$feed = $value;			break;
			case "feed_img":	$feed_img = $value;		break;
			case "exclude":		$exclude = $value;		break;
			case "hierarchical":
				if( $value == "false" || $value == "no" ) $hierarchical = 0;
				else $hierarchical = 1;
				break;
		}
	}

	ob_start();
	wp_list_cats("optionall=$optionall&all=$all&sort_column=$sort_column&sort_order=$sort_order&file=$file&list=$list&optiondates=$optiondates&optioncount=$optioncount&hide_empty=$hide_empty&use_desc_for_title=$use_desc_for_title&children=$children&child_of=$child_of&feed=$feed&feed_img=$feed_img&exclude=$exclude&hierarchical=$hierarchical");
	$output = ob_get_contents();
	ob_clean();
	return $output;
}

#
# function: wpexec_include
# description: allows embedding different posts dynamically inside another
# usage: <exec type="include" id="#" />
# optional: field
#
function wpexec_include( $matches )
{
	// default values
	$id = 0;
	$field = "Content";

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "id":	$id = $value;	break;
			case "field":
				switch( strtolower($value) )
				{
					case "author_id":		$field = "Author_ID";		break;
					case "date":			$field = "Date";			break;
					case "content":			$field = "Content";			break;
					case "excerpt":			$field = "Excerpt";			break;
					case "title":			$field = "Title";			break;
					case "category":		$field = "Category";		break;
					case "post_status":		$field = "post_status";		break;
					case "comment_status":	$field = "comment_status";	break;
					case "ping_status":		$field = "ping_status";		break;
					case "post_password":	$field = "post_password";	break;
					case "to_ping":			$field = "to_ping";			break;
					case "pinged":			$field = "pinged";			break;
					case "post_name":		$field = "post_name";		break;
				}
				break;
		}
	}

	if( $id != 0 )
	{
		$posts = get_postdata( $id );
		return $posts[$field];
	}
	return;
}

#
# function: wpexec_function
# description: facilitates calling wordpress functions
# usage: <exec type="function" name="..." />
# optional: params, internal
#
function wpexec_function( $matches )
{
	// default values
	$function_name = "";
	$params = "";
	$internal = false;

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "name":	$function_name = $value;	break;
			case "params":	$params = $value;			break;
			case "internal":
				if( $value == "false" || $value == "no" ) $internal = false;
				else $internal = true;
				break;
		}
	}

	if( $internal && strlen($function_name) > 0 )
	{
		return $function_name($params);
	}
	else
	{
		if( function_exists($function_name) )
		{
			$param_arr = explode(",", $params);
			ob_start();
			call_user_func_array($function_name, $param_arr);
			$output = ob_get_contents();
			ob_clean();
		}
		else
		{
			$output = "call to $fuction_name($params) not found";
		}
	}
	return $output;
}

#
# function: wpexec_bloginfo
# description: allows for easy access from get_bloginfo($show)
# usage: <exec type="bloginfo" show="..." />
# optional: none
#
function wpexec_bloginfo( $matches )
{
	// default values
	$show = "name";

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "show": $show = $value; break;
		}
	}
	return get_bloginfo($show);
}

#
# function: wpexec_links
# description: allows for easy access to the get_links() functionality
# usage: <exec type="links" />
# optional: category, before, after, between, show_images, odd, even,
#			orderby, show_description, show_rating, limit, show_updated
#			sort
#
function wpexec_links( $matches )
{
	// default values
	$category = -1;
	$before = "";
	$after = "<br />";
	$between = " ";
	$orderby = "name";
	$show_description = true;
	$show_rating = false;
	$limit = -1;
	$show_updated = false;
	$sort = " ASC";
	$echo = true;

	// explode
	$size = preg_match_all("| +(\w+)=[\"']?([^\"'/]+)[\"' ]?|", $matches[0], $attributes);

	// any subsequent elements are in pairs of name/value
	for( $i = 1; $i <= $size; $i++ )
	{
		$name = $attributes[1][$i];
		$value = $attributes[2][$i];

		if( strlen($name) < 1 ) continue;

		switch( $name )
		{
			case "category":	$category = $value; break;
			case "before":		$before = $value; 	break;
			case "after":		$after = $value;	break;
			case "between":		$between = $value;	break;
			case "orderby":		$orderby = $value;	break;
			case "limit":		$limit = $value;	break;
			case "sort":		$sort = $value;		break;
			case "show_description":
				if( $value == "false" || $value == "no" ) $show_description = false;
				else $show_description = true;
				break;
			case "show_rating":
				if( $value == "false" || $value == "no" ) $show_rating = false;
				else $show_rating = true;
				break;
			case "show_updated":
				if( $value == "false" || $value == "no" ) $show_updated = false;
				else $show_updated = true;
				break;
		}
	}

	if( strtolower($sort) == "desc" )
	{
		$orderby = "_" . $orderby;
	}

	ob_start();
	get_links($category, $before, $after, $between, $orderby, $show_description, $show_rating, $limit, $show_updated, $echo);
	$output = ob_get_contents();
	ob_clean();
	return $output;
}
?>