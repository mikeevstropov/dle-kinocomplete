<?php

namespace Kinocomplete\Video;

use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Templating\Templating;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class VideoFactory extends DefaultService
{
  /**
   * Apply patterns.
   *
   * @param  Video $video
   * @return Video
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function applyPatterns(Video $video)
  {
    $patterns = ContainerFactory::fromNamespace(
      $this->container,
      'video_pattern',
      true
    );

    $context = (array) $video;

    foreach ($patterns as $field => $pattern) {

      $field = Utils::snakeToCamel($field);

      $video->$field = Templating::renderString(
        $pattern,
        $context
      );
    }

    return $video;
  }

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
  public function createByMoonwalk(
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
    if (array_key_exists('camrip', $data)) {

      $video->quality = $data['camrip']
        ? 'CAMRip'
        : 'HD';
    }

    // Field "trailer".
    if (array_key_exists('trailer_iframe_url', $data)) {

      if (is_string($data['trailer_iframe_url']))
        $video->trailer = $data['trailer_iframe_url'];
    }

    return $this->applyPatterns($video);
  }

  /**
   * Factory method.
   * Parsing an array provided by TMDB API
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function createByTmdb(
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

      if (is_int($data['runtime'])) {

        if ($data['runtime'])
          $video->duration = $data['runtime'] * 60;
      }
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
  public function createByHdvb(
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

  /**
   * Create by Rutor.
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function createByRutor(
    array $data
  ) {
    Assert::isArray($data);

    if (!array_key_exists('id', $data) || !$data['id'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['id'];
    $video->origin = Video::RUTOR_ORIGIN;

    // Field "title".
    if (array_key_exists('title', $data)) {

      if (is_string($data['title']))
        $video->title = $data['title'];
    }

    // Field "magnetLink".
    if (array_key_exists('magnet_link', $data)) {

      if (is_string($data['magnet_link']))
        $video->magnetLink = $data['magnet_link'];
    }

    // Field "torrentFile".
    if (array_key_exists('file_link', $data)) {

      if (is_string($data['file_link']))
        $video->torrentFile = $data['file_link'];
    }

    // Field "torrentSize".
    if (array_key_exists('file_size', $data)) {

      if (is_int($data['file_size']))
        $video->torrentSize = Utils::bytesToHuman(
          $data['file_size']
        );
    }

    // Field "torrentSeeds".
    if (array_key_exists('seeds', $data)) {

      if (is_int($data['seeds']))
        $video->torrentSeeds = $data['seeds'];
    }

    // Field "torrentLeeches".
    if (array_key_exists('leeches', $data)) {

      if (is_int($data['leeches']))
        $video->torrentLeeches = $data['leeches'];
    }

    return $this->applyPatterns($video);
  }
}
