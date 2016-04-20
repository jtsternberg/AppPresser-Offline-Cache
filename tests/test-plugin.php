<?php

class AppPresser_Offline_Cache_Test extends BaseTest {

	function test_class_exists() {
		$this->assertTrue( class_exists( 'AppPresser_Offline_Cache') );
	}

	function test_get_instance() {
		$this->assertTrue( appp_oc() instanceof AppPresser_Offline_Cache );
	}
}
