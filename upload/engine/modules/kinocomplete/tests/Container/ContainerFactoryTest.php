<?php

namespace Kinocomplete\Test\Container;

use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Container\Container;
use Psr\Container\ContainerInterface;
use PHPUnit\Framework\TestCase;
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
   * Testing "fromPostfix" method.
   */
  public function testCanFromPostfix()
  {
    $file = realpath(FIXTURES_DIR .'/container/postfixed-container.json');

    $arrayFromFile = ContainerFactory::fromFile($file, true);
    $containerFromFile = ContainerFactory::fromFile($file);

    $array = ContainerFactory::fromPostfix(
      $arrayFromFile,
      'postfix',
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

    $container = ContainerFactory::fromPostfix(
      $containerFromFile,
      'postfix'
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

    $postfixedArray = ContainerFactory::fromPostfix(
      $arrayFromFile,
      'postfix',
      true,
      true
    );

    Assert::isArray($postfixedArray);

    Assert::same(
      $postfixedArray['integer_option_postfix'],
      1
    );

    Assert::same(
      $postfixedArray['string_option_postfix'],
      '1'
    );

    Assert::same(
      $postfixedArray['boolean_option_postfix'],
      false
    );

    Assert::same(
      $postfixedArray['serialized_option_postfix'],
      ['a' => 'b']
    );

    $postfixedContainer = ContainerFactory::fromPostfix(
      $containerFromFile,
      'postfix',
      false,
      true
    );

    Assert::isInstanceOf(
      $postfixedContainer,
      Container::class
    );

    Assert::same(
      $postfixedContainer->get('integer_option_postfix'),
      1
    );

    Assert::same(
      $postfixedContainer->get('string_option_postfix'),
      '1'
    );

    Assert::same(
      $postfixedContainer->get('boolean_option_postfix'),
      false
    );

    Assert::same(
      $postfixedContainer->get('serialized_option_postfix'),
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
