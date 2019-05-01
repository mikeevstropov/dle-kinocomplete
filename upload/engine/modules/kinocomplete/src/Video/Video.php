<?php

namespace Kinocomplete\Video;

use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;

class Video
{
  const MIXED_TYPE  = 'mixed';
  const VIDEO_TYPE  = 'video';
  const MOVIE_TYPE  = 'movie';
  const SERIES_TYPE = 'series';

  const MOONWALK_ORIGIN  = 'moonwalk';
  const TMDB_ORIGIN      = 'tmdb';
  const HDVB_ORIGIN      = 'hdvb';
  const VIDEO_CDN_ORIGIN = 'video-cdn';
  const RUTOR_ORIGIN     = 'rutor';

  /**
   * @var string
   */
  public $id = '';

  /**
   * @var string
   */
  public $origin = '';

  /**
   * @var string
   */
  public $type = Video::MIXED_TYPE;

  /**
   * @var string
   */
  public $title = '';

  /**
   * @var string
   */
  public $worldTitle = '';

  /**
   * @var string
   */
  public $tagline = '';

  /**
   * @var string
   */
  public $description = '';

  /**
   * @var string|int
   */
  public $duration = 0;

  /**
   * @var array
   */
  public $actors = [];

  /**
   * @var array
   */
  public $directors = [];

  /**
   * @var array
   */
  public $studios = [];

  /**
   * @var array;
   */
  public $countries = [];

  /**
   * @var array
   */
  public $genres = [];

  /**
   * @var string
   */
  public $ageGroup = 0;

  /**
   * @var string
   */
  public $poster = '';

  /**
   * @var string
   */
  public $thumbnail = '';

  /**
   * @var array
   */
  public $screenshots = [];

  /**
   * @var string YYYY
   */
  public $year = '';

  /**
   * @var string
   */
  public $translator = '';

  /**
   * @var string
   */
  public $kinopoiskId = '';

  /**
   * @var string
   */
  public $tmdbId = '';

  /**
   * @var string
   */
  public $worldArtId = '';

  /**
   * @var string
   */
  public $pornoLabId = '';

  /**
   * @var string
   */
  public $imdbId = '';

  /**
   * @var string YYYY-MM-DD hh:mm:ss
   */
  public $addedAt = '';

  /**
   * @var string YYYY-MM-DD hh:mm:ss
   */
  public $updatedAt = '';

  /**
   * @var string
   */
  public $kinopoiskRating = '';

  /**
   * @var string
   */
  public $kinopoiskVotes = '';

  /**
   * @var string
   */
  public $tmdbRating = '';

  /**
   * @var string
   */
  public $tmdbVotes = '';

  /**
   * @var string
   */
  public $imdbRating = '';

  /**
   * @var string
   */
  public $imdbVotes = '';

  /**
   * @var string
   */
  public $mpaaRating = '';

  /**
   * @var string
   */
  public $mpaaVotes = '';

  /**
   * @var string
   */
  public $player = '';

  /**
   * @var string
   */
  public $quality = '';

  /**
   * @var string
   */
  public $trailer = '';

  /**
   * @var string
   */
  public $magnetLink = '';

  /**
   * @var string
   */
  public $torrentFile = '';

  /**
   * @var string
   */
  public $torrentSize = '';

  /**
   * @var int
   */
  public $torrentSeeds = 0;

  /**
   * @var int
   */
  public $torrentLeeches = 0;

  /**
   * Get type label.
   *
   * @return string
   */
  public function getTypeCategoryLabel()
  {
    Assert::stringNotEmpty(
      $this->type,
      'Тип видео не определен.'
    );

    switch ($this->type) {

      case self::MIXED_TYPE:
        return 'разное';

      case self::VIDEO_TYPE:
        return 'видео';

      case self::MOVIE_TYPE:
        return 'фильмы';

      case self::SERIES_TYPE:
        return 'сериалы';

      default:
        throw new \InvalidArgumentException(
          'Неизвестный тип видео.'
        );
    }
  }

  /**
   * Get categories.
   *
   * @param  CategoryFactory $categoryFactory
   * @param  bool $fromType
   * @param  bool $fromGenres
   * @param  int $creationMode
   * @param  int $caseMode
   * @return array
   * @throws \Exception
   */
  public function getCategories(
    CategoryFactory $categoryFactory,
    $fromType = false,
    $fromGenres = false,
    $creationMode = 0,
    $caseMode = 0
  ) {
    $names = [];
    $categories = [];

    if ($fromType && $this->type !== self::MIXED_TYPE)
      $names[] = $this->getTypeCategoryLabel();

    if ($fromGenres)
      $names = array_merge(
        $names,
        $this->genres
      );

    if ($names) {

      switch ($caseMode) {

        case LOWER_CASE:
          $names = array_map(
            'mb_strtolower',
            $names
          );
          break;

        case UPPER_CASE:
          $names = array_map(
            'mb_strtoupper',
            $names
          );
          break;

        case CAPITAL_CASE:
          $names = array_map(
            Utils::class .'::toCapitalCase',
            $names
          );
          break;
      }

      $categories = $categoryFactory->fromNamesArray(
        $names,
        $creationMode
      );
    }

    return $categories;
  }

}
