<?php

namespace Palmtree\WordPress;

class Pagination {
	private $query;
	private $links;

	public function __construct( $query = null ) {
		$this->query = ( $query ) ? $query : $GLOBALS['wp_query'];
	}

	public function getLinks() {
		if ( $this->links === null ) {
			$this->generateLinks();
		}

		return $this->links;
	}

	protected function generateLinks() {
		$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$big   = 999999999; // need an unlikely integer

		$this->links = paginate_links( [
			'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'    => '?paged=%#%',
			'current'   => $paged,
			'total'     => $this->query->max_num_pages,
			'show_all'  => true,
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
			'type'      => 'array',
		] );
	}
}
