<?php

abstract class BaseTest extends WP_UnitTestCase {
	protected function html() {
		ob_start();
		include 'test.html';
		return ob_get_clean();
	}

	protected function assertDomResultsMatch( $class, $method, $expected ) {
		$html = $this->html();
		$this->assertClassMethodResultsMatch( $class, $method, $html, $expected );
	}

	protected function assertClassMethodResultsMatch( $class, $method, $parameters, $expected ) {
		$object = new $class( $parameters );
		$results = $object->$method();

		$this->assertEquals( $expected, $results );
	}
}
