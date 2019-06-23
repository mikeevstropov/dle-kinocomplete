<?php

namespace Kinocomplete\Test\ExtraField;

use Kinocomplete\Container\Container;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\ExtraField\ExtraFieldFactory;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class ExtraFieldFactoryTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var ExtraFieldFactory
   */
  public $instance;

  /**
   * ExtraFieldFactoryTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();

    $this->instance = new ExtraFieldFactory($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      ExtraFieldFactory::class
    );
  }

  /**
   * Testing "fromDefinition" method.
   */
  public function testCanFromDefinition()
  {
    $filePath = realpath(
      FIXTURES_DIR .'/extra-field/definitions.txt'
    );

    $definition = current(
      array_filter(
        file($filePath)
      )
    );

    $field = $this->instance->fromDefinition(
      $definition
    );

    Assert::isInstanceOf(
      $field,
      ExtraField::class
    );
  }

  /**
   * Testing "fromDefinition" method with link.
   */
  public function testCanFromDefinitionWithLink()
  {
    $filePath = realpath(
      FIXTURES_DIR .'/extra-field/link-definitions.txt'
    );

    $definition = current(
      array_filter(
        file($filePath)
      )
    );

    $field = $this->instance->fromDefinition(
      $definition
    );

    Assert::isInstanceOf(
      $field,
      ExtraField::class
    );

    Assert::true(
      $field->link
    );
  }

  /**
   * Testing "fromDefinitions" method.
   */
  public function testCanFromDefinitions()
  {
    $filePath = realpath(
      FIXTURES_DIR .'/extra-field/definitions.txt'
    );

    $definitions = file_get_contents($filePath);

    $fieldsNumber = count(
      array_filter(
        file($filePath)
      )
    );

    $fields = $this->instance->fromDefinitions(
      $definitions
    );

    Assert::count($fields, $fieldsNumber);

    foreach ($fields as $field) {

      Assert::isInstanceOf(
        $field,
        ExtraField::class
      );
    }
  }

  /**
   * Testing "fromValue" method.
   *
   * @throws \Exception
   */
  public function testCanFromValue()
  {
    $definitionsFilePath = realpath(
      FIXTURES_DIR  .'/extra-field/definitions.txt'
    );

    $valuesFilePath = realpath(
      FIXTURES_DIR  .'/extra-field/values.txt'
    );

    $definition = current(
      array_filter(
        file($definitionsFilePath)
      )
    );

    $values = file_get_contents($valuesFilePath);

    $value = current(
      explode('||', $values)
    );

    $field = $this->instance->fromDefinition(
      $definition
    );

    Assert::isEmpty(
      $field->value
    );

    $field = $this->instance->fromValue(
      $field,
      $value
    );

    Assert::stringNotEmpty(
      $field->value
    );

    Assert::isInstanceOf(
      $field,
      ExtraField::class
    );
  }

  /**
   * Testing "fromValues" method.
   *
   * @throws \Exception
   */
  public function testCanFromValues()
  {
    $definitionsFilePath = realpath(
      FIXTURES_DIR .'/extra-field/definitions.txt'
    );

    $valuesFilePath = realpath(
      FIXTURES_DIR .'/extra-field/values.txt'
    );

    $definitions = file_get_contents($definitionsFilePath);
    $values = file_get_contents($valuesFilePath);

    $fields = $this->instance->fromDefinitions(
      $definitions
    );

    $expectedCount = count($fields);

    $fields = $this->instance->fromValues(
      $values,
      $fields
    );

    Assert::count(
      $fields,
      $expectedCount
    );
  }

  /**
   * Testing "fromValues" method with values
   * of undefined fields.
   *
   * @throws \Exception
   */
  public function testCanFromValuesOfUndefinedFields()
  {
    $definitionsFilePath = realpath(
      FIXTURES_DIR .'/extra-field/definitions.txt'
    );

    $valuesFilePath = realpath(
      FIXTURES_DIR .'/extra-field/values-of-undefined.txt'
    );

    $definitions = file_get_contents($definitionsFilePath);
    $values = file_get_contents($valuesFilePath);

    $fields = $this->instance->fromDefinitions(
      $definitions
    );

    $fields = $this->instance->fromValues(
      $values,
      $fields,
      true
    );

    Assert::count($fields, 1);
  }

  /**
   * Testing "fromValues" method exceptions
   * with values of undefined fields.
   *
   * @throws \Exception
   */
  public function testCannotFromValuesOfUndefinedFields() {

    $this->expectException(\Exception::class);

    $definitionsFilePath = realpath(
      FIXTURES_DIR .'/extra-field/definitions.txt'
    );

    $valuesFilePath = realpath(
      FIXTURES_DIR .'/extra-field/values-of-undefined.txt'
    );

    $definitions = file_get_contents($definitionsFilePath);
    $values = file_get_contents($valuesFilePath);

    $fields = $this->instance->fromDefinitions(
      $definitions
    );

    $this->instance->fromValues(
      $values,
      $fields
    );
  }

  /**
   * Testing "toValue" method.
   */
  public function testCanToValue()
  {
    $field = new ExtraField();
    $field->name = 'name';
    $field->value = 'value';

    $expected = 'name|value';

    Assert::same(
      $this->instance->toValue($field),
      $expected
    );
  }

  /**
   * Testing "toValue" method exceptions.
   */
  public function testCannotToValue()
  {
    $this->expectException(\InvalidArgumentException::class);

    $field = new ExtraField();
    $this->instance->toValue($field);
  }

  /**
   * Testing "fromVideo" method.
   */
  public function testCanFromVideo()
  {
    $video = new Video();
    $video->worldTitle = Utils::randomString();
    $video->tagline = Utils::randomString();

    $expectedName = Utils::randomString();

    $container = new Container([
      'video_field_world_title' => $expectedName,
      'video_field_tagline' => ''
    ]);

    $instance = new ExtraFieldFactory(
      $container
    );

    $result = $instance->fromVideo($video);

    Assert::count($result, 1);

    Assert::isInstanceOf(
      $result[0],
      ExtraField::class
    );

    Assert::same(
      $result[0]->name,
      $expectedName
    );

    Assert::same(
      $result[0]->value,
      $video->worldTitle
    );
  }

  /**
   * Testing "fromVideo" method with empty value.
   */
  public function testCanFromVideoWithEmptyValue()
  {
    $video = new Video();
    $video->worldTitle = '';

    $expectedName = Utils::randomString();

    $container = new Container([
      'video_field_world_title' => $expectedName
    ]);

    $instance = new ExtraFieldFactory(
      $container
    );

    $result = $instance->fromVideo($video);

    Assert::count($result, 0);
  }
}
