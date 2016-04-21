<?php

require_once 'test-base.php';

class APOC_Base64_Images_Test extends BaseTest {

	function test_class_exists() {
		$this->assertTrue( class_exists( 'APOC_Base64_Images') );
	}

	function test_class_access() {
		$html = $this->html();

		$converter = new APOC_Base64_Images( $html );

		$images = $converter->get_images();

		$this->assertEquals( array(
			'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
			'//s.w.org/screenshots/3.8/plugins.png',
		), $images );

		// base64 the images.
		$base64_images = $converter->base64_images( $images );

		$this->assertEquals( array(
			'https://www.google.com/images/branding/googlelogo/2x/googlelogo_color_272x92dp.png',
			'http://s.w.org/screenshots/3.8/plugins.png',
		), array_keys( $base64_images ) );

		$html = strtr( $html, $base64_images );

		$this->assertTrue( is_string( $html ) );
	}
}
