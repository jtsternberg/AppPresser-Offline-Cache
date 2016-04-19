<?php
/**
 * AppPresser Offline Cache Endpoint Static Html
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

class APOC_Static_HTML_Controller extends WP_REST_Posts_Controller {

	/**
	 * The namespace/rest_base for the pages item
	 *
	 * @var array
	 */
	protected $parent_controller_data = array();

	/**
	 * Constructor
	 *
	 * @since  NEXT
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;

		parent::__construct( 'page' );

		$this->parent_controller_data = array(
			'namespace' => $this->namespace,
			'rest_base' => $this->rest_base,
		);

		$this->namespace = 'appp-offline/v1';
		$this->rest_base = 'static-pages';

		$base = sprintf( '/%s/%s', $this->namespace, $this->rest_base );
	}

	/**
	 * Register routes, hooked into rest_api_init.
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function register_routes() {
		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_items_permissions_check' ),
				'args'            => $this->get_collection_params(),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			array(
				'methods'         => WP_REST_Server::READABLE,
				'callback'        => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_item_permissions_check' ),
				'args'            => array(
					'context'          => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
	}

	/**
	 * Get a collection of pages, designated for offline.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		add_filter( 'rest_page_query', array( $this, 'modify_query' ), 10, 2 );
		$response = parent::get_items( $request );
		return $response;
	}

	/**
	 * Get static output of a single page (if whitelisted).
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$response = parent::get_item( $request );

		// Gets the static html output of the entire page.
		$response->data['html'] = $this->get_static_html( $response->data['link'] );

		// If requesting plain html,
		if ( isset( $_GET['html'] ) ) {
			// Spit it out in plain html.
	 		header( 'Content-Type: text/html' );
			echo $response->data['html'];
			exit();
		}

		// Otherwise, return the response object.
		return $response;
	}

	public function modify_query( $args, $request ) {
		$whitelist = $this->plugin->get_option( 'offline-whitelist' );

		// Only include pages which are whitelisted for offline. This should be an AppPresser setting.
		$args['post__in'] = empty( $whitelist ) || ! is_array( $whitelist ) ? array( 0 ) : $whitelist;

		return $args;
	}

	/**
	 * Modify the page schema to minimum.
	 *
	 * @return array
	 */
	public function get_item_schema() {
		$schema = parent::get_item_schema();

		// We only want a subset of the schema for the pages endpoint.
		$schema_keep = array( 'id', 'link', 'modified', 'modified_gmt', 'slug', 'type', 'parent', 'title' );

		foreach ( $schema['properties'] as $key => $value ) {
			if ( ! in_array( $key, $schema_keep, 1 ) ) {
				unset( $schema['properties'][ $key ] );
			}
		}

		return $schema;
	}

	/**
	 * Prepare links for the request.
	 *
	 * @param WP_Post $post Post object.
	 * @return array Links for the given post.
	 */
	protected function prepare_links( $post ) {
		$links = parent::prepare_links( $post );

		// We only want a subset of the links for the pages endpoint.
		$new_links['self'] = $links['self'];
		$new_links['collection'] = $links['collection'];
		$new_links['version-history'] = $links['version-history'];
		$new_links['canonical'] = $this->parent_controller_data;

		$obj = get_post_type_object( 'page' );
		$rest_base = ! empty( $obj->rest_base ) ? $obj->rest_base : $this->parent_controller_data['rest_base'];
		$base = sprintf( '/%s/%s', $this->parent_controller_data['namespace'],$rest_base );

		// Provide canonical REST URL for accessing the full object
		$new_links['canonical'] = array(
			'href'   => rest_url( trailingslashit( $base ) . $post->ID ),
		);

		return $new_links;
	}

	/**
	 * Gets the static html output of a URL.
	 *
	 * @since  NEXT
	 *
	 * @param  string  $url URL to fetch
	 *
	 * @return string       HTML output of URL.
	 */
	public function get_static_html( $url ) {
		$html = wp_remote_retrieve_body( wp_remote_get( $url ) );

		$converter = new APOC_Base64_Images;

		// base64 the images.
		if ( $images = $converter->base64_images( $converter->get_images( $html ) ) ) {
			$html = strtr( $html, $images );
		}

		return $html;
	}

}
