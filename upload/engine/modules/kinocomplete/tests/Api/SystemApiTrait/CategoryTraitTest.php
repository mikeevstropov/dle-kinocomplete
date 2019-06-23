<?php

namespace Kinocomplete\Test\Api\SystemApiTrait;

use Kinocomplete\Api\SystemApiTrait\CategoryTrait;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\Container\Container;
use Kinocomplete\Category\Category;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Medoo\Medoo;

class CategoryTraitTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var SystemApi
   */
  public $instance;

  /**
   * @var Medoo
   */
  public $database;

  /**
   * SystemApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $this->database = $this->getContainer()->get('database');

    $this->instance = $this->getMockBuilder(
      CategoryTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $this->instance
      ->method('getContainer')
      ->willReturn($this->getContainer());
  }

  /**
   * Testing `addCategory` method.
   *
   * @return Category
   * @throws \Exception
   */
  public function testCanAddCategory()
  {
    $name = 'Category';

    $category = new Category();
    $category->name = $name;

    $addedCategory = $this->instance->addCategory($category);

    Assert::isInstanceOf(
      $addedCategory,
      Category::class
    );

    Assert::stringNotEmpty(
      $addedCategory->id
    );

    Assert::same(
      $addedCategory->name,
      $name
    );

    return $addedCategory;
  }

  /**
   * Testing `getCategory` method.
   *
   * @param   Category $category
   * @return  string
   * @throws  NotFoundException
   * @depends testCanAddCategory
   */
  public function testCanGetCategory(
    Category $category
  ) {
    $fetchedCategory = $this->instance->getCategory(
      $category->id
    );

    Assert::isInstanceOf(
      $fetchedCategory,
      Category::class
    );

    Assert::same(
      $category->id,
      $fetchedCategory->id
    );

    Assert::same(
      $category->name,
      $fetchedCategory->name
    );

    return $fetchedCategory;
  }

  /**
   * Testing `updateCategory` method.
   *
   * @param   Category $category
   * @return  Category
   * @throws  NotFoundException
   * @depends testCanAddCategory
   */
  public function testCanUpdateCategory(
    Category $category
  ) {
    $category->name = Utils::randomString();

    $this->instance->updateCategory(
      $category
    );

    $updatedCategory = $this->instance->getCategory(
      $category->id
    );

    Assert::same(
      $updatedCategory->name,
      $category->name
    );

    return $updatedCategory;
  }

  /**
   * Testing `getCategories` method.
   *
   * @param   Category $category
   * @return  mixed
   * @throws  \Exception
   * @depends testCanUpdateCategory
   */
  public function testCanGetCategories(
    Category $category
  ) {
    $fetchedCategories = $this->instance->getCategories([
      'id' => $category->id
    ]);

    Assert::notEmpty($fetchedCategories);

    $fetchedCategory = $fetchedCategories[0];

    Assert::isInstanceOf(
      $fetchedCategory,
      Category::class
    );

    Assert::same(
      $category->id,
      $fetchedCategory->id
    );

    Assert::same(
      $category->name,
      $fetchedCategory->name
    );

    return $fetchedCategory->id;
  }

  /**
   * Testing `getCategories` method with "join".
   *
   * @throws \Exception
   */
  public function testCanGetCategoriesWithJoin()
  {
    $table   = 'category';
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var CategoryFactory $factory */
    $factory = $this->getContainer()->get('category_factory');

    $expectedInstance = new Category();
    $expectedArray = $factory->toDatabaseArray($expectedInstance);

    $database
      ->expects($this->exactly(2))
      ->method('select')
      ->with(
        $table,
        $join,
        $columns,
        $where
      )->willReturn([$expectedArray]);

    $trait = $this->getMockBuilder(
      CategoryTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $trait
      ->method('getContainer')
      ->willReturn(new Container([
        'database' => $database,
        'category_factory' => $factory
      ]));

    /** @var CategoryTrait $trait */
    $instances = $trait->getCategories(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $trait->getCategories(
      $where,
      $join,
      $columns,
      true
    );

    Assert::count($arrays, 1);

    Assert::same(
      $arrays[0],
      $expectedArray
    );
  }

  /**
   * Testing `hasCategories` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanGetCategories
   */
  public function testCanHasCategories($id)
  {
    Assert::true(
      $this->instance->hasCategories([
        'id[~]' => $id
      ])
    );

    Assert::false(
      $this->instance->hasCategories([
        'id[~]' => '-20'
      ])
    );

    return $id;
  }

  /**
   * Testing `countCategories` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanHasCategories
   */
  public function testCanCountCategories($id)
  {
    $count = $this->instance->countCategories([
      'id' => $id
    ]);

    Assert::same($count, 1);

    return $id;
  }

  /**
   * Testing `countCategories` method with join.
   *
   * @throws \Exception
   */
  public function testCanCountCategoriesWithJoin()
  {
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $expected = 5;

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    $database
      ->expects($this->once())
      ->method('count')
      ->with(
        'category',
        $join,
        $columns,
        $where
      )->willReturn($expected);

    $trait = $this->getMockBuilder(
      CategoryTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $trait
      ->method('getContainer')
      ->willReturn(new Container([
        'database' => $database,
      ]));

    /** @var CategoryTrait $trait */
    $result = $trait->countCategories(
      $where,
      $join,
      $columns
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing `removeCategory` method.
   *
   * @param   string $id
   * @throws  NotFoundException
   * @depends testCanCountCategories
   */
  public function testCanRemoveCategory($id)
  {
    $isRemoved = $this->instance->removeCategory($id);

    Assert::true($isRemoved);
  }

  /**
   * Testing `removeCategories` method.
   *
   * @throws \Exception
   */
  public function testCanRemoveCategories()
  {
    $addedCategory = $this->instance->addCategory(
      new Category()
    );

    $count = $this->instance->removeCategories([
      'id' => $addedCategory->id
    ]);

    Assert::same($count, 1);
  }

  /**
   * Testing `getCategory` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotGetCategory()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getCategory('-20');
  }

  /**
   * Testing `updateCategory` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotUpdateCategory()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->updateCategory(new Category());
  }

  /**
   * Testing `removeCategory` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotRemoveCategory()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->removeCategory('-20');
  }
}
