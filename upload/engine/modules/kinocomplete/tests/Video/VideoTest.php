<?php

namespace Kinocomplete\Test\Video;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\Container\Container;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

class VideoTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "getTypeCategoryLabel" method.
   */
  public function testCanGetTypeCategoryLabel()
  {
    $video = new Video();
    $video->type = Video::VIDEO_TYPE;

    Assert::stringNotEmpty(
      $video->getTypeCategoryLabel()
    );

    $video->type = Video::MOVIE_TYPE;

    Assert::stringNotEmpty(
      $video->getTypeCategoryLabel()
    );

    $video->type = Video::SERIES_TYPE;

    Assert::stringNotEmpty(
      $video->getTypeCategoryLabel()
    );
  }

  /**
   * Testing "getTypeCategoryLabel" method exceptions.
   */
  public function testCannotGetTypeCategoryLabel()
  {
    $this->expectException(\InvalidArgumentException::class);

    $video = new Video();
    $video->type = '';
    $video->getTypeCategoryLabel();
  }

  /**
   * Testing "getCategories" method.
   *
   * @throws \Exception
   */
  public function testCanGetCategories()
  {
    $video = new Video();
    $video->genres = ['genre'];
    $video->type = Video::MOVIE_TYPE;

    $categoryFactory = new CategoryFactory(
      new Container([
        'categories' => []
      ])
    );

    $typeCategories = $video->getCategories(
      $categoryFactory,
      true,
      false
    );

    Assert::count($typeCategories, 1);

    Assert::same(
      $typeCategories[0]->name,
      $video->getTypeCategoryLabel()
    );

    $genreCategories = $video->getCategories(
      $categoryFactory,
      false,
      true
    );

    Assert::count($genreCategories, 1);

    Assert::same(
      $genreCategories[0]->name,
      'genre'
    );
  }

  /**
   * Testing "getCategories" method in LOWER_CASE mode.
   *
   * @throws \Exception
   */
  public function testCanGetCategoriesByLowerCase()
  {
    $name = 'gEnRe cAtEgOrY';
    $expectedName = 'genre category';

    $video = new Video();
    $video->genres = [$name];

    $categoryFactory = $this->getContainer()
      ->get('category_factory');

    $genreCategories = $video->getCategories(
      $categoryFactory,
      false,
      true,
      DEFAULT_MODE,
      LOWER_CASE
    );

    $category = current($genreCategories);

    Assert::same(
      $category->name,
      $expectedName
    );
  }

  /**
   * Testing "getCategories" method in UPPER_CASE mode.
   *
   * @throws \Exception
   */
  public function testCanGetCategoriesByUpperCase()
  {
    $name = 'gEnRe cAtEgOrY';
    $expectedName = 'GENRE CATEGORY';

    $video = new Video();
    $video->genres = [$name];

    $categoryFactory = $this->getContainer()
      ->get('category_factory');

    $genreCategories = $video->getCategories(
      $categoryFactory,
      false,
      true,
      DEFAULT_MODE,
      UPPER_CASE
    );

    $category = current($genreCategories);

    Assert::same(
      $category->name,
      $expectedName
    );
  }

  /**
   * Testing "getCategories" method in CAPITAL_CASE mode.
   *
   * @throws \Exception
   */
  public function testCanGetCategoriesByCapitalCase()
  {
    $name = 'gEnRe cAtEgOrY';
    $expectedName = 'Genre category';

    $video = new Video();
    $video->genres = [$name];

    $categoryFactory = $this->getContainer()
      ->get('category_factory');

    $genreCategories = $video->getCategories(
      $categoryFactory,
      false,
      true,
      DEFAULT_MODE,
      CAPITAL_CASE
    );

    $category = current($genreCategories);

    Assert::same(
      $category->name,
      $expectedName
    );
  }
}