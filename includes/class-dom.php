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
	 * Content to parse
	 *
	 * @var string
	 */
	protected $content;

	/**
	 * Stores an array of the removed script tags when remove_tags is run.
	 *
	 * @var array
	 */
	public $removed_tags = array();

	/**
	 * The resource's html tag name.
	 *
	 * @var string
	 */
	protected $tag_name = '';

	/**
	 * The resource's tag open pattern.
	 *
	 * @var string
	 */
	protected $tag_open = '';

	/**
	 * The resource's tag close pattern.
	 *
	 * @var string
	 */
	protected $tag_close = '';

	/**
	 * The properties required to be extended by another class.
	 *
	 * @var string
	 */
	protected $required = array(
		'tag_name',
		'tag_open',
		'tag_close',
	);

	/**
	 * Constructor
	 *
	 * @since NEXT
	 *
	 * @param string $content Content to parse.
	 */
	public function __construct( $content ) {
		foreach ( $this->required as $property ) {
			if ( '' === $this->$property ) {
				throw new Exception( __CLASS__ . " requires extending class to have a set {$property} property" );
			}
		}

		$this->content = $content;
		$this->dom = $this->initiate_dom( $content );
	}

	/**
	 * Initiate a DOMDocument instance with the provided content.
	 *
	 * @since  NEXT
	 *
	 * @param  string $content HTML content.
	 *
	 * @return DOMDocument
	 */
	private function initiate_dom( $content ) {
		$dom = new DOMDocument;
		@$dom->loadHTML( '<?xml encoding="UTF-8">' . $content );

		return $dom;
	}

	/**
	 * Adds http/s to resource URLs w/o scheme specified (URLs that start with //).
	 *
	 * @since  NEXT
	 *
	 * @param  string $url URL to possibly modify.
	 *
	 * @return string      Possibly modified URL.
	 */
	protected function get_http_url( $url ) {
		$url = esc_url_raw( $url );
		if ( false === strpos( $url, 'http' ) ) {
			$url = set_url_scheme( $url );
		}

		return $url;
	}

	/**
	 * Gets resources.
	 *
	 * @since  NEXT
	 *
	 * @return array Array of stylesheet URLs
	 */
	protected function get_tags( $get_attributes = false ) {
		$tags = array();
		foreach ( $this->dom->getElementsByTagName( $this->tag_name ) as $tag ) {
			if ( $tag_url = $this->should_use_tag( $tag ) ) {
				$this->get_tag( $tags, $tag, $tag_url, $get_attributes );
			}
		}

		return $tags;
	}

	/**
	 * Gets resource.
	 *
	 * @since  NEXT
	 *
	 * @param array   $tags           Tags we're returning. Passed by reference.
	 * @param DOMNode $tag            DOMNode instance
	 * @param string  $tag_url        Tag's URL
	 * @param boolean $get_attributes Whether to include all attributes, or just tag URL.
	 */
	protected function get_tag( &$tags, DOMNode $tag, $tag_url, $get_attributes = false ) {
		if ( ! $get_attributes ) {
			$tags[] = $tag_url;
		} else {
			$tags[] = $this->get_all_tag_attributes( $tag, $get_attributes );
		}
	}

	/**
	 * Determines if a tag matches the correct pattern.
	 *
	 * @since  NEXT
	 *
	 * @param  DOMNode $tag DomNode instance
	 *
	 * @return bool
	 */
	abstract protected function should_use_tag( DOMNode $tag );

	/**
	 * Gets tag attributes.
	 *
	 * @since  NEXT
	 *
	 * @param  DOMNode $tag DomNode instance
	 *
	 * @return array        Array of tag attributes.
	 */
	protected function get_all_tag_attributes( DOMNode $tag ) {
		$tag_attrs = array();

		foreach ( $tag->attributes as $attr ) {
			$tag_attrs[ $attr->nodeName ] = $attr->nodeValue;
		}

		ksort( $tag_attrs );

		return $tag_attrs;
	}

	/**
	 * Removes the tags from the html
	 *
	 * @since  NEXT
	 *
	 * @return string Modified content.
	 */
	protected function remove_tags() {
		$this->removed_tags = array();
		foreach ( $this->dom->getElementsByTagName( $this->tag_name ) as $tag ) {
			if ( $tag_url = $this->should_use_tag( $tag ) ) {
				$this->remove_tag_from_content( $tag_url );
			}
		}

		return $this->content;
	}

	/**
	 * Remove the tag associated with the URL
	 *
	 * @since  NEXT
	 *
	 * @param  string  $url Resource URL
	 */
	protected function remove_tag_from_content( $url ) {
		$url_start = strpos( $this->content, $url );

		if ( ! $url_start ) {
			return;
		}

		$before_url      = substr( $this->content, 0, $url_start );
		$tag_start       = strrpos( $before_url, $this->tag_open );
		$after_tag_start = substr( $this->content, $tag_start, strlen( $this->content ) );
		$tag_stop        = strpos( $after_tag_start, $this->tag_close ) + strlen( $this->tag_close );
		$tag             = substr( $after_tag_start, 0, $tag_stop );

		$this->removed_tags[] = $tag;
		$this->content        = str_replace( $tag, '', $this->content );
	}

}
