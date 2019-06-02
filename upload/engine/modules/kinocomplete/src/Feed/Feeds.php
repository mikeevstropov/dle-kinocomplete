<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Container\Container;
use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;

class Feeds
{
  /**
   * @var ContainerInterface
   */
  static protected $feeds;

  /**
   * Get feed.
   *
   * @param  string $feedName
   * @param  string $videoOrigin
   * @return Feed
   */
  static public function get(
    $feedName,
    $videoOrigin
  ) {
    if (!self::$feeds)
      self::createFeeds();

    $key = self::getKey(
      $feedName,
      $videoOrigin
    );

    return self::$feeds->get($key);
  }

  /**
   * Add feed.
   *
   * @param string $feedName
   * @param string $videoOrigin
   * @param callable $feedFactory
   */
  static protected function add(
    $feedName,
    $videoOrigin,
    callable $feedFactory
  ) {
    if (!self::$feeds)
      self::$feeds = new Container();

    $key = self::getKey(
      $feedName,
      $videoOrigin
    );

    self::$feeds[$key] = $feedFactory;
  }

  /**
   * Get key of feed.
   *
   * @param  string $feedName
   * @param  string $videoOrigin
   * @return string
   */
  static protected function getKey(
    $feedName,
    $videoOrigin
  ) {
    Assert::stringNotEmpty($videoOrigin);
    Assert::stringNotEmpty($feedName);

    return $feedName .'_'. $videoOrigin;
  }

  /**
   * Create feeds.
   */
  static protected function createFeeds()
  {
    $addMethod = function () {
      call_user_func_array(
        [__CLASS__, 'add'],
        func_get_args()
      );
    };

    MoonwalkFeedsInjector::inject($addMethod);
    KodikFeedsInjector::inject($addMethod);
  }
}
