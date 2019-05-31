<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Templating\Templating;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

trait KodikFactoryTrait
{
  use FactoryTrait;

  /**
   * Factory method.
   * Parsing an array provided by Kodik API.
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function fromKodik(
    array $data
  ) {
    Assert::isArray($data);

    $playerPattern    = $this->container->get('kodik_player_pattern');
    $posterPattern    = $this->container->get('kodik_poster_pattern');
    $thumbnailPattern = $this->container->get('kodik_thumbnail_pattern');

    if (!array_key_exists('id', $data) || !$data['id'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['id'];
    $video->origin = Video::KODIK_ORIGIN;

    // Field "type".
    if (
      array_key_exists('type', $data)
      && is_string($data['type'])
    ) {
      $type = $data['type'];

      if (strpos($type, 'movie') !== false)
        $video->type = Video::MOVIE_TYPE;

      elseif (strpos($type, 'serial') !== false)
        $video->type = Video::SERIES_TYPE;
    }

    // Field "title".
    if (array_key_exists('title', $data)) {

      if ($data['title'])
        $video->title = (string) $data['title'];
    }

    // Field "worldTitle".
    if (array_key_exists('title_orig', $data)) {

      if ($data['title_orig'])
        $video->worldTitle = (string) $data['title_orig'];
    }

    // Field "poster".
    if (
      $posterPattern &&
      is_string($posterPattern) &&
      array_key_exists('kinopoisk_id', $data) &&
      $data['kinopoisk_id']
    ) {

      $video->poster = Templating::renderString(
        $posterPattern,
        ['kinopoisk_id' => $data['kinopoisk_id']]
      );
    }

    // Field "thumbnail".
    if (
      $thumbnailPattern &&
      is_string($thumbnailPattern) &&
      array_key_exists('kinopoisk_id', $data) &&
      $data['kinopoisk_id']
    ) {

      $video->thumbnail = Templating::renderString(
        $thumbnailPattern,
        ['kinopoisk_id' => $data['kinopoisk_id']]
      );
    }

    // Field "year".
    if (array_key_exists('year', $data)) {

      if ($data['year'])
        $video->year = (string) $data['year'];
    }

    // Field "translator".
    if (
      array_key_exists('translation', $data)
      && is_array($data['translation'])
    ) {
      $translation = $data['translation'];

      if (
        array_key_exists('title', $translation)
        && $translation['title']
      ) $video->translator = (string) $translation['title'];
    }

    // Field "kinopoiskId".
    if (array_key_exists('kinopoisk_id', $data)) {

      if ($data['kinopoisk_id'])
        $video->kinopoiskId = (string) $data['kinopoisk_id'];
    }

    // Field "pornoLabId".
    if (array_key_exists('pornolab_id', $data)) {

      if ($data['pornolab_id'])
        $video->pornoLabId = (string) $data['pornolab_id'];
    }

    // Field "imdbId".
    if (array_key_exists('imdb_id', $data)) {

      if ($data['imdb_id'])
        $video->imdbId = (string) $data['imdb_id'];
    }

    // Field "addedAt".
    if (array_key_exists('created_at', $data)) {

      $unixTime = strtotime(
        $data['created_at']
      );

      if ($unixTime)
        $video->addedAt = date(
          'Y-m-d H:i:s',
          $unixTime
        );
    }

    // Field "updatedAt".
    if (array_key_exists('updated_at', $data)) {

      $unixTime = strtotime(
        $data['updated_at']
      );

      if ($unixTime)
        $video->updatedAt = date(
          'Y-m-d H:i:s',
          $unixTime
        );
    }

    // Field "player".
    if (array_key_exists('link', $data)) {

      if (is_string($data['link'])) {

        $video->player = preg_replace(
          '/^.*?\/\/.*?\//',
          '/',
          $data['link']
        );

        $video->player = Templating::renderString(
          $playerPattern,
          ['path' => $video->player]
        );
      }
    }

    // Field "quality".
    if (array_key_exists('quality', $data)) {

      if (is_string($data['quality'])) {

        $quality = current(
          explode(' ', $data['quality'])
        );

        if ($quality)
          $video->quality = $quality;
      }
    }

    return $this->applyPatterns($video);
  }
}
