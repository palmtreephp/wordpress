<?php

namespace Palmtree\WordPress\Shortcode;

use Palmtree\ArgParser\ArgParser;
use Palmtree\WordPress\AbstractCollection;

class ShortcodeCollection extends AbstractCollection  {
	protected $prefix = '';

	public function __construct( array $args = [] ) {
		$this->parseArgs( $args );
		add_action( 'init', [ $this, '_registerShortcodes' ] );
	}

	public function setPrefix( $prefix ) {
		$this->prefix = $prefix;

		return $this;
	}

	public function _registerShortcodes() {
		foreach ( $this->getItems() as $shortcode ) {
			/** @var ShortcodeInterface $shortcode */
			add_shortcode( $this->prefix . $shortcode->getKey(), [ $shortcode, 'getOutput' ] );
		}
	}

	protected function parseArgs( $args ) {
		$parser = new ArgParser( $args );

		$parser->parseSetters( $this );
	}
}
