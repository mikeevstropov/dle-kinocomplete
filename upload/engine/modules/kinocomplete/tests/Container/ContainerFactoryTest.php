<?php

namespace Kinocomplete\Test\Container;

use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Container\Container;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ContainerFactoryTest extends TestCase
{
  /**
   * Testing "fromFile" method.
   */
  public function testCanFromFile()
  {
    $file = realpath(FIXTURES_DIR .'/container/container.json');

    $array = ContainerFactory::fromFile($file, true);

    Assert::isArray($array);

    Assert::same(
      $array['integer_option'],
      1
    );

    Assert::same(
      $array['string_option'],
      '1'
    );

    Assert::same(
      $array['boolean_option'],
      false
    );

    Assert::same(
      $array['serialized_option'],
      ['a' => 'b']
    );

    $container = ContainerFactory::fromFile($file);

    Assert::isInstanceOf(
      $container,
      Container::class
    );

    Assert::same(
      $container->get('integer_option'),
      1
    );

    Assert::same(
      $container->get('string_option'),
      '1'
    );

    Assert::same(
      $container->get('boolean_option'),
      false
    );

    Assert::same(
      $container->get('serialized_option'),
      ['a' => 'b']
    );
  }

  /**
   * Testing "fromNamespace" method.
   */
  public function testCanFromNamespace()
  {
    $file = realpath(FIXTURES_DIR .'/container/namespaced-container.json');

    $arrayFromFile = ContainerFactory::fromFile($file, true);
    $containerFromFile = ContainerFactory::fromFile($file);

    $array = ContainerFactory::fromNamespace(
      $arrayFromFile,
      'namespace',
      true
    );

    Assert::isArray($array);

    Assert::same(
      $array['integer_option'],
      1
    );

    Assert::same(
      $array['string_option'],
      '1'
    );

    Assert::same(
      $array['boolean_option'],
      false
    );

    Assert::same(
      $array['serialized_option'],
      ['a' => 'b']
    );

    $container = ContainerFactory::fromNamespace(
      $containerFromFile,
      'namespace'
    );

    Assert::isInstanceOf(
      $container,
      Container::class
    );

    Assert::same(
      $container->get('integer_option'),
      1
    );

    Assert::same(
      $container->get('string_option'),
      '1'
    );

    Assert::same(
      $container->get('boolean_option'),
      false
    );

    Assert::same(
      $container->get('serialized_option'),
      ['a' => 'b']
    );

    $namespacedArray = ContainerFactory::fromNamespace(
      $arrayFromFile,
      'namespace',
      true,
      true
    );

    Assert::isArray($namespacedArray);

    Assert::same(
      $namespacedArray['namespace_integer_option'],
      1
    );

    Assert::same(
      $namespacedArray['namespace_string_option'],
      '1'
    );

    Assert::same(
      $namespacedArray['namespace_boolean_option'],
      false
    );

    Assert::same(
      $namespacedArray['namespace_serialized_option'],
      ['a' => 'b']
    );

    $namespacedContainer = ContainerFactory::fromNamespace(
      $containerFromFile,
      'namespace',
      false,
      true
    );

    Assert::isInstanceOf(
      $namespacedContainer,
      Container::class
    );

    Assert::same(
      $namespacedContainer->get('namespace_integer_option'),
      1
    );

    Assert::same(
      $namespacedContainer->get('namespace_string_option'),
      '1'
    );

    Assert::same(
      $namespacedContainer->get('namespace_boolean_option'),
      false
    );

    Assert::same(
      $namespacedContainer->get('namespace_serialized_option'),
      ['a' => 'b']
    );
  }

  /**
   * Testing "filterByKeys" method.
   */
  public function testCanFilterByKeys()
  {
    $file = realpath(FIXTURES_DIR .'/container/container.json');

    $array = ContainerFactory::fromFile($file, true);

    Assert::isArray($array);

    $keys = [
      'integer_option',
      'string_option'
    ];

    $filteredArray = ContainerFactory::filterByKeys(
      $array,
      $keys,
      true
    );

    Assert::count($filteredArray, 2);

    Assert::same(
      $filteredArray['integer_option'],
      1
    );

    Assert::same(
      $filteredArray['string_option'],
      '1'
    );

    $filteredContainer = ContainerFactory::filterByKeys(
      $array,
      $keys
    );

    Assert::isInstanceOf(
      $filteredContainer,
      ContainerInterface::class
    );

    Assert::count(
      $filteredContainer->keys(),
      2
    );

    Assert::count($filteredArray, 2);

    Assert::same(
      $filteredContainer->get('integer_option'),
      1
    );

    Assert::same(
      $filteredContainer->get('string_option'),
      '1'
    );
  }

  /**
   * Testing "toArray" method.
   */
  public function testCanToArray()
  {
    $expected = ['key' => 'value'];

    $container = new Container($expected);

    $array = ContainerFactory::toArray($container);

    Assert::same(
      $array,
      $expected
    );
  }
}