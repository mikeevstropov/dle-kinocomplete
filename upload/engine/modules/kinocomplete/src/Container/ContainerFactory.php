<?php

namespace Kinocomplete\Container;

use Pimple\Container as PimpleContainer;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class ContainerFactory
{
  /**
   * From file.
   *
   * @param  string $filePath
   * @param  boolean $raw
   * @return ContainerInterface|array
   */
  static public function fromFile(
    $filePath,
    $raw = false
  ) {
    Assert::fileExists(
      $filePath,
      'Файл контейнера не найден.'
    );

    $arrayJson = file_get_contents($filePath);
    $array = json_decode($arrayJson, true);

    foreach ($array as $key => $value) {

      $serialized = @unserialize($value);

      $array[$key] = $serialized !== false
        ? $serialized
        : $value;
    }

    return $raw
      ? $array
      : new Container($array);
  }

  /**
   * From namespace.
   *
   * @param  ContainerInterface|array $array
   * @param  string $namespace
   * @param  bool $raw
   * @param  bool $namespaceInResult
   * @param  bool $includeServices
   * @return ContainerInterface|array
   */
  static public function fromNamespace(
    $array,
    $namespace,
    $raw = false,
    $namespaceInResult = false,
    $includeServices = false
  ) {
    if (!is_array($array) && !($array instanceof ContainerInterface))
      throw new \InvalidArgumentException(sprintf(
        'First argument must be an array or instance of Psr\Container\ContainerInterface, got %s.',
        $array
      ));

    $result = [];

    // Fix for PimpleContainer.
    if ($array instanceof PimpleContainer) {

      $keys = $array->keys();
      $iterable = [];

      foreach ($keys as $key) {

        $isService = is_callable($array->raw($key));

        if (!$includeServices && $isService)
          continue;

        $iterable[$key] = $array[$key];
      }

      $array = $iterable;
    }

    foreach ($array as $key => $value) {

      $matches = [];

      preg_match(
        "/^$namespace\_/",
        $key,
        $matches
      );

      $shortKey = $namespaceInResult
        ? $key
        : str_replace($namespace .'_', '', $key);

      if ($matches)
        $result[$shortKey] = $array[$key];
    }

    return $raw
      ? $result
      : new Container($result);
  }

  /**
   * From postfix.
   *
   * @param  ContainerInterface|array $array
   * @param  string $postfix
   * @param  bool $raw
   * @param  bool $postfixInResult
   * @param  bool $includeServices
   * @return ContainerInterface|array
   */
  static public function fromPostfix(
    $array,
    $postfix,
    $raw = false,
    $postfixInResult = false,
    $includeServices = false
  ) {
    if (!is_array($array) && !($array instanceof ContainerInterface))
      throw new \InvalidArgumentException(sprintf(
        'First argument must be an array or instance of Psr\Container\ContainerInterface, got %s.',
        $array
      ));

    $result = [];

    // Fix for PimpleContainer.
    if ($array instanceof PimpleContainer) {

      $keys = $array->keys();
      $iterable = [];

      foreach ($keys as $key) {

        $isService = is_callable($array->raw($key));

        if (!$includeServices && $isService)
          continue;

        $iterable[$key] = $array[$key];
      }

      $array = $iterable;
    }

    foreach ($array as $key => $value) {

      $matches = [];

      preg_match(
        "/\_$postfix$/",
        $key,
        $matches
      );

      $shortKey = $postfixInResult
        ? $key
        : str_replace('_'. $postfix, '', $key);

      if ($matches)
        $result[$shortKey] = $array[$key];
    }

    return $raw
      ? $result
      : new Container($result);
  }

  /**
   * Filter by keys.
   *
   * @param  ContainerInterface|array $array
   * @param  array $keys
   * @param  bool $raw
   * @return array|Container
   */
  static public function filterByKeys(
    $array,
    array $keys,
    $raw = false
  ) {
    if (!is_array($array) && !($array instanceof ContainerInterface))
      throw new \InvalidArgumentException(sprintf(
        'First argument must be an array or instance of Psr\Container\ContainerInterface, got %s.',
        $array
      ));

    $result = [];

    foreach ($keys as $key) {

      Assert::keyExists($array, $key);

      $result[$key] = $array[$key];
    }

    return $raw
      ? $result
      : new Container($result);
  }

  /**
   * To array.
   *
   * @param  ContainerInterface $container
   * @param  bool $includeServices
   * @return array
   */
  static public function toArray(
    ContainerInterface $container,
    $includeServices = false
  ) {
    $array = [];

    if ($container instanceof PimpleContainer) {

      $keys = $container->keys();

      foreach ($keys as $key) {

        $isService = is_callable($container->raw($key));

        if (!$includeServices && $isService)
          continue;

        $array[$key] = $container[$key];
      }

    } else if ($container instanceof \Iterator) {

      $array = [];

      foreach ($container as $key => $value) {

        $isService = is_callable($value);

        if (!$includeServices && $isService)
          continue;

        $array[$key] = $value;
      }
    }

    return $array;
  }
}
