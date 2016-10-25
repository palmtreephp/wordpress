<?php

namespace Palmtree\WordPress;

class CategorySlugRemover {
	public function __construct() {
		add_filter( 'category_link', [ $this, '_filterCategoryLink' ], 10 );
	}

	public function _filterCategoryLink( $link ) {
		$blogHome = get_permalink( get_option( 'page_for_posts' ) );

		$pattern = '~^(' . preg_quote( trailingslashit( $blogHome ), '~' ) . ')category/(.+/?)$~';

		$link = preg_replace( $pattern, '$1$2', $link );

		return $link;
	}
}
