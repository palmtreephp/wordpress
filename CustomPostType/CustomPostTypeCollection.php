<?php

namespace Palmtree\WordPress\CustomPostType;

use Palmtree\WordPress\AbstractCollection;

class CustomPostTypeCollection extends AbstractCollection {
	protected $itemClass = CustomPostType::class;

	public function addItem( $key, $item ) {
		if ( is_array( $item ) && ! array_key_exists( 'postType', $item ) ) {
			$item['postType'] = $key;
		}

		return parent::addItem( $key, $item );
	}
}
