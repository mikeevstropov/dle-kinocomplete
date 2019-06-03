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
   * Testing "getAll" method.
   */
  public function testCanGetAll()
  {
    $firstFeed  = new Feed();
    $secondFeed = new Feed();
    $thirdFeed  = new Feed();

    $reflection = new \ReflectionClass(Feeds::class);

    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);

    $property->setValue(new Container([
      'first-feed_origin'         => $firstFeed,
      'second-feed_origin'        => $secondFeed,
      'third-feed_another-origin' => $thirdFeed,
    ]));

    $method = $reflection->getMethod('getAll');

    $storedFeeds = $method->invoke(
      $reflection,
      'origin'
    );

    Assert::count($storedFeeds, 2);
    Assert::same($firstFeed, $storedFeeds[0]);
    Assert::same($secondFeed, $storedFeeds[1]);

    $storedFeeds = $method->invoke(
      $reflection
    );

    Assert::count($storedFeeds, 3);
    Assert::same($firstFeed, $storedFeeds[0]);
    Assert::same($secondFeed, $storedFeeds[1]);
    Assert::same($thirdFeed, $storedFeeds[2]);
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
