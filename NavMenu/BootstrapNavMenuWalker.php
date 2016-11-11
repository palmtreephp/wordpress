<?php

namespace Palmtree\WordPress\NavMenu;

class BootstrapNavMenuWalker extends \Walker_Nav_Menu {
	/**
	 * Start the element output.
	 *
	 * @param  string $output Passed by reference. Used to append additional content.
	 * @param  object $item   Menu item data object.
	 * @param  int    $depth  Depth of menu item. May be used for padding.
	 * @param  array  $args   Additional strings.
	 *
	 * @return void
	 */
	public function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
		$output .= '<li class="nav-item">';

		$active = false;

		$active_classes = [ 'current-menu-item', 'current-page-ancestor' ];
		$classes        = [ 'nav-link' ];

		$post              = get_post();
		$page_for_posts_id = (int) get_option( 'page_for_posts', 0 );

		if ( (int) $item->object_id === $page_for_posts_id ) {
			if ( is_category() || is_tag() || is_singular( 'post' ) || is_home() ) {
				$active = true;
			}
		} else {

			$queried_obj = get_queried_object();

			if ( $queried_obj instanceof \WP_Post ) {
				$queried_obj = get_post_type_object( $queried_obj->post_type );
			}

			if ( ! empty( $queried_obj->has_archive ) ) {
				$slug = is_string( $queried_obj->has_archive ) ? $queried_obj->has_archive : $queried_obj->rewrite['slug'];

				$query = new \WP_Query( [
					'post_type'      => 'page',
					'posts_per_page' => '1',
					'pagename'       => $slug,
				] );

				if ( $query->found_posts && (int) $query->post->ID === (int) $item->object_id ) {
					$active = true;
				}
			} else {

				// If the item classes array contains one of the WordPress active classes.
				$intersection = array_intersect( $item->classes, $active_classes );
				if ( ! empty( $intersection ) ) {
					$active = true;
				}
			}
		}

		if ( $active ) {
			$classes[] = 'active';
		}

		$attributes = 'class="' . implode( ' ', $classes ) . '"';

		if ( ! empty( $item->url ) ) {
			$attributes .= ' href="' . esc_attr( $item->url ) . '"';
		}

		$attributes = trim( $attributes );
		$title      = apply_filters( 'the_title', $item->title, $item->ID );

		$item_output = "$args->before<a $attributes>$args->link_before$title</a>"
		               . "$args->link_after$args->after";
		// Since $output is called by reference we don't need to return anything.
		$output .= apply_filters(
			'walker_nav_menu_start_el'
			, $item_output
			, $item
			, $depth
			, $args
		);
	}
}
