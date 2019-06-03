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
   * Testing "getEnabled" method.
   */
  public function testCanGetEnabled()
  {
    $firstFeed  = new Feed();
    $secondFeed = new Feed();
    $thirdFeed  = new Feed();
    $fourthFeed = new Feed();

    $reflection = new \ReflectionClass(Feeds::class);

    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);

    $property->setValue(new Container([
      'first-feed_first-origin'   => $firstFeed,
      'second-feed_first-origin'  => $secondFeed,
      'third-feed_second-origin'  => $thirdFeed,
      'fourth-feed_second-origin' => $fourthFeed,
    ]));

    $method = $reflection->getMethod('getEnabled');

    $configuration = new Container([
      'first_origin_first_feed_feed_enabled' => '1',
      'first_origin_second_feed_feed_enabled' => '1',
      'second_origin_third_feed_feed_enabled' => '1',
      'second_origin_fourth_feed_feed_enabled' => '0',
    ]);

    $enabledFeeds = $method->invoke(
      $reflection,
      $configuration,
      'first-origin'
    );

    Assert::count($enabledFeeds, 2);
    Assert::same($firstFeed, $enabledFeeds[0]);
    Assert::same($secondFeed, $enabledFeeds[1]);

    $enabledFeeds = $method->invoke(
      $reflection,
      $configuration
    );

    Assert::count($enabledFeeds, 3);
    Assert::same($firstFeed, $enabledFeeds[0]);
    Assert::same($secondFeed, $enabledFeeds[1]);
    Assert::same($thirdFeed, $enabledFeeds[2]);
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
