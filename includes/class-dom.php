<?php
/**
 * AppPresser Offline Cache Dom
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

abstract class APOC_Dom {

	/**
	 * DOMDocument object
	 *
	 * @var DOMDocument
	 */
	protected $dom;

	/**
	 * Constructor
	 *
	 * @since NEXT
	 *
	 * @param string $content Content to parse.
	 */
	public function __construct( $content ) {
		$this->dom = $this->get( $content );
	}

	protected function get( $content ) {
		$dom = new DOMDocument;
		@$dom->loadHTML( '<?xml encoding="UTF-8">' . $content );

		return $dom;
	}

	protected function get_http_url( $url ) {
		$url = esc_url_raw( $url );
		if ( false === strpos( $url, 'http' ) ) {
			$url = set_url_scheme( $url );
		}

		return $url;
	}

}
