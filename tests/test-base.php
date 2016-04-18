<?php

class BaseTest extends WP_UnitTestCase {

	function test_sample() {
		// replace this with some actual testing code
		$this->assertTrue( true );
	}

	function test_class_exists() {
		$this->assertTrue( class_exists( 'AppPresser_Offline_Cache') );
	}
	
	function test_get_instance() {
		$this->assertTrue( app_oc() instanceof AppPresser_Offline_Cache );
	}
}
