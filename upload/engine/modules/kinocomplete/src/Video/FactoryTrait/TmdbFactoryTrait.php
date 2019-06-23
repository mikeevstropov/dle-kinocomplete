<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Video\Video;
use Webmozart\PathUtil\Path;
use Webmozart\Assert\Assert;

trait TmdbFactoryTrait
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
  public function fromTmdb(
    array $data
  ) {
    Assert::isArray($data);

    if (!array_key_exists('id', $data) || !$data['id'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['id'];
    $video->origin = Video::TMDB_ORIGIN;

    // Field "type".
    if (array_key_exists('media_type', $data)) {

      if (is_string($data['media_type'])) {

        if ($data['media_type'] === 'movie')
          $video->type = Video::MOVIE_TYPE;

        else if ($data['media_type'] === 'tv')
          $video->type = Video::SERIES_TYPE;
      }
    }

    // Field "title".
    if (array_key_exists('title', $data)) {

      if ($data['title'])
        $video->title = (string) $data['title'];

    } else if (array_key_exists('name', $data)) {

      if ($data['name'])
        $video->title = (string) $data['name'];
    }

    // Field "worldTitle".
    if (array_key_exists('original_title', $data)) {

      if ($data['original_title'])
        $video->worldTitle = (string) $data['original_title'];

    } else if (array_key_exists('original_name', $data)) {

      if ($data['original_name'])
        $video->worldTitle = (string) $data['original_name'];
    }

    // Field "tagline".
    if (array_key_exists('tagline', $data)) {

      if ($data['tagline'])
        $video->tagline = (string) $data['tagline'];
    }

    // Field "description".
    if (array_key_exists('overview', $data)) {

      if ($data['overview'])
        $video->description = (string) $data['overview'];
    }

    // Field "duration".
    if (array_key_exists('runtime', $data)) {

      $duration = $data['runtime'];

      if ($duration && is_int($duration))
        $video->duration = $duration * 60;
    }

    // Field "actors".
    if (array_key_exists('credits', $data)) {

      $credits = $data['credits'];

      if (
        is_array($credits) &&
        array_key_exists('cast', $credits)
      ) {

        $cast = $credits['cast'];

        if (is_array($cast)) {

          foreach ($cast as $actor) {

            if (
              is_array($actor) &&
              array_key_exists('name', $actor)
            ) {

              if (
                $actor['name'] &&
                is_string($actor['name'])
              ) $video->actors[] = $actor['name'];
            }

            if (count($video->actors) > 6)
              break;
          }
        }
      }
    }

    // Field "directors".
    if (array_key_exists('credits', $data)) {

      $credits = $data['credits'];

      if (
        is_array($credits) &&
        array_key_exists('crew', $credits)
      ) {

        $crew = $credits['crew'];

        if (is_array($crew)) {

          foreach ($crew as $employee) {

            if (
              is_array($employee) &&
              array_key_exists('name', $employee) &&
              array_key_exists('department', $employee)
            ) {

              if (
                $employee['name'] &&
                is_string($employee['name']) &&
                $employee['department'] === 'Directing'
              ) $video->directors[] = $employee['name'];
            }

            if (count($video->directors) > 6)
              break;
          }
        }
      }
    }

    // Field "studios".
    if (array_key_exists('production_companies', $data)) {

      $studios = $data['production_companies'];

      if (is_array($studios)) {

        foreach ($studios as $studio) {

          if (
            is_array($studio) &&
            array_key_exists('name', $studio)
          ) {

            if (
              $studio['name'] &&
              is_string($studio['name'])
            ) $video->studios[] = $studio['name'];
          }
        }
      }
    }

    // Field "countries".
    if (array_key_exists('production_countries', $data)) {

      $countries = $data['production_countries'];

      if (is_array($countries)) {

        foreach ($countries as $country) {

          if (
            is_array($country) &&
            array_key_exists('name', $country)
          ) {

            if (
              $country['name'] &&
              is_string($country['name'])
            ) $video->countries[] = $country['name'];
          }
        }
      }
    }

    // Field "genres".
    if (array_key_exists('genres', $data)) {

      $genres = $data['genres'];

      if (is_array($genres)) {

        foreach ($genres as $genre) {

          if (
            is_array($genre) &&
            array_key_exists('name', $genre)
          ) {

            if (
              $genre['name'] &&
              is_string($genre['name'])
            ) $video->genres[] = $genre['name'];
          }
        }
      }
    }

    // Field "ageGroup".
    if (array_key_exists('adult', $data)) {

      if ($data['adult'] === true)
        $video->ageGroup = '18';
    }

    // Fields "poster" and "thumbnail".
    if (array_key_exists('poster_path', $data)) {

      if (is_string($data['poster_path'])) {

        $video->poster = Path::join([
          'https://image.tmdb.org/t/p/w600_and_h900_bestv2',
          $data['poster_path']
        ]);

        $video->thumbnail = Path::join([
          'https://image.tmdb.org/t/p/w300_and_h450_bestv2',
          $data['poster_path']
        ]);
      }
    }

    // Field "year".
    if (array_key_exists('release_date', $data)) {

      if (is_string($data['release_date'])) {

        if (strlen($data['release_date']) === 10)
          $video->year = substr($data['release_date'], 0, 4);
      }
    }

    // Field "tmdbId".
    if (array_key_exists('id', $data)) {

      if ($data['id'])
        $video->tmdbId = (string) $data['id'];
    }

    // Field "imdbId".
    if (array_key_exists('imdb_id', $data)) {

      if ($data['imdb_id'])
        $video->imdbId = (string) $data['imdb_id'];
    }

    // Field "tmdbRating".
    if (array_key_exists('vote_average', $data)) {

      if ($data['vote_average'])
        $video->tmdbRating = (string) $data['vote_average'];
    }

    // Field "tmdbVotes".
    if (array_key_exists('vote_count', $data)) {

      if ($data['vote_count'])
        $video->tmdbVotes = (string) $data['vote_count'];
    }

    return $this->applyPatterns($video);
  }
}
