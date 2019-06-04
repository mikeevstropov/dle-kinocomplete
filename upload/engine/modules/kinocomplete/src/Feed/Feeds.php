<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Container\ContainerFactory;
use Psr\Container\ContainerInterface;
use Kinocomplete\Container\Container;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;

class Feeds
{
  /**
   * @var Container
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
   * Get all feeds.
   *
   * @param  string|null $videoOrigin
   * @return array
   */
  static public function getAll(
    $videoOrigin = null
  ) {
    Assert::nullOrStringNotEmpty(
      $videoOrigin
    );

    if (!self::$feeds)
      self::createFeeds();

    if ($videoOrigin) {

      $namedArray = ContainerFactory::fromPostfix(
        self::$feeds,
        $videoOrigin,
        true,
        true,
        true
      );

    } else {

      $namedArray = ContainerFactory::toArray(
        self::$feeds,
        true
      );
    }

    return array_values($namedArray);
  }

  /**
   * Get enabled feeds by configuration
   * container which contain enable flags.
   *
   * @param  ContainerInterface $configuration
   * @param  string|null $videoOrigin
   * @return array
   */
  static public function getEnabled(
    ContainerInterface $configuration,
    $videoOrigin = null
  ) {
    Assert::nullOrStringNotEmpty(
      $videoOrigin
    );

    if (!self::$feeds)
      self::createFeeds();

    if ($videoOrigin) {

      $namedArray = ContainerFactory::fromPostfix(
        self::$feeds,
        $videoOrigin,
        true,
        true,
        true
      );

    } else {

      $namedArray = ContainerFactory::toArray(
        self::$feeds,
        true
      );
    }

    $enabledFeeds = [];

    foreach ($namedArray as $nameAndOrigin => $feed) {

      $keyParts = explode('_', $nameAndOrigin);

      Assert::count(
        $keyParts,
        2,
        'Неверный ключ фида.'
      );

      $name = Utils::hyphenToSnake($keyParts[0]);
      $origin = Utils::hyphenToSnake($keyParts[1]);

      $option = $origin .'_'. $name .'_feed_enabled';
      $has = $configuration->has($option);

      if ($has && $configuration->get($option))
        $enabledFeeds[] = $feed;
    }

    return $enabledFeeds;
  }

  /**
   * Add feed.
   *
   * @param Feed $feed
   */
  static protected function add(Feed $feed)
  {
    if (!self::$feeds)
      self::$feeds = new Container();

    $key = self::getKey(
      $feed->getName(),
      $feed->getVideoOrigin()
    );

    self::$feeds[$key] = $feed;
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

    if (strpos($feedName, '_') !== false)
      throw new \InvalidArgumentException(
        'Имя фида имеет недопустимый символ нижнего подчеркивания.'
      );

    if (strpos($videoOrigin, '_') !== false)
      throw new \InvalidArgumentException(
        'Имя источника имеет недопустимый символ нижнего подчеркивания.'
      );

    return $feedName .'_'. $videoOrigin;
  }

  /**
   * Create feeds.
   */
  static protected function createFeeds()
  {
    $injector = function (Feed $feed) {
      self::add($feed);
    };

    MoonwalkFeedsInjector::inject($injector);
    KodikFeedsInjector::inject($injector);
  }
}
