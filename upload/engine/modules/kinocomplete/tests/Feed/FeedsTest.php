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
   * Testing "add" method.
   */
  public function testCanAdd()
  {
    $feed = new Feed();
    $feed->setName(Utils::randomString());
    $feed->setVideoOrigin(Utils::randomString());

    $reflection = new \ReflectionClass(Feeds::class);

    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);
    $property->setValue(new Container());

    $addMethod = $reflection->getMethod('add');
    $addMethod->setAccessible(true);

    $addMethod->invoke(
      $reflection,
      $feed
    );

    Assert::same(
      $feed,
      Feeds::get(
        $feed->getName(),
        $feed->getVideoOrigin()
      )
    );
  }

  /**
   * Testing "add" method exceptions.
   */
  public function testCannotAdd()
  {
    $exceptionsCount = 0;
    $expectedCount = 2;

    $invalidNameFeed = new Feed();
    $invalidNameFeed->setName('test_name');
    $invalidNameFeed->setVideoOrigin('origin');

    $invalidOriginFeed = new Feed();
    $invalidOriginFeed->setName('name');
    $invalidOriginFeed->setVideoOrigin('test_origin');

    $reflection = new \ReflectionClass(Feeds::class);

    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);
    $property->setValue(new Container());

    $addMethod = $reflection->getMethod('add');
    $addMethod->setAccessible(true);

    try {

      $addMethod->invoke(
        $reflection,
        $invalidNameFeed
      );

    } catch (\InvalidArgumentException $e) {

      ++$exceptionsCount;
    }

    try {

      $addMethod->invoke(
        $reflection,
        $invalidOriginFeed
      );

    } catch (\InvalidArgumentException $e) {

      ++$exceptionsCount;
    }

    Assert::same(
      $expectedCount,
      $exceptionsCount
    );

    Assert::isEmpty(
      Feeds::getAll('origin')
    );
  }

  /**
   * Testing "getKey" method.
   */
  public function testCanGetKey()
  {
    $name = 'test-feed';
    $origin = 'test-origin';
    $expected = $name .'_'. $origin;

    $reflection = new \ReflectionClass(Feeds::class);

    $property = $reflection->getProperty('feeds');
    $property->setAccessible(true);
    $property->setValue(new Container());

    $method = $reflection->getMethod('getKey');
    $method->setAccessible(true);

    Assert::same(
      $expected,
      $method->invoke(
        $reflection,
        $name,
        $origin
      )
    );
  }

  /**
   * Testing "getKey" method exceptions.
   */
  public function testCannotGetKey()
  {
    $exceptions = 0;
    $expectedExceptions = 2;

    $reflection = new \ReflectionClass(Feeds::class);

    $method = $reflection->getMethod('getKey');
    $method->setAccessible(true);

    try {

      $method->invoke(
        $reflection,
        'test_name',
        'origin'
      );

    } catch (\InvalidArgumentException $e) {

      ++$exceptions;
    }

    try {

      $method->invoke(
        $reflection,
        'name',
        'test_origin'
      );

    } catch (\InvalidArgumentException $e) {

      ++$exceptions;
    }

    Assert::same(
      $expectedExceptions,
      $exceptions
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
