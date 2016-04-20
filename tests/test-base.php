<?php

abstract class BaseTest extends WP_UnitTestCase {
	protected function html() {
		ob_start();
		include 'test.html';
		return ob_get_clean();
	}
}
