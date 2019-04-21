<?php

namespace Kinocomplete\Data;

use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class Data
{
  /**
   * @var string
   */
  static protected $dataDir = __DIR__ .'/../../data';

  /**
   * Get post accessory video fields.
   *
   * @return array
   */
  static function getPostAccessoryVideoFields()
  {
    static $array;

    if ($array === null) {

      $content = file_get_contents(
        Path::join(
          self::$dataDir,
          'post-accessory-video-fields.json'
        )
      );

      $array = json_decode($content, true);

      foreach ($array as $item) {

        Assert::keyExists($item, 'name');
        Assert::keyExists($item, 'label');
      }
    }

    return $array;
  }

  /**
   * Get post updater video fields.
   *
   * @return array
   */
  static function getPostUpdaterVideoFields()
  {
    static $array;

    if ($array === null) {

      $content = file_get_contents(
        Path::join(
          self::$dataDir,
          'post-updater-video-fields.json'
        )
      );

      $array = json_decode($content, true);

      foreach ($array as $item) {

        Assert::keyExists($item, 'name');
        Assert::keyExists($item, 'label');
      }
    }

    return $array;
  }

  /**
   * Get TMDB languages.
   *
   * @return array
   */
  static function getTmdbLanguages()
  {
    static $array;

    if ($array === null) {

      $content = file_get_contents(
        Path::join(
          self::$dataDir,
          'tmdb-languages.json'
        )
      );

      $array = json_decode($content, true);

      foreach ($array as $item) {

        Assert::keyExists($item, 'iso_639_1');
        Assert::keyExists($item, 'english_name');
        Assert::keyExists($item, 'name');
      }
    }

    return $array;
  }
}