<?php

class APOC_Base64_Images_Test extends WP_UnitTestCase {

	function test_class_exists() {
		$this->assertTrue( class_exists( 'APOC_Base64_Images') );
	}

	function test_class_access() {
		$html = '<html><head><body><div><img src="https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png" alt=""/></div></body></head></html>';

		$converter = new APOC_Base64_Images;

		$images = $converter->get_images( $html );
		$this->assertTrue( is_array( $images ) );
		// base64 the images.
		$images = $converter->base64_images( $converter->get_images( $html ) );
		$this->assertTrue( is_array( $images ) );

		$html = strtr( $html, $images );

		$this->assertTrue( is_string( $html ) );
	}
}
