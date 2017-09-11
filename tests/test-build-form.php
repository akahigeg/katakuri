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

  function test_build_textarea() {
    $text_field = KatakuriFormRenderer::buildTextarea('field1', 'saved!', array());
    $this->assertEquals('<textarea name="field1" rows="5" cols="40">saved!</textarea>', $text_field);
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

  function test_build_radio() {
    $saved_value = 'B';
    $options = array('values' => array('A', 'B', 'C'));
    $radios = KatakuriFormRenderer::buildRadio('field1', $saved_value, $options);
    $this->assertContains('<input type="radio" name="field1" value="A">', $radios);
    $this->assertContains('<input type="radio" name="field1" value="B" checked>', $radios);
    $this->assertContains('<input type="radio" name="field1" value="C">', $radios);
  }

  function test_build_radio_with_label() {
    $saved_value = 'b';
    $options = array('values' => array(array('a' => 'A'), array('b' => 'B'), array('c' => 'C')));
    $radios = KatakuriFormRenderer::buildRadio('field1', $saved_value, $options);
    $this->assertContains('<input type="radio" name="field1" value="a">A', $radios);
    $this->assertContains('<input type="radio" name="field1" value="b" checked>B', $radios);
    $this->assertContains('<input type="radio" name="field1" value="c">C', $radios);
  }

  function test_build_select() {
    $saved_value = array('B');
    $options = array('values' => array('A', 'B', 'C'));
    $select = KatakuriFormRenderer::buildSelect('field1', $saved_value, $options);
    $this->assertContains('<select name="field1[]"', $select);
    $this->assertContains('<option value="A">', $select);
    $this->assertContains('<option value="B" selected>', $select);
    $this->assertContains('<option value="C">', $select);
    $this->assertContains('</select>', $select);
  }

  function test_build_select_with_option_label() {
    $saved_value = array('b');
    $options = array('values' => array(array('a' => 'A'), array('b' => 'B'), array('c' => 'C')));
    $select = KatakuriFormRenderer::buildSelect('field1', $saved_value, $options);
    $this->assertContains('<option value="a">A', $select);
    $this->assertContains('<option value="b" selected>B', $select);
    $this->assertContains('<option value="c">C', $select);
  }

  function test_build_select_with_style() {
    $options = array('values' => array('A', 'B', 'C'),
                     'width' => 10, 'size' => 3);
    $select = KatakuriFormRenderer::buildSelect('field1', '', $options);
    $this->assertContains('<select name="field1[]" size="3" style="width:10px;"', $select);
  }

  function test_build_multipl_select() {
    $options = array('values' => array('A', 'B', 'C'),
                     'width' => 10, 'size' => 3, 'multiple' => true);
    $select = KatakuriFormRenderer::buildSelect('field1', '', $options);
    $this->assertContains('<select name="field1[]" size="3" style="width:10px;" multiple', $select);
  }
}
