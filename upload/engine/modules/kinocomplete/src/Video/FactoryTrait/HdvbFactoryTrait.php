<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Templating\Templating;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

trait HdvbFactoryTrait
{
  use FactoryTrait;

  /**
   * Factory method.
   * Parsing an array provided by HDVB API.
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function fromHdvb(
    array $data
  ) {
    Assert::isArray($data);

    $playerPattern    = $this->container->get('hdvb_player_pattern');
    $posterPattern    = $this->container->get('hdvb_poster_pattern');
    $thumbnailPattern = $this->container->get('hdvb_thumbnail_pattern');

    if (!array_key_exists('token', $data) || !$data['token'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['token'];
    $video->origin = Video::HDVB_ORIGIN;

    // Field "title".
    if (array_key_exists('title_ru', $data)) {

      if ($data['title_ru'])
        $video->title = (string) $data['title_ru'];
    }

    // Field "worldTitle".
    if (array_key_exists('title_en', $data)) {

      if ($data['title_en'])
        $video->worldTitle = (string) $data['title_en'];
    }

    // Field "year".
    if (array_key_exists('year', $data)) {

      if ($data['year'])
        $video->year = (string) $data['year'];
    }

    // Field "kinopoiskId".
    if (array_key_exists('kinopoisk_id', $data)) {

      if ($data['kinopoisk_id'])
        $video->kinopoiskId = (string) $data['kinopoisk_id'];
    }

    // Field "worldArtId".
    if (array_key_exists('world_art_id', $data)) {

      if ($data['world_art_id'])
        $video->worldArtId = (string) $data['world_art_id'];
    }

    // Field "translator".
    if (array_key_exists('translator', $data)) {

      if (is_string($data['translator']))
        $video->translator = $data['translator'];
    }

    // Field "type".
    if (array_key_exists('type', $data)) {

      if (is_string($data['type'])) {

        if ($data['type'] === 'movie')
          $video->type = Video::MOVIE_TYPE;

        else if ($data['type'] === 'serial')
          $video->type = Video::SERIES_TYPE;
      }
    }

    // Field "player".
    if (array_key_exists('iframe_url', $data)) {

      if (is_string($data['iframe_url'])) {

        $video->player = preg_replace(
          '/^.*?\/\/.*?\//',
          '/',
          $data['iframe_url']
        );

        $video->player = Templating::renderString(
          $playerPattern,
          ['path' => $video->player]
        );
      }
    }

    // Field "addedAt".
    if (array_key_exists('added_date', $data)) {

      if (is_string($data['added_date']))
        $video->addedAt = $data['added_date'];
    }

    // Field "updatedAt"
    if (array_key_exists('update_date', $data)) {

      if (is_string($data['update_date']))
        $video->updatedAt = $data['update_date'];
    }

    // Field "quality".
    if (array_key_exists('quality', $data)) {

      if (is_string($data['quality']))
        $video->quality = $data['quality'];
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

    } else if (
      array_key_exists('poster', $data) &&
      $data['poster'] &&
      is_string($data['poster'])
    ) {

      $video->poster = $data['poster'];
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

    } else if (
      array_key_exists('poster', $data) &&
      $data['poster'] &&
      is_string($data['poster'])
    ) {

      $video->thumbnail = $data['poster'];
    }

    // Field "trailer".
    if (array_key_exists('trailer', $data)) {

      if (is_string($data['trailer']))
        $video->trailer = $data['trailer'];
    }

    return $this->applyPatterns($video);
  }
}
