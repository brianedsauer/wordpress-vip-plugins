<?php

// Load compat layer for Co-Authors Plus
if ( function_exists( 'get_coauthors' ) && ! defined( 'CAP_IA_COMPAT' ) ) {
	include(  dirname( __FILE__ ) . '/compat/class-instant-articles-co-authors-plus.php' );
	$cap = new Instant_Articles_Co_Authors_Plus;
	$cap->init();
}

// Load compat layer for Yoast SEO
if ( defined( 'WPSEO_VERSION' ) && ! defined( 'WPSEO_IA_COMPAT' ) ) {
	include(  dirname( __FILE__ ) . '/compat/class-instant-articles-yoast-seo.php' );
	$yseo = new Instant_Articles_Yoast_SEO;
	$yseo->init();
}

// Load support for Google Analytics for WordPress (Google Analytics by Yoast)
if ( defined( 'GAWP_VERSION' ) && ! defined( 'GAWP_IA_COMPAT' ) ) {
	include( dirname( __FILE__ ) . '/compat/class-instant-articles-google-analytics-for-wordpress.php' );
	$gawp = new Instant_Articles_Google_Analytics_For_WordPress;
	$gawp->init();
}

