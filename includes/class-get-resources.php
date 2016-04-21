<?php
/**
 * AppPresser Offline Cache Get Resources
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */
// die('test');
class APOC_Get_Resources extends APOC_Dom {

	/**
	 * Gets the javascript resources.
	 *
	 * @since  NEXT
	 *
	 * @return array Array of script URLs
	 */
	public function get_scripts( $get_attributes = false ) {
		$scripts = array();
		foreach ( $this->dom->getElementsByTagName( 'script' ) as $tag ) {
			if ( $src = $tag->getAttributeNode( 'src' ) ) {

				if ( ! $get_attributes ) {
					$scripts[] = $src->nodeValue;
				} else {
					$script = array();

					foreach ( $tag->attributes as $attr ) {
						$script[ $attr->nodeName ] = $attr->nodeValue;
					}

					ksort( $script );
					$scripts[] = $script;
				}
			}


		}

		return $scripts;
	}

	/**
	 * Removes the javascript resources from the html
	 *
	 * @since  NEXT
	 *
	 * @return string Modified content.
	 */
	public function remove_scripts() {
		foreach ( $this->dom->getElementsByTagName( 'script' ) as $tag ) {
			if ( $src = $tag->getAttributeNode( 'src' ) ) {
				$this->remove_tag_from_content(
					$src->nodeValue,
					'<script',
					'/script>'
				);
			}
		}

		return $this->content;
	}

	/**
	 * Gets the CSS resources.
	 *
	 * @since  NEXT
	 *
	 * @return array Array of stylesheet URLs
	 */
	public function get_stylesheets( $get_attributes = false ) {
		$stylesheets = array();
		foreach ( $this->dom->getElementsByTagName( 'link' ) as $tag ) {
			$rel = $tag->getAttributeNode( 'rel' );
			$href = $tag->getAttributeNode( 'href' );

			if ( ! $rel || ! $href || 'stylesheet' !== $rel->nodeValue ) {
				continue;
			}

			if ( ! $get_attributes ) {
				$stylesheets[] = $href->nodeValue;
			} else {
				$stylesheet = array();

				foreach ( $tag->attributes as $attr ) {
					$stylesheet[ $attr->nodeName ] = $attr->nodeValue;
				}

				ksort( $stylesheet );
				$stylesheets[] = $stylesheet;
			}

		}

		return $stylesheets;
	}

	/**
	 * Removes the css resources from the html
	 *
	 * @since  NEXT
	 *
	 * @return string Modified content.
	 */
	public function remove_styles() {
		foreach ( $this->dom->getElementsByTagName( 'link' ) as $tag ) {
			$rel = $tag->getAttributeNode( 'rel' );
			$href = $tag->getAttributeNode( 'href' );

			if ( ! $rel || ! $href || 'stylesheet' !== $rel->nodeValue ) {
				continue;
			}

			$this->remove_tag_from_content(
				$href->nodeValue,
				'<link',
				'>'
			);
		}

		return $this->content;
	}

	/**
	 * Remove the script/style tag associated with the URL
	 *
	 * @since  NEXT
	 *
	 * @param  string  $url Script URL
	 */
	protected function remove_tag_from_content( $url, $tag_open, $tag_close ) {
		$url_start = strpos( $this->content, $url );

		if ( ! $url_start ) {
			return;
		}

		$before    = substr( $this->content, 0, $url_start );
		$tag_start = strrpos( $before, $tag_open );
		$ending    = substr( $this->content, $tag_start, strlen( $this->content ) );
		$end_pos   = strpos( $ending, $tag_close ) + strlen( $tag_close );
		$tag       = substr( $ending, 0, $end_pos );

		$this->content = str_replace( $tag, '', $this->content );
	}

}
