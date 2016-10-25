<?php

namespace Palmtree\WordPress;

abstract class AbstractCustomPostType {
	public $post_type;
	protected $name;
	protected $singular_name;
	protected $slug;
	protected $front;

	protected $is_public = true;

	protected $args = [];
	protected $labels = [];

	protected $custom_enter_title_text = false;

	protected $taxonomies = [];

	public function __construct() {
		$this->setup_properties();

		$this->args = wp_parse_args( $this->args, $this->get_default_args() );

		add_action( 'init', [ $this, 'register' ] );
		add_action( 'pre_get_posts', [ $this, 'change_default_orderby' ] );
		add_action( 'admin_menu', [ $this, 'remove_page_attributes' ] );

		if ( $this->custom_enter_title_text ) {
			add_filter( 'enter_title_here', [ $this, 'custom_enter_title' ] );
		}
	}

	protected function add_taxonomy( $slug, $name = '', $args = [] ) {
		$taxonomy = new CustomTaxonomy( $slug, $name, $this->post_type, $args );

		$this->taxonomies[ $slug ] = $taxonomy;
	}

	protected function setup_properties() {
		if ( empty( $this->post_type ) ) {
			$this->post_type = $this->get_post_type_from_filename();
		}

		if ( empty( $this->name ) ) {
			$this->name = ucwords( str_replace( '_', ' ', $this->post_type ) );
		}

		if ( empty( $this->singular_name ) ) {
			$this->singular_name = $this->name;
			$this->name          = $this->singular_name . 's';
		}

		if ( empty( $this->slug ) ) {
			$this->slug = $this->post_type . 's';
		}

		if ( empty( $this->labels ) && ! empty( $this->name ) && ! empty( $this->singular_name ) ) {
			$this->labels = $this->get_labels();
		}

		if ( empty( $this->slug ) ) {
			$this->slug = $this->post_type;
		}
	}

	/**
	 * Returns an array of all posts in the CPT
	 * ordered by their menu order.
	 *
	 * @param array $args Optional array of args to pass to the WP_Query.
	 *
	 * @return \WP_Post[]
	 */
	public function get_all_posts( $args = [] ) {
		$defaults = [
			'post_type' => $this->post_type,
			'nopaging'  => true,
			'orderby'   => 'menu_order',
			'order'     => 'asc',
		];

		$args = wp_parse_args( $args, $defaults );

		$query = new \WP_Query( $args );

		return $query->posts;
	}

	/**
	 * Returns an array of default arguments to be merged
	 * with the $this->args array before being passed to register_post_type().
	 * Also sets the relevant arguments when the CPT is set to non-public.
	 * @return array
	 */
	protected function get_default_args() {
		$defaults = [
			'labels'       => $this->labels,
			'hierarchical' => false,

			'supports' => [ 'title', 'editor', 'thumbnail', 'page-attributes' ],

			'show_in_nav_menus' => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'menu_position'     => 20,

			'has_archive' => false,
			'can_export'  => true,

			'rewrite'              => [ 'slug' => $this->slug, 'with_front' => false, 'feeds' => false ],
			'capability_type'      => 'post',
			'register_meta_box_cb' => '',

			'public'              => true,
			'publicly_queryable'  => true,
			'exclude_from_search' => false,
			'query_var'           => true,
		];

		if ( ! $this->is_public ) {
			$defaults = array_merge( $defaults, [
				'public'              => false,
				'publicly_queryable'  => false,
				'exclude_from_search' => true,
				'query_var'           => false,
				'rewrite'             => false,
			] );
		}

		return $defaults;
	}

	/**
	 * Returns an array of labels to be passed to register_post_type().
	 * @return array
	 */
	protected function get_labels() {
		return [
			'name'               => _x( $this->name, $this->post_type ),
			'singular_name'      => _x( $this->singular_name, $this->post_type ),
			'add_new'            => _x( 'Add New', $this->post_type ),
			'add_new_item'       => _x( 'Add New ' . $this->singular_name, $this->post_type ),
			'edit_item'          => _x( 'Edit ' . $this->singular_name, $this->post_type ),
			'new_item'           => _x( 'New ' . $this->singular_name, $this->post_type ),
			'view_item'          => _x( 'View ' . $this->singular_name, $this->post_type ),
			'search_items'       => _x( 'Search ' . $this->name, $this->post_type ),
			'not_found'          => _x( 'No ' . $this->name . ' found', $this->post_type ),
			'not_found_in_trash' => _x( 'No ' . $this->name . ' found in Trash', $this->post_type ),
			'parent_item_colon'  => _x( 'Parent ' . $this->singular_name . ':', $this->post_type ),
			'menu_name'          => _x( $this->name, $this->post_type ),
		];
	}

	/**
	 * Callback for the 'init' action.
	 * Registers the CPT with WordPress.
	 */
	public function register() {
		register_post_type( $this->post_type, $this->args );
	}

	/**
	 * Callback for the 'pre_get_posts' action.
	 * Sets the default orderby and order parameters of backend
	 * queries so CPTs can be ordered with the Simple Page Ordering plugin.
	 *
	 * @param \WP_Query $query
	 */
	public function change_default_orderby( $query ) {
		if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ||
		     ! class_exists( 'Simple_Page_Ordering' ) || $query->get( 'post_type' ) !== $this->post_type
		) {
			return;
		}

		$orderby = $query->get( 'orderby' );

		if ( empty( $orderby ) ) {
			$query->set( 'orderby', 'menu_order+title' );
			$query->set( 'order', 'asc' );
		}
	}

	/**
	 * Callback for the 'admin_menu' action.
	 * Removes the page attributes metabox for our custom post types.
	 */
	public function remove_page_attributes() {
		remove_meta_box( 'pageparentdiv', $this->post_type, 'side' );
	}

	/**
	 * Callback for the 'enter_title_here' filter.
	 * Allows CPTs to set their own e.g "Enter author name".
	 *
	 * @param string $input
	 *
	 * @return string
	 */
	public function custom_enter_title( $input ) {
		if ( get_post_type() === $this->post_type ) {
			return $this->custom_enter_title_text;
		}

		return $input;
	}

	private function get_post_type_from_filename() {
		$rc = new \ReflectionClass( get_class( $this ) );

		$file = $rc->getFileName();

		$post_type = basename( $file, '.php' );

		return $post_type;
	}
}
