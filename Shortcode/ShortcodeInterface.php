<?php

namespace Palmtree\WordPress\Shortcode;

interface ShortcodeInterface {
	/**
	 * @return string
	 */
	public function getKey();

	/**
	 * @param array $atts
	 *
	 * @return mixed
	 */
	public function getOutput( $atts );
}
