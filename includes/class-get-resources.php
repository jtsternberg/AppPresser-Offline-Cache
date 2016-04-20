<?php
/**
 * AppPresser Offline Cache Get Resources
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

class APOC_Get_Resources extends APOC_Dom {

	/**
	 * Gets the javascript resources.
	 *
	 * @since  NEXT
	 *
	 * @param  string $content HTML content.
	 *
	 * @return array           Array of script URLs
	 */
	public function get_scripts() {
		$scripts = array();
		foreach ( $this->dom->getElementsByTagName( 'script' ) as $script ) {
			if ( $src = $script->getAttributeNode( 'src' ) ) {
				$scripts[] = $src->nodeValue;
			}
		}

		return $scripts;
	}

	/**
	 * Gets the CSS resources.
	 *
	 * @since  NEXT
	 *
	 * @param  string $content HTML content.
	 *
	 * @return array           Array of stylesheet URLs
	 */
	public function get_stylesheets() {
		$stylesheets = array();
		foreach ( $this->dom->getElementsByTagName( 'link' ) as $link ) {
			$rel = $link->getAttributeNode( 'rel' );
			$href = $link->getAttributeNode( 'href' );

			if ( ! $rel || ! $href || 'stylesheet' !== $rel->nodeValue ) {
				continue;
			}

			$stylesheets[] = $href->nodeValue;
		}

		return $stylesheets;
	}


}
