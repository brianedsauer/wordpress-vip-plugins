<?php

/*
 * Plugin Name: Cache Nav Menus
 * Description: Allows Core Nave Menus to be cached using WP.com's Advanced Post Cache
 * Author: Automattic
 */

function cache_nav_menu_parse_query( &$query ) {
	if ( !isset( $query->query_vars['post_type'] ) || 'nav_menu_item' !== $query->query_vars['post_type'] ) {
		return;
	}

	$query->query_vars['suppress_filters'] = false;
	$query->query_vars['cache_results'] = true;
}

add_action( 'parse_query', 'cache_nav_menu_parse_query' );

/**
 * Wrapper function around wp_nav_menu() that will cache the wp_nav_menu for all tag/category
 * pages used in the nav menus
 */ 
function wpcom_vip_cached_nav_menu( $args = array(), $prime_cache = false ) {
	global $wp_query;
	
	$queried_object_id = empty( $wp_query->queried_object_id ) ? 0 : (int) $wp_query->queried_object_id;
	
	$nav_menu_key = md5( serialize( $args ) . '-' . $queried_object_id );
	$my_args = wp_parse_args( $args );
	$my_args = apply_filters( 'wp_nav_menu_args', $my_args );
	$my_args = (object) $my_args;
	
	if ( ( isset( $my_args->echo ) && true === $my_args->echo ) || !isset( $my_args->echo ) ) {
		$echo = true;
	} else {
		$echo = false;
	}
	
	$skip_cache = false;
	$use_cache = ( true === $prime_cache ) ? false : true;
	
	if ( is_singular() ) {
		$skip_cache = true;
	} else if ( !in_array( $queried_object_id, wpcom_vip_get_nav_menu_cache_objects( $use_cache ) ) ) {
		$skip_cache = true;
	}
	
	if ( true === $skip_cache || true === $prime_cache || false === ( $nav_menu = get_transient( $nav_menu_key ) ) ) {
		if ( false === $echo ) {
			$nav_menu = wp_nav_menu( $args );
		} else {
			ob_start();
			wp_nav_menu( $args );
			$nav_menu = ob_get_clean();
		}
		if ( false === $skip_cache )
			set_transient( $nav_menu_key, $nav_menu );
	} 
	if ( true === $echo )
		echo $nav_menu;
	else
		return $nav_menu;
}


add_action( 'wp_update_nav_menu', 'wpcom_vip_update_nav_menu_objects' );
function wpcom_vip_update_nav_menu_objects( $menu_id = null, $menu_data = null ) {
	wpcom_vip_cached_nav_menu( array( 'echo' => false ), $prime_cache = true );
}

function wpcom_vip_get_nav_menu_cache_objects( $use_cache = true ) {
	$cache_key = 'wpcom_vip_nav_menu_cache_object_ids';
	$object_ids = get_transient( $cache_key );
	if ( true === $use_cache && !empty( $object_ids ) ) {
		return $object_ids;
	}

	$object_ids = $objects = array();
	
	$menus = wp_get_nav_menus();
	foreach ( $menus as $menu_maybe ) {
		if ( $menu_items = wp_get_nav_menu_items( $menu_maybe->term_id ) ) {
			foreach( $menu_items as $menu_item ) {
				if ( preg_match( "#.*/category/([^/]+)/?$#", $menu_item->url, $match ) )
					$objects['category'][] = $match[1];
				if ( preg_match( "#.*/tag/([^/]+)/?$#", $menu_item->url, $match ) )
					$objects['post_tag'][] = $match[1];
			}
		}
	}
	if ( !empty( $objects ) ) {
		foreach( $objects as $taxonomy => $term_names ) {
			foreach( $term_names as $term_name ) {
				$term = get_term_by( 'slug', $term_name, $taxonomy );
				if ( $term )
					$object_ids[] = $term->term_id;
			}
		}
	}
	
	$object_ids[] = 0; // that's for the homepage
	
	set_transient( $cache_key, $object_ids );
	return $object_ids;
}
