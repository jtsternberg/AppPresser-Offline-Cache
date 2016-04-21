<?php

class APOC_Get_Resources_Test extends BaseTest {

	function test_classes_exist() {
		$this->assertTrue( class_exists( 'APOC_Get_Stylesheets') );
		$this->assertTrue( class_exists( 'APOC_Get_Scripts') );
	}

	function test_get_stylesheets() {
		$this->assertDomResultsMatch( 'APOC_Get_Stylesheets', 'get_stylesheets', array(
			'//s.w.org/wp-includes/css/dashicons.css?20150710',
			'//fonts.googleapis.com/css?family=Open+Sans:300italic,400italic,600italic,400,300,600&subset=latin,cyrillic-ext,greek-ext,greek,vietnamese,latin-ext,cyrillic',
			'//s.w.org/style/wp4.css?40',
		) );
	}

	function test_get_scripts() {
		$this->assertDomResultsMatch( 'APOC_Get_Scripts', 'get_scripts', array(
			'//s.w.org/wp-includes/js/jquery/jquery.js?v=1.11.1',
			'https://apis.google.com/js/platform.js',
		) );
	}

	function test_remove_stylesheets() {
		$this->do_remove_tags_tests( 'APOC_Get_Stylesheets', 'remove_stylesheets', 3 );
	}

	function test_remove_scripts() {
		$this->do_remove_tags_tests( 'APOC_Get_Scripts', 'remove_scripts', 2 );
	}

	private function do_remove_tags_tests( $class, $method, $expected_found_count ) {

		$html = $this->html();
		$html_length = strlen( $html );

		$tag_dom = new $class( $html );

		$removed = $tag_dom->$method();
		$this->assertNotEquals( $html, $removed );

		// Should have removed $expected_found_count tags
		$this->assertEquals( $expected_found_count, count( $tag_dom->removed_tags ) );

		$remove_length = 0;
		foreach ( $tag_dom->removed_tags as $tag ) {
			$remove_length += strlen( $tag );
		}

		// Make sure the length of our new html is the equivelant to removing all the script tags.
		$this->assertEquals( $remove_length, $html_length - similar_text( $html, $removed ) );
	}
}
