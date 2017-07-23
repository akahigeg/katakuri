<?php

class SampleTest extends WP_UnitTestCase {
  function setUp() {
    parent::setUp();
    $this->plugin = $GLOBALS['wp-cpt-json'];
  }

  function test_sample() {
    // replace this with some actual testing code
    $this->assertTrue( true );
  }
}

