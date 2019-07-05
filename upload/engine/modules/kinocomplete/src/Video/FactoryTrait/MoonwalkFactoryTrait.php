<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Templating\Templating;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

trait MoonwalkFactoryTrait
{
  use FactoryTrait;

  /**
   * Factory method.
   * Parsing an array provided by Moonwalk API.
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function fromMoonwalk(
    array $data
  ) {
    Assert::isArray($data);

    $playerPattern    = $this->container->get('moonwalk_player_pattern');
    $posterPattern    = $this->container->get('moonwalk_poster_pattern');
    $thumbnailPattern = $this->container->get('moonwalk_thumbnail_pattern');

    if (!array_key_exists('token', $data) || !$data['token'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['token'];
    $video->origin = Video::MOONWALK_ORIGIN;

    $material = array_key_exists('material_data', $data)
      ? $data['material_data']
      : [];

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
    if (array_key_exists('title_ru', $data)) {

      if ($data['title_ru'])
        $video->title = (string) $data['title_ru'];
    }

    // Field "worldTitle".
    if (array_key_exists('title_en', $data)) {

      if ($data['title_en'])
        $video->worldTitle = (string) $data['title_en'];
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
    if (array_key_exists('duration', $data)) {

      if (is_array($data['duration'])) {

        if (array_key_exists('seconds', $data['duration'])) {

          if (is_int($data['duration']['seconds']))
            $video->duration = $data['duration']['seconds'];
        }
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

    // Field "studios".
    if (array_key_exists('studios', $material)) {

      if (is_array($material['studios']))
        $video->studios = array_filter(
          $material['studios'],
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

    // Field "ageGroup".
    if (array_key_exists('age', $material)) {

      if ($material['age'])
        $video->ageGroup = (string) $material['age'];
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
      array_key_exists('poster', $material) &&
      $material['poster'] &&
      is_string($material['poster'])
    ) {

      $video->poster = $material['poster'];
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
      array_key_exists('poster', $material) &&
      $material['poster'] &&
      is_string($material['poster'])
    ) {

      $video->thumbnail = $material['poster'];
    }

    // Field "screenshots".
    if (array_key_exists('screenshots', $data)) {

      if (is_array($data['screenshots'])) {

        $video->screenshots = array_filter(
          $data['screenshots'],
          'is_string'
        );
      }
    }

    // Field "year".
    if (array_key_exists('year', $material)) {

      if ($material['year'])
        $video->year = (string) $material['year'];
    }

    // Field "translator".
    if (array_key_exists('translator', $data)) {

      if (is_string($data['translator']))
        $video->translator = $data['translator'];
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

    // Field "pornoLabId".
    if (array_key_exists('pornolab_id', $data)) {

      if ($data['pornolab_id'])
        $video->pornoLabId = (string) $data['pornolab_id'];
    }

    // Field "addedAt".
    if (array_key_exists('added_at', $data)) {

      if (is_string($data['added_at']))
        $video->addedAt = $data['added_at'];
    }

    // Field "updatedAt"
    if (array_key_exists('updated_at', $material)) {

      if (is_string($material['updated_at']))
        $video->updatedAt = $material['updated_at'];
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

    // Field "mpaaRating".
    if (array_key_exists('mpaa_rating', $material)) {

      if ($material['mpaa_rating'])
        $video->mpaaRating = (string) $material['mpaa_rating'];
    }

    // Field "mpaaVotes".
    if (array_key_exists('mpaa_votes', $material)) {

      if ($material['mpaa_votes'])
        $video->mpaaVotes = (string) $material['mpaa_votes'];
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

    // Field "quality".
    $camrip = array_key_exists('camrip', $data)
      ? (bool) $data['camrip']
      : false;

    $quality = array_key_exists('source_type', $data)
      ? (string) $data['source_type']
      : null;

    $video->quality = $camrip == false
      ? $quality ?: 'HD'
      : 'CAMRip';

    // Field "trailer".
    if (array_key_exists('trailer_iframe_url', $data)) {

      if (is_string($data['trailer_iframe_url']))
        $video->trailer = $data['trailer_iframe_url'];
    }

    return $this->applyPatterns($video);
  }
}
