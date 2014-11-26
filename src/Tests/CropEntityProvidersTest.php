<?php

/**
 * @file
 * Contains \Drupal\crop\Tests\CropCropEntityProvidersTest.
 */

namespace Drupal\crop\Tests;

use Drupal\crop\EntityProviderNotFoundException;

/**
 * Tests entity provider plugins.
 *
 * @group crop
 */
class CropEntityProvidersTest extends CropUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['crop', 'file', 'image', 'user'];


  protected function setUp() {
    parent::setUp();

    $this->container->get('entity.manager')
      ->onEntityTypeCreate($this->container->get('entity.manager')->getDefinition('file'));
  }

  /**
   * Tests file provider plugin.
   */
  function testCropEffect() {
    $file = $this->getTestFile();
    $file->save();

    // Create crop.
    $values = [
      'type' => $this->cropType->id(),
      'entity_id' => $file->id(),
      'entity_type' => 'file',
      'uri' => $file->getFileUri(),
      'x' => '190',
      'y' => '120',
      'width' => '50',
      'height' => '50',
      'image_style' => $this->testStyle->id(),
    ];
    /** @var \Drupal\crop\CropInterface $crop */
    $crop = $this->container->get('entity.manager')->getStorage('crop')->create($values);
    $crop->save();

    try {
      $provider = $crop->provider();
      $this->assertTrue(TRUE, 'File entity provider plugin was found.');
    } catch (EntityProviderNotFoundException $e) {
      $this->assertTrue(FALSE, 'File entity provider plugin was found.');
    }

    $this->assertEqual($provider->uri($file), $file->getFileUri(), 'File provider plugin returned correct URI.');
  }

}
