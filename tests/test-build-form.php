<?php
/**
 * Class KatakuriFormRendererTest
 *
 * @package Katakuri
 */

/**
 * Register post type test case.
 */
class KatakuriFormRendererTest extends WP_UnitTestCase {
  function test_build_text() {
    $text_field = KatakuriFormRenderer::buildText('field1', 'saved!', array());
    $this->assertEquals('<input name="field1" type="text" value="saved!" size="40">', $text_field);
  }

  function test_build_text_with_size() {
    $text_field = KatakuriFormRenderer::buildText('field1', 'saved!', array('size' => 50));
    $this->assertEquals('<input name="field1" type="text" value="saved!" size="50">', $text_field);
  }
}
