<?php
/**
 * AppPresser Offline Cache Get Resources
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

class APOC_Get_Scripts extends APOC_Dom {

	protected $tag_name = 'script';
	protected $tag_open = '<script';
	protected $tag_close = '/script>';

	/**
	 * Gets the javascript resources.
	 *
	 * @since  NEXT
	 *
	 * @return array Array of script URLs
	 */
	public function get_scripts( $get_attributes = false ) {
		return $this->get_tags( $get_attributes );
	}

	/**
	 * Removes the javascript resources from the html
	 *
	 * @since  NEXT
	 *
	 * @return string Modified content.
	 */
	public function remove_scripts() {
		return $this->remove_tags();
	}

	/**
	 * Determines if a script tag matches the correct pattern.
	 *
	 * @since  NEXT
	 *
	 * @param  DOMNode $tag DomNode instance
	 *
	 * @return bool
	 */
	protected function should_use_tag( DOMNode $tag ) {
		$src = $tag->getAttributeNode( 'src' );

		return $src ? $src->nodeValue : false;
	}

}
