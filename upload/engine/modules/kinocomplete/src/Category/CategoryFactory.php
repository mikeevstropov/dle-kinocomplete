<?php

namespace Kinocomplete\Category;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Service\ServiceFactory;
use Webmozart\Assert\Assert;
use Cocur\Slugify\Slugify;

class CategoryFactory extends DefaultService
{
  const USE_ONLY_EXISTED = 1;
  const CREATE_NOT_EXISTED = 2;

  /**
   * Create instance from an array
   * received from Database response.
   *
   * @param  array $array
   * @return Category
   */
  public function fromDatabaseArray($array)
  {
    $category = new Category();

    // Field "id".
    if (array_key_exists('id', $array)) {

      if (
        $array['id'] &&
        is_string($array['id'])
      ) $category->id = $array['id'];
    }

    // Field "parentId".
    if (array_key_exists('parentId', $array)) {

      if (
        $array['parentId'] &&
        is_string($array['parentId'])
      ) $category->parentId = $array['parentId'];
    }

    // Field "slug".
    if (array_key_exists('alt_name', $array)) {

      if (is_string($array['alt_name']))
        $category->slug = $array['alt_name'];
    }

    // Field "name".
    if (array_key_exists('name', $array)) {

      if (is_string($array['name']))
        $category->name = $array['name'];
    }

    // Field "position".
    if (array_key_exists('posi', $array)) {

      if (ctype_digit($array['posi']))
        $category->position = (int) $array['posi'];
    }

    // Field "rssAllowed".
    if (array_key_exists('allow_rss', $array)) {

      if ($array['allow_rss'] !== null)
        $category->rssAllowed = (bool) $array['allow_rss'];
    }

    // Field "searchAllowed".
    if (array_key_exists('disable_search', $array)) {

      if ($array['disable_search'] !== null)
        $category->searchAllowed = !$array['disable_search'];
    }

    return $category;
  }

  /**
   * Convert instance to Database array.
   *
   * @param Category $category
   * @return array
   */
  public function toDatabaseArray(
    Category $category
  ) {
    $array = [
      'alt_name'       => $category->slug,
      'name'           => $category->name,
      'posi'           => $category->position,
      'allow_rss'      => (int) $category->rssAllowed,
      'disable_search' => (int) !$category->searchAllowed
    ];

    if ($category->id)
      $array['id'] = $category->id;

    if ($category->parentId)
      $array['parentId'] = $category->parentId;

    return $array;
  }

  /**
   * Create instances from database string
   * by following string pattern "1,2,3,4".
   *
   * @param $string
   * @return array
   * @throws NotFoundException
   */
  public function fromDatabaseString($string)
  {
    Assert::string($string);

    $categories = $this->container->get('categories');

    foreach ($categories as $category) {

      Assert::isInstanceOf(
        $category,
        Category::class
      );
    }

    $ids = explode(',', $string);
    $ids = array_map('trim', $ids);

    $result = [];

    foreach ($ids as $id) {

      $filter = function (Category $category) use ($id) {
        return $category->id === $id;
      };

      /** @var Category $category */
      $category = current(
        array_filter(
          $categories,
          $filter
        )
      );

      if (!$category)
        throw new NotFoundException(
          'Не удалось создать экземпляр категории по идентификатору.'
        );

      $result[] = $category;
    }

    return $result;
  }

  /**
   * Create instances from an
   * array of Category names.
   *
   * @param  array $array
   * @param  int $mode
   * @return array
   * @throws \Exception
   */
  public function fromNamesArray(
    array $array,
    $mode = 0
  ) {
    $categories = $this->container->get('categories');

    // Checking first argument.
    foreach ($array as $name) {

      Assert::string(
        $name,
        'Имя категории не является строкой.'
      );
    }

    // Checking categories.
    foreach ($categories as $category) {

      Assert::isInstanceOf(
        $category,
        Category::class,
        'Категория не является экземпляром Category.'
      );
    }

    $result = [];

    $slugify = new Slugify();
    $slugify->activateRuleSet('russian');

    $categoriesUpdated = false;

    foreach ($array as $name) {

      $categoryFilter = function (Category $category) use ($name) {
        return mb_strtolower($category->name) === mb_strtolower($name);
      };

      $existed = current(
        array_filter(
          $categories,
          $categoryFilter
        )
      );

      if ($existed) {

        $result[] = $existed;

      } else if ($mode === self::USE_ONLY_EXISTED) {

        continue;

      } else if ($mode === self::CREATE_NOT_EXISTED) {

        /** @var SystemApi $systemApi */
        $systemApi = $this->container->get('system_api');

        $category = new Category();
        $category->name = $name;
        $category->slug = $slugify->slugify($name);

        $category = $systemApi->addCategory($category);
        $result[] = $category;

        $categoriesUpdated = true;

      } else {

        $category = new Category();
        $category->name = $name;
        $category->slug = $slugify->slugify($name);

        $result[] = $category;
      }
    }

    // Categories must be updated.
    if (
      $categoriesUpdated &&
      $this->container->has('categories')
    ) {
      unset($this->container['categories']);
      $this->container['categories'] = ServiceFactory::getCategories();
    }

    return $result;
  }
}