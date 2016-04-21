<?php
/**
 * AppPresser Offline Cache Get Resources
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

class APOC_Get_Stylesheets extends APOC_Dom {

	protected $tag_name = 'link';
	protected $tag_open = '<link';
	protected $tag_close = '>';

	/**
	 * Gets the CSS resources.
	 *
	 * @since  NEXT
	 *
	 * @return array Array of stylesheet URLs
	 */
	public function get_stylesheets( $get_attributes = false ) {
		return $this->get_tags( $get_attributes );
	}

	/**
	 * Removes the css resources from the html
	 *
	 * @since  NEXT
	 *
	 * @return string Modified content.
	 */
	public function remove_stylesheets() {
		return $this->remove_tags();
	}

	/**
	 * Determines if a link tag matches the correct pattern.
	 *
	 * @since  NEXT
	 *
	 * @param  DOMNode $tag DomNode instance
	 *
	 * @return bool
	 */
	protected function should_use_tag( $tag ) {
		$rel = $tag->getAttributeNode( 'rel' );
		$href = $tag->getAttributeNode( 'href' );

		return $rel && $href && 'stylesheet' === $rel->nodeValue
			? $href->nodeValue
			: false;
	}

}
