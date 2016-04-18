<?php
/**
 * AppPresser Offline Cache Endpoints
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

class APOC_REST_Controllers {

	/**
	 * Parent plugin class
	 *
	 * @var   class
	 * @since NEXT
	 */
	protected $plugin = null;

	/**
	 * Instance of APOC_Static_HTML_Controller
	 *
	 * @since NEXT
	 * @var APOC_Static_HTML_Controller
	 */
	protected $static_html;

	/**
	 * Constructor
	 *
	 * @since  NEXT
	 * @param  object $plugin Main plugin object.
	 * @return void
	 */
	public function __construct( $plugin ) {
		$this->plugin = $plugin;
	}

	/**
	 * Initiate our hooks
	 *
	 * @since  NEXT
	 * @return void
	 */
	public function hooks() {
		// Waited to initiate until WP_REST_Posts_Controller is ready.
		$this->static_html = new APOC_Static_HTML_Controller( $this->plugin );

		add_action( 'rest_api_init', array( $this->static_html, 'register_routes' ) );
	}

	/**
	 * Magic getter for our object.
	 *
	 * @since  NEXT
	 * @param string $field Field to get.
	 * @throws Exception Throws an exception if the field is invalid.
	 * @return mixed
	 */
	public function __get( $field ) {
		switch ( $field ) {
			case 'static_html':
				return $this->$field;
			default:
				throw new Exception( 'Invalid '. __CLASS__ .' property: ' . $field );
		}
	}
}
