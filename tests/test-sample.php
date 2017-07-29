<?php
/**
 * Class SampleTest
 *
 * @package Post_Type_Note
 */

/**
 * Sample test case.
 */
class SampleTest extends WP_UnitTestCase {

	/**
	 * A single example test.
	 */
	function test_sample() {
		// Replace this with some actual testing code.
		$this->assertTrue(post_type_exists('some_post'));
	}
	function test_hierarchical() {
		$this->assertFalse(is_post_type_hierarchical('some_post'));
	}
}
