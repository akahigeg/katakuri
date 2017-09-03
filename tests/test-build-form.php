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

  function test_build_checkbox() {
    $saved_value = maybe_serialize(array('A'));
    $options = array('values' => array('A', 'B', 'C'));
    $checkboxes = KatakuriFormRenderer::buildCheckbox('field1', $saved_value, $options);
    $this->assertContains('<input type="checkbox" name="field1[]" value="A" checked>', $checkboxes);
    $this->assertContains('<input type="checkbox" name="field1[]" value="B">', $checkboxes);
    $this->assertContains('<input type="checkbox" name="field1[]" value="C">', $checkboxes);
  }

  function test_build_checkbox_with_label() {
    $saved_value = maybe_serialize(array());
    $options = array('values' => array(array('a' => 'A'), array('b' => 'B'), array('c' => 'C')));
    $checkboxes = KatakuriFormRenderer::buildCheckbox('field1', $saved_value, $options);
    $this->assertContains('<input type="checkbox" name="field1[]" value="a">A</label>', $checkboxes);
  }
}
