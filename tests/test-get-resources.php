<?php

class APOC_Get_Resources_Test extends BaseTest {

	function test_class_exists() {
		$this->assertTrue( class_exists( 'APOC_Get_Resources') );
	}

	function test_class_access() {

		$html = $this->html();

		$getter = new APOC_Get_Resources( $html );

		$scripts = $getter->get_scripts();

		$this->assertEquals( array(
			'//s.w.org/wp-includes/js/jquery/jquery.js?v=1.11.1',
			'https://apis.google.com/js/platform.js',
		), $scripts );

		$stylesheets = $getter->get_stylesheets();


		$this->assertEquals( array(
			'//s.w.org/wp-includes/css/dashicons.css?20150710',
			'//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,400,300,600&subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic',
			'//s.w.org/style/wp4.css?40',
		), $stylesheets );

	}
}
