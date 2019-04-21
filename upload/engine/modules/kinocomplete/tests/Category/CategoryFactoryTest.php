<?php

namespace Kinocomplete\Test\Category;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\Container\Container;
use Kinocomplete\Category\Category;
use Kinocomplete\Api\SystemApi;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class CategoryFactoryTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing `fromDatabaseArray` method.
   */
  public function testCanFromDatabaseArray()
  {
    $array = [
      'id'             => '1',
      'parentId'       => '2',
      'alt_name'       => 'alt-category',
      'name'           => 'category',
      'posi'           => '3',
      'allow_rss'      => '0',
      'disable_search' => '1'
    ];

    $expected = [
      'id'            => '1',
      'parentId'      => '2',
      'slug'          => 'alt-category',
      'name'          => 'category',
      'position'      => 3,
      'rssAllowed'    => false,
      'searchAllowed' => false
    ];

    $categoryFactory = new CategoryFactory(
      new Container()
    );

    $category = $categoryFactory->fromDatabaseArray(
      $array
    );

    Assert::isInstanceOf(
      $category,
      Category::class
    );

    $this->assertEquals(
      $expected,
      (array) $category
    );
  }

  /**
   * Testing `toDatabaseArray` method.
   */
  public function testCanToDatabaseArray()
  {
    $expected = [
      'id'             => '1',
      'parentId'       => '2',
      'alt_name'       => 'alt-category',
      'name'           => 'category',
      'posi'           => 3,
      'allow_rss'      => 0,
      'disable_search' => 1
    ];

    $category                = new Category();
    $category->id            = '1';
    $category->parentId      = '2';
    $category->slug          = 'alt-category';
    $category->name          = 'category';
    $category->position      = 3;
    $category->rssAllowed    = false;
    $category->searchAllowed = false;

    $categoryFactory = new CategoryFactory(
      new Container()
    );

    $array = $categoryFactory->toDatabaseArray(
      $category
    );

    $this->assertEquals(
      $expected,
      $array
    );
  }

  /**
   * Testing `fromDatabaseString` method.
   */
  public function testCanFromDatabaseString()
  {
    $string = '1, 2,3';

    $firstCategory      = new Category();
    $firstCategory->id  = '1';
    $secondCategory     = new Category();
    $secondCategory->id = '2';
    $thirdCategory      = new Category();
    $thirdCategory->id  = '3';

    $categories = [
      $firstCategory,
      $secondCategory,
      $thirdCategory
    ];

    $idMapper = function(Category $item) {
      return $item->id;
    };

    $expectedIds = array_map(
      $idMapper,
      $categories
    );

    $container = new Container([
      'categories' => $categories,
    ]);

    $categoryFactory = new CategoryFactory(
      $container
    );

    $result = $categoryFactory->fromDatabaseString(
      $string
    );

    $ids = array_map(
      $idMapper,
      $result
    );

    $this->assertEquals(
      $expectedIds,
      $ids
    );
  }

  /**
   * Testing `fromDatabaseString` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotFromDatabaseString()
  {
    $this->expectException(NotFoundException::class);

    $container = new Container([
      'categories' => []
    ]);

    $categoryFactory = new CategoryFactory(
      $container
    );

    $categoryFactory->fromDatabaseString('1');
  }

  /**
   * Testing `fromNamesArray` method.
   *
   * @throws \Exception
   */
  public function testCanFromNamesArray()
  {
    $existedFirst = new Category();
    $existedFirst->name = 'First';

    $existedSecond = new Category();
    $existedSecond->name = 'second';

    $existed = [
      $existedFirst,
      $existedSecond
    ];

    $container = new Container([
      'categories' => $existed
    ]);

    $names = [
      'first',
      'second',
      'third'
    ];

    $categoryFactory = new CategoryFactory(
      $container
    );

    $categories = $categoryFactory->fromNamesArray(
      $names
    );

    Assert::count($categories, 3);

    foreach ($categories as $category) {

      Assert::isInstanceOf(
        $category,
        Category::class
      );
    }

    Assert::same(
      strtolower($categories[0]->name),
      $names[0]
    );

    Assert::same(
      $categories[1]->name,
      $names[1]
    );

    Assert::same(
      $categories[2]->name,
      $names[2]
    );
  }

  /**
   * Testing `fromNamesArray` method in USE_ONLY_EXISTED mode.
   *
   * @throws \Exception
   */
  public function testCanFromNamesArrayUseOnlyExisted()
  {
    $existedFirst = new Category();
    $existedFirst->id = '1';
    $existedFirst->name = 'First';

    $existedSecond = new Category();
    $existedSecond->id = '2';
    $existedSecond->name = 'second';

    $existed = [
      $existedFirst,
      $existedSecond
    ];

    $container = new Container([
      'categories' => $existed
    ]);

    $names = [
      'first',
      'second',
      'third'
    ];

    $categoryFactory = new CategoryFactory(
      $container
    );

    $categories = $categoryFactory->fromNamesArray(
      $names,
      CategoryFactory::USE_ONLY_EXISTED
    );

    Assert::count($categories, 2);

    foreach ($categories as $category) {

      Assert::isInstanceOf(
        $category,
        Category::class
      );
    }

    Assert::same(
      strtolower($categories[0]->name),
      $names[0]
    );

    Assert::same(
      $categories[1]->name,
      $names[1]
    );
  }

  /**
   * Testing `fromNamesArray` method in CREATE_NOT_EXISTED mode.
   *
   * @throws \Exception
   */
  public function testCanFromNamesArrayCreateNotExisted()
  {
    $notExistedCategory = new Category();
    $notExistedCategory->name = 'notExistedCategory';

    $systemApi = $this->getMockBuilder(
      SystemApi::class
    )->setConstructorArgs([
      new Container()
    ])->getMock();

    $systemApi
      ->method('addCategory')
      ->willReturn($notExistedCategory);

    $container = new Container([
      'system_api' => $systemApi,
      'categories' => []
    ]);

    $categoryFactory = new CategoryFactory(
      $container
    );

    $categories = $categoryFactory->fromNamesArray(
      ['notExistedCategory'],
      CategoryFactory::CREATE_NOT_EXISTED
    );

    Assert::count($categories, 1);

    Assert::same(
      $categories[0]->name,
      $notExistedCategory->name
    );
  }

  /**
   * Testing `testCannotFromNamesArray` method exceptions.
   *
   * @throws \Exception
   */
  public function testCannotFromNamesArray()
  {
    $this->expectException(\InvalidArgumentException::class);

    $container = new Container([
      'categories' => []
    ]);

    $categoryFactory = new CategoryFactory(
      $container
    );

    $categoryFactory->fromNamesArray([1]);
  }
}