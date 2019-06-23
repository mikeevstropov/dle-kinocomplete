<?php

namespace Kinocomplete\Api\SystemApiTrait;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\Container\Container;
use Kinocomplete\Category\Category;
use Webmozart\Assert\Assert;
use Medoo\Medoo;

trait CategoryTrait {

  /**
   * Get container.
   *
   * @return Container
   */
  abstract public function getContainer();

  /**
   * Add category.
   *
   * @param  Category $category
   * @return Category
   * @throws \Exception
   */
  public function addCategory(
    Category $category
  ) {
    Assert::notEmpty($category);

    /** @var CategoryFactory $categoryFactory */
    $categoryFactory = $this->getContainer()->get('category_factory');

    $data = $categoryFactory->toDatabaseArray(
      $category
    );

    $versionId = $this->getContainer()
      ->get('system')
      ->get('version_id');

    // Remove specific field which
    // not existed in old versions.
    if (
      $versionId <= '11.0' &&
      array_key_exists('disable_search', $data)
    ) unset($data['disable_search']);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $database->insert(
      'category',
      $data
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При добавлении категории произошла ошибка: '. $error[2]
      );

    $category->id = $database->id();

    return $category;
  }

  /**
   * Get category.
   *
   * @param  string $id
   * @return Category
   * @throws NotFoundException
   */
  public function getCategory($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $items = $database->select(
      'category',
      '*',
      ['id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе категории произошла ошибка: '. $error[2]
      );

    $item = current($items);

    if (!$item)
      throw new NotFoundException(
        'Не удалось найти категорию.'
      );

    /** @var CategoryFactory $categoryFactory */
    $categoryFactory = $this->getContainer()->get('category_factory');

    return $categoryFactory->fromDatabaseArray($item);
  }

  /**
   * Update category.
   *
   * @param  Category $category
   * @return Category
   * @throws NotFoundException
   */
  public function updateCategory(
    Category $category
  ) {
    Assert::stringNotEmpty(
      $category->id,
      'Идентификатор обновляемой категории не найден.'
    );

    /** @var CategoryFactory $categoryFactory */
    $categoryFactory = $this->getContainer()->get('category_factory');

    $data = $categoryFactory->toDatabaseArray(
      $category
    );

    $versionId = $this->getContainer()
      ->get('system')
      ->get('version_id');

    // Remove specific field which
    // not existed in old versions.
    if (
      $versionId <= '11.0' &&
      array_key_exists('disable_search', $data)
    ) unset($data['disable_search']);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->update(
      'category',
      $data,
      ['id' => $category->id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При обновлении категории произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти категорию для обновления.'
      );

    return $category;
  }

  /**
   * Remove category.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeCategory($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->delete(
      'category',
      ['id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении категории произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти категорию для удаления.'
      );

    return true;
  }

  /**
   * Get categories.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getCategories(
    array $where = [],
    array $join = [],
    $columns = '*',
    $raw = false
  ) {
    if (!is_array($columns) && !is_string($columns))
      throw new \InvalidArgumentException(sprintf(
        'Argument 2 must be an array or string, got %s.',
        $columns
      ));

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    if ($join) {

      $items = $database->select(
        'category',
        $join,
        $columns,
        $where
      );

    } else {

      $items = $database->select(
        'category',
        $columns,
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе категорий произошла ошибка: '. $error[2]
      );

    if (!$raw) {

      /** @var CategoryFactory $categoryFactory */
      $categoryFactory = $this->getContainer()->get('category_factory');

      return array_map(
        [$categoryFactory, 'fromDatabaseArray'],
        $items
      );

    } else {

      return $items;
    }
  }

  /**
   * Has categories.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasCategories(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $has = $database->has(
      'category',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При проверке наличия категорий произошла ошибка: '. $error[2]
      );

    return $has;
  }

  /**
   * Remove categories.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeCategories(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->delete(
      'category',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении категорий произошла ошибка: '. $error[2]
      );

    return $statement->rowCount();
  }

  /**
   * Count categories.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @return int
   * @throws \Exception
   */
  public function countCategories(
    array $where = [],
    array $join = [],
    $columns = '*'
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    if ($join) {

      $count = $database->count(
        'category',
        $join,
        $columns,
        $where
      );

    } else {

      $count = $database->count(
        'category',
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете категорий произошла ошибка: '. $error[2]
      );

    return $count;
  }
}
