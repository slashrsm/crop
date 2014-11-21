<?php

/**
 * @file
 * Contains \Drupal\crop\Tests\CropCRUDTest.
 */

namespace Drupal\crop\Tests;

use Drupal\Component\Utility\String;
use Drupal\simpletest\KernelTestBase;

/**
 * Tests the crop entity CRUD operations.
 *
 * @group crop
 */
class CropCRUDTest extends KernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['user', 'image', 'crop'];

  /**
   * The crop storage.
   *
   * @var \Drupal\crop\CropStorageInterface.
   */
  protected $cropStorage;

  /**
   * The crop storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface.
   */
  protected $cropTypeStorage;

  /**
   * Test image style.
   *
   * @var \Drupal\image\ImageStyleInterface
   */
  protected $testStyle;

  /**
   * Test crop type.
   *
   * @var \Drupal\crop\CropInterface
   */
  protected $cropType;

  protected function setUp() {
    parent::setUp();

    /** @var \Drupal\Core\Entity\EntityManagerInterface $entity_manager */
    $entity_manager = $this->container->get('entity.manager');
    $this->cropStorage = $entity_manager->getStorage('crop');
    $this->cropTypeStorage = $entity_manager->getStorage('crop_type');

    // Create DB schemas.
    $entity_manager->onEntityTypeCreate($entity_manager->getDefinition('user'));
    $entity_manager->onEntityTypeCreate($entity_manager->getDefinition('image_style'));
    $entity_manager->onEntityTypeCreate($entity_manager->getDefinition('crop'));

    // Create test image style
    $this->testStyle = $entity_manager->getStorage('image_style')->create([
      'name' => 'test',
      'label' => 'Test image style',
      'effects' => [],
    ]);
    $this->testStyle->save();

    // Create test crop type
    $this->cropType = $entity_manager->getStorage('crop_type')->create([
      'id' => 'test_type',
      'label' => 'Test crop type',
      'description' => 'Some nice desc.',
    ]);
    $this->cropType->save();
  }

  /**
   * Tests crop type save.
   */
  public function testCropTypeSave() {
    $values = [
      'id' => $this->randomMachineName(),
      'label' => $this->randomString(),
      'description' => $this->randomGenerator->sentences(8),
    ];
    $crop_type = $this->cropTypeStorage->create($values);

    try {
      $crop_type->save();
      $this->assertTrue(TRUE, 'Crop type saved correctly.');
    } catch (\Exception $exception) {
      $this->assertTrue(FALSE, 'Crop type not saved correctly.');
    }

    $loaded = $this->container->get('config.factory')->get('crop.type.' . $values['id'])->get();
    foreach ($values as $key => $value) {
      $this->assertEqual($loaded[$key], $value, String::format('Correct value for @field found.', ['@field' => $key]));
    }
  }

  /**
   * Tests crop save.
   */
  public function testCropSave() {
    /** @var \Drupal\crop\CropInterface $crop */
    $values = [
      'type' => $this->cropType->id(),
      'entity_id' => 1,
      'entity_type' => 'file',
      'x' => '100',
      'y' => '150',
      'width' => '200',
      'height' => '250',
      'image_style' => $this->testStyle->id(),
    ];
    $crop = $this->cropStorage->create($values);

    try {
      $crop->save();
      $this->assertTrue(TRUE, 'Crop saved correctly.');
    } catch (\Exception $exception) {
      $this->assertTrue(FALSE, 'Crop not saved correctly.');
    }

    $loaded_crop = $this->cropStorage->loadUnchanged(1);
    foreach ($values as $key => $value) {
      switch ($key) {
        case 'image_style':
        case 'type':
          $this->assertEqual($loaded_crop->{$key}->target_id, $value, String::format('Correct value for @field found.', ['@field' => $key]));
          break;

        default:
          $this->assertEqual($loaded_crop->{$key}->value, $value, String::format('Correct value for @field found.', ['@field' => $key]));
          break;
      }
    }

  }
}
