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

    $material = array_key_exists('material_data', $data)
      ? $data['material_data']
      : [];

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

    // Field "tagline".
    if (array_key_exists('tagline', $material)) {

      if ($material['tagline'])
        $video->tagline = (string) $material['tagline'];
    }

    // Field "description".
    if (array_key_exists('description', $material)) {

      if ($material['description'])
        $video->description = (string) $material['description'];
    }

    // Field "duration".
    if (array_key_exists('duration', $material)) {

      if ($material['duration']) {

        if (is_int($material['duration']))
          $video->duration = $material['duration'] * 60;
      }
    }

    // Field "actors".
    if (array_key_exists('actors', $material)) {

      if (is_array($material['actors']))
        $video->actors = array_filter(
          $material['actors'],
          'is_string'
        );
    }

    // Field "directors".
    if (array_key_exists('directors', $material)) {

      if (is_array($material['directors']))
        $video->directors = array_filter(
          $material['directors'],
          'is_string'
        );
    }

    // Field "countries".
    if (array_key_exists('countries', $material)) {

      if (is_array($material['countries']))
        $video->countries = array_filter(
          $material['countries'],
          'is_string'
        );
    }

    // Field "genres".
    if (array_key_exists('genres', $material)) {

      if (is_array($material['genres']))
        $video->genres = array_filter(
          $material['genres'],
          'is_string'
        );
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
      array_key_exists('poster_url', $material) &&
      $material['poster_url'] &&
      is_string($material['poster_url'])
    ) {

      $video->poster = $material['poster_url'];
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
      array_key_exists('poster_url', $material) &&
      $material['poster_url'] &&
      is_string($material['poster_url'])
    ) {

      $video->thumbnail = $material['poster_url'];
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

    // Field "kinopoiskRating".
    if (array_key_exists('kinopoisk_rating', $material)) {

      if ($material['kinopoisk_rating'])
        $video->kinopoiskRating = (string) $material['kinopoisk_rating'];
    }

    // Field "kinopoiskVotes".
    if (array_key_exists('kinopoisk_votes', $material)) {

      if ($material['kinopoisk_votes'])
        $video->kinopoiskVotes = (string) $material['kinopoisk_votes'];
    }

    // Field "imdbRating".
    if (array_key_exists('imdb_rating', $material)) {

      if ($material['imdb_rating'])
        $video->imdbRating = (string) $material['imdb_rating'];
    }

    // Field "imdbVotes".
    if (array_key_exists('imdb_votes', $material)) {

      if ($material['imdb_votes'])
        $video->imdbVotes = (string) $material['imdb_votes'];
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
