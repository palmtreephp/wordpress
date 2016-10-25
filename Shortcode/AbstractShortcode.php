<?php

namespace Palmtree\WordPress\Shortcode;

abstract class AbstractShortcode {
	protected $key;
	protected $defaults = [];

	public function getDefaults() {
		return $this->defaults;
	}

	public function getKey() {
		return $this->key;
	}

	public function getAttributes( $attributes = '' ) {
		return shortcode_atts( $this->getDefaults(), $attributes, $this->getKey() );
	}
}
