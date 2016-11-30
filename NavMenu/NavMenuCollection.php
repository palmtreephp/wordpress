<?php

namespace Palmtree\WordPress\NavMenu;

use Palmtree\WordPress\AbstractCollection;

class NavMenuCollection extends AbstractCollection  {
	public function __construct() {
		add_action( 'after_setup_theme', [ $this, '_registerMenus' ] );
	}

	/**
	 *
	 */
	public function _registerMenus() {
		register_nav_menus( $this->getItems() );
	}

	/**
	 * @param string $menu
	 * @param array  $args
	 */
	public function navMenu( $menu = '', $args = [] ) {
		$defaults = [
			'menu'       => $menu,
			'menu_class' => 'clearfix',
			'container'  => null,
			'walker'     => new BootstrapNavMenuWalker(),
		];

		$args = wp_parse_args( $args, $defaults );

		wp_nav_menu( $args );
	}
}
