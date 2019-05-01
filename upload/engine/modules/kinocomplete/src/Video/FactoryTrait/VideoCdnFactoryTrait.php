<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Templating\Templating;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

trait VideoCdnFactoryTrait
{
  use FactoryTrait;

  /**
   * Factory method.
   * Parsing an array provided by TMDB API
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   */
  public function fromVideoCdn(
    array $data
  ) {
    Assert::isArray($data);

    $playerPattern    = $this->container->get('moonwalk_player_pattern');
    $posterPattern    = $this->container->get('moonwalk_poster_pattern');
    $thumbnailPattern = $this->container->get('moonwalk_thumbnail_pattern');

    if (!array_key_exists('id', $data) || !$data['id'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['id'];
    $video->origin = Video::VIDEO_CDN_ORIGIN;

    // Field "type".
    if (array_key_exists('type', $data)) {

      if (is_string($data['type'])) {

        if ($data['type'] === 'movie')
          $video->type = Video::MOVIE_TYPE;

        else if ($data['type'] === 'serial')
          $video->type = Video::SERIES_TYPE;
      }
    }

    // Field "title".
    if (array_key_exists('title', $data)) {

      if ($data['title'])
        $video->title = (string) $data['title'];
    }

    // Field "kinopoiskId".
    if (array_key_exists('kp_id', $data)) {

      if ($data['kp_id'])
        $video->kinopoiskId = (string) $data['kp_id'];
    }

    // Field "worldArtId".
    if (array_key_exists('world_art_id', $data)) {

      if ($data['world_art_id'])
        $video->worldArtId = (string) $data['world_art_id'];
    }

    // Field "addedAt".
    if (array_key_exists('add', $data)) {

      if (is_string($data['add']))
        $video->addedAt = $data['add'];
    }

    // Field "translator".
    if (array_key_exists('translations', $data)) {

      if (is_array($data['translations'])) {

        $translator = current($data['translations']);

        if (
          $translator &&
          is_string($translator)
        ) $video->translator = $translator;
      }
    }

    // Field "poster".
    if (
      $posterPattern &&
      is_string($posterPattern) &&
      array_key_exists('kp_id', $data) &&
      $data['kp_id']
    ) {

      $video->poster = Templating::renderString(
        $posterPattern,
        ['kinopoisk_id' => $data['kp_id']]
      );
    }

    // Field "thumbnail".
    if (
      $thumbnailPattern &&
      is_string($thumbnailPattern) &&
      array_key_exists('kp_id', $data) &&
      $data['kp_id']
    ) {

      $video->thumbnail = Templating::renderString(
        $thumbnailPattern,
        ['kinopoisk_id' => $data['kp_id']]
      );
    }

    // Field "player".
    if (array_key_exists('iframe_src', $data)) {

      if (is_string($data['iframe_src'])) {

        $video->player = preg_replace(
          '/^.*?\/\/.*?\//',
          '/',
          $data['iframe_src']
        );

        $video->player = Templating::renderString(
          $playerPattern,
          ['path' => $video->player]
        );
      }
    }

    return $this->applyPatterns($video);
  }
}
