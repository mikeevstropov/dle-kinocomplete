<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Container\Container;
use Psr\Container\ContainerInterface;
use Kinocomplete\Video\Video;
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
   * @param callable $closure
   */
  static protected function add(
    $feedName,
    $videoOrigin,
    callable $closure
  ) {
    if (!self::$feeds)
      self::$feeds = new Container();

    $key = self::getKey(
      $feedName,
      $videoOrigin
    );
    
    self::$feeds[$key] = $closure;
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
    // Moonwalk feed "foreign-movies".
    self::add(
      'foreign-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_foreign.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(49719268);

        return $feed;
      }
    );

    // Moonwalk feed "russian-movies".
    self::add(
      'russian-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_russian.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(5085601);

        return $feed;
      }
    );

    // Moonwalk feed "camrip-movies".
    self::add(
      'camrip-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('camrip-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_camrip.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(1415144);

        return $feed;
      }
    );

    // Moonwalk feed "foreign-series".
    self::add(
      'foreign-series',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-series');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('serials_foreign.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(16970833);

        return $feed;
      }
    );

    // Moonwalk feed "russian-series".
    self::add(
      'russian-series',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-series');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('serials_russian.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(5535230);

        return $feed;
      }
    );

    // Moonwalk feed "anime-movies".
    self::add(
      'anime-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('anime-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_anime.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(1189131);

        return $feed;
      }
    );

    // Moonwalk feed "anime-series".
    self::add(
      'anime-series',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('anime-series');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('serials_anime.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(6542717);

        return $feed;
      }
    );
  }
}