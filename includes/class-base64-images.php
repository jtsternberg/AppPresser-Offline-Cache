<?php
/**
 * AppPresser Offline Cache Base64 Images
 * @version 0.0.0
 * @package AppPresser Offline Cache
 */

class APOC_Base64_Images extends APOC_Dom {

	/**
	 * Gets the image src/srcset values of html content.
	 *
	 * @since  NEXT
	 *
	 * @return array Array of image URLs
	 */
	public function get_images() {
		$images = array();
		foreach ( $this->dom->getElementsByTagName( 'img' ) as $image ) {
			if ( $src = $image->getAttributeNode( 'src' ) ) {
				$images[] = $src->nodeValue;
			}
			if ( $srcset = $image->getAttributeNode( 'srcset' ) ) {
				$srcset = explode( ',', $srcset->nodeValue );
				if ( ! empty( $srcset ) ) {
					foreach ( $srcset as $url ) {
						$url = explode( ' ', trim( $url ) );
						$images[] = $url[0];
					}
				}
			}
		}

		return $images;
	}

	/**
	 * Takes an array of image URLs and converts them to an array of
	 * URL => base64 encoded values for replacing in html content.
	 *
	 * @since  NEXT
	 *
	 * @param  array  $images Array of image URLs
	 *
	 * @return array          Array of URL => base64 encoded image URIs
	 */
	public function base64_images( $images ) {
		if ( ! empty( $images ) ) {
			/** WordPress Administration File API */
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}

		$replace_pairs = array();

		foreach ( $images as $url ) {
			// Set variables for storage, fix file filename for query strings.
			preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $url, $matches );
			if ( ! $matches ) {
				continue;
			}

			$url = $this->get_http_url( $url );

			// Download file to temp location.
			$file_tmp_name = download_url( $url );

			// If error downloading, unlink.
			if ( is_wp_error( $file_tmp_name ) ) {
				@unlink( $file_tmp_name );
				continue;
			}

			// Get the image output
			$image_output = file_get_contents( $file_tmp_name );

			// Delete the downloaded temp. file.
			unlink( $file_tmp_name );

			if ( empty( $image_output ) ) {
				continue;
			}

			// Get the base64-encoded image src URI.
			$replace_pairs[ $url ] = sprintf(
				'data:%s;base64,%s',
				$this->get_mime_type( basename( $matches[0] ), $url ),
				base64_encode( $image_output )
			);
		}

		return ! empty( $replace_pairs ) ? $replace_pairs : false;
	}

	/**
	 * wp_check_filetype/mime_content_type wrapper
	 *
	 * @since  NEXT
	 *
	 * @param  string  $file_name File name
	 * @param  string  $url       File URL
	 *
	 * @return string             Mime type value
	 */
	protected function get_mime_type( $file_name, $url ) {
		$mime = wp_check_filetype( $file_name );

		if ( false === $mime[ 'type' ] && function_exists( 'mime_content_type' ) ) {
			$mime[ 'type' ] = mime_content_type( $url );
		}

		return $mime[ 'type' ] ? $mime[ 'type' ] : 'image/' . substr( $url, strrpos( $url, '.' ) + 1 );
	}

}
