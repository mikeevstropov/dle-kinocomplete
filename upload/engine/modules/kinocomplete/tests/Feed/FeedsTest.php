<?php

namespace Kinocomplete\Test\Source;

use Slim\Exception\ContainerValueNotFoundException;
use Kinocomplete\Container\Container;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Feed\Feeds;
use Webmozart\Assert\Assert;
use Kinocomplete\Feed\Feed;

class FeedsTest extends TestCase
{
  /**
   * Testing "get" method.
   */
  public function testCanGet()
  {
    $expected = new Feed();

    $reflection = new \ReflectionClass(Feeds::class);

    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);

    $property->setValue(new Container([
      'name_origin' => $expected
    ]));

    $method = $reflection->getMethod('get');

    Assert::same(
      $expected,
      $method->invoke(
        $reflection,
        'name',
        'origin'
      )
    );
  }

  /**
   * Testing "get" method exceptions.
   */
  public function testCannotGet()
  {
    $this->expectException(ContainerValueNotFoundException::class);

    Feeds::get(
      Utils::randomString(),
      Utils::randomString()
    );
  }

  /**
   * Tear down after class.
   *
   * @throws \ReflectionException
   */
  public static function tearDownAfterClass()
  {
    $reflection = new \ReflectionClass(Feeds::class);
    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);
    $property->setValue(null);
  }
}
