<?php
/**
 * Class RegisterPostTypeTest
 *
 * @package Katakuri
 */

/**
 * Register post type test case.
 */
class RegisterPostTypeTest extends WP_UnitTestCase {
  /**
   * some_post
   */
  function test_register_some_post() {
    $this->assertTrue(post_type_exists('some_post'));
  }
  function test_some_post_is_not_hierarchical() {
    $this->assertFalse(is_post_type_hierarchical('some_post'));
  }
  // check post type supports

  // register taxonomy test
  function test_register_some_post_tag_args() {
    $t = get_taxonomies(array('name' => 'some_post_tag'), 'objects')['some_post_tag'];
    $this->assertEquals($t->label, 'SomePostTag');
  }

  /**
   * another_post
   */
  function test_register_another_post() {
    $this->assertTrue(post_type_exists('another_post'));
  }
  function test_another_post_is_hierarchical() {
    $this->assertTrue(is_post_type_hierarchical('another_post'));
  }

  /**
   * unknown_post 
   */
  function test_not_register_unknown() {
    $this->assertFalse(post_type_exists('unknown_post'));
  }
}
