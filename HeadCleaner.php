<?php

namespace Palmtree\WordPress;

class HeadCleaner {
	public $callbacks = [
		'wp_generator',
		'rsd_link',
		'wlwmanifest_link',
		'wp_shortlink_wp_head',
	];

	public function clean() {
		foreach ( $this->callbacks as $callback => $priority ) {
			if ( is_int( $callback ) ) {
				$callback = $priority;
				$priority = 10;
			}

			remove_action( 'wp_head', $callback, $priority );
		}
	}

	public function removeCommentsFeedLink() {
		add_filter( 'feed_links_show_comments_feed', '__return_false' );
	}
}
