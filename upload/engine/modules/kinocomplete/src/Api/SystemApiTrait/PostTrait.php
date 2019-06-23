<?php

namespace Kinocomplete\Api\SystemApiTrait;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Container\Container;
use Kinocomplete\Category\Category;
use Kinocomplete\Post\PostFactory;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;
use Medoo\Medoo;

trait PostTrait {

  /**
   * Get container.
   *
   * @return Container
   */
  abstract public function getContainer();

  /**
   * Add post.
   *
   * @param  Post $post
   * @return Post
   * @throws \Exception
   */
  public function addPost(
    Post $post
  ) {
    Assert::notEmpty($post);

    /** @var PostFactory $postFactory */
    $postFactory = $this->getContainer()->get('post_factory');

    $data = $postFactory->toDatabaseArray($post);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $database->insert(
      'post',
      $data
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При добавлении новости произошла ошибка: '. $error[2]
      );

    $post->id = $database->id();

    // Add related post extra.
    $database->insert(
      'post_extras',
      ['news_id' => $post->id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При добавлении экстра новости произошла ошибка: %s',
        $error[2]
      ));

    $versionId = $this->getContainer()
      ->get('system')
      ->get('version_id');

    // Add categories relations. (>= 13.2)
    if ($versionId >= '13.2' && $post->categories) {

      $categoriesRelationsMapper = function (
        Category $category
      ) use ($post) {

        return [
          'news_id' => $post->id,
          'cat_id' => $category->id
        ];
      };

      $categoriesRelations = array_map(
        $categoriesRelationsMapper,
        $post->categories
      );

      if (count($categoriesRelations)) {

        $database->insert(
          'post_extras_cats',
          $categoriesRelations
        );

        $error = $database->error();

        if ($error[1])
          throw new \Exception(sprintf(
            'При добавлении связей новости с категориями произошла ошибка: %s',
            $error[2]
          ));
      }
    }

    return $post;
  }

  /**
   * Get post.
   *
   * @param  $id
   * @return Post
   * @throws NotFoundException
   */
  public function getPost($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $items = $database->select(
      'post',
      '*',
      ['id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе новости произошла ошибка: '. $error[2]
      );

    $item = current($items);

    if (!$item)
      throw new NotFoundException(
        'Не удалось найти новость.'
      );

    /** @var PostFactory $postFactory */
    $postFactory = $this->getContainer()->get('post_factory');

    return $postFactory->fromDatabaseArray($item);
  }

  /**
   * Update post.
   *
   * @param  Post $post
   * @return Post
   * @throws NotFoundException
   */
  public function updatePost(
    Post $post
  ) {
    Assert::stringNotEmpty(
      $post->id,
      'Идентификатор обновляемой новости не найден.'
    );

    /** @var PostFactory $postFactory */
    $postFactory = $this->getContainer()->get('post_factory');

    $data = $postFactory->toDatabaseArray(
      $post
    );

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->update(
      'post',
      $data,
      ['id' => $post->id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При обновлении новости произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти новость для обновления.'
      );

    $versionId = $this->getContainer()
      ->get('system')
      ->get('version_id');

    // Update categories relations. (>= 13.2)
    if ($versionId >= '13.2') {

      $postsCategoriesTable = 'post_extras_cats';

      $categoriesRelationsMapper = function (
        Category $category
      ) use ($post) {

        return [
          'news_id' => $post->id,
          'cat_id' => $category->id
        ];
      };

      $categoriesRelations = array_map(
        $categoriesRelationsMapper,
        $post->categories
      );

      // Remove existed categories relations.
      $database->delete(
        $postsCategoriesTable,
        ['news_id' => $post->id]
      );

      $error = $database->error();

      if ($error[1])
        throw new \Exception(sprintf(
          'При удалении связей новости с категориями произошла ошибка: %s',
          $error[2]
        ));

      // Add new categories relations.
      if (count($categoriesRelations)) {

        $database->insert(
          $postsCategoriesTable,
          $categoriesRelations
        );

        $error = $database->error();

        if ($error[1])
          throw new \Exception(sprintf(
            'При добавлении связей новости с категориями произошла ошибка: %s',
            $error[2]
          ));
      }
    }

    return $post;
  }

  /**
   * Remove post.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removePost($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->delete(
      'post',
      ['id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении новости произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти новость для удаления.'
      );

    // Remove related feed post.
    $feedPostsTable = $this->getContainer()->get(
      'database_feed_posts_table'
    );

    $database->delete(
      $feedPostsTable,
      ['postId' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При удалении новости фида произошла ошибка: %s',
        $error[2]
      ));

    // Remove related post extra.
    $database->delete(
      'post_extras',
      ['news_id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При удалении экстра новости произошла ошибка: %s',
        $error[2]
      ));

    $versionId = $this->getContainer()
      ->get('system')
      ->get('version_id');

    // Remove categories relations. (>= 13.2)
    if ($versionId >= '13.2') {

      $database->delete(
        'post_extras_cats',
        ['news_id' => $id]
      );

      $error = $database->error();

      if ($error[1])
        throw new \Exception(sprintf(
          'При удалении связей новости с категориями произошла ошибка: %s',
          $error[2]
        ));
    }

    return true;
  }

  /**
   * Get posts.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getPosts(
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
        'post',
        $join,
        $columns,
        $where
      );

    } else {

      $items = $database->select(
        'post',
        $columns,
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе новостей произошла ошибка: '. $error[2]
      );

    if (!$raw) {

      /** @var PostFactory $postFactory */
      $postFactory = $this->getContainer()->get('post_factory');

      return array_map(
        [$postFactory, 'fromDatabaseArray'],
        $items
      );

    } else {

      return $items;
    }
  }

  /**
   * Has posts.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasPosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $has = $database->has(
      'post',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При проверке наличия новостей произошла ошибка: '. $error[2]
      );

    return $has;
  }

  /**
   * Remove posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removePosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $count                = $this->countPosts($where);
    $prefix               = $this->getContainer()->get('database_prefix');
    $feedPostsTable       = $this->getContainer()->get('database_feed_posts_table');
    $feedPostsTable       = $prefix . $feedPostsTable;
    $postsTable           = $prefix .'post';
    $postsExtrasTable     = $prefix .'post_extras';
    $postsCategoriesTable = $prefix .'post_extras_cats';
    $whereClause          = $where ? ' WHERE' : '';

    foreach ($where as $key => $value) {

      $prefixedKey = $postsTable .'.'. $key;
      $value = $database->quote($value);
      $whereClause .= ' '. $prefixedKey .' = '. $value;
    }

    $versionId = $this->getContainer()
      ->get('system')
      ->get('version_id');

    // With categories relations. (>= 13.2)
    if ($versionId >= '13.2') {

      $sql = "
        DELETE
          `{$postsTable}`,
          `{$postsExtrasTable}`,
          `{$postsCategoriesTable}`,
          `{$feedPostsTable}`
        FROM `{$postsTable}`
        LEFT JOIN `{$postsExtrasTable}`
          ON {$postsTable}.id = {$postsExtrasTable}.news_id
        LEFT JOIN `{$postsCategoriesTable}`
          ON {$postsTable}.id = {$postsCategoriesTable}.news_id
        LEFT JOIN `{$feedPostsTable}`
          ON {$postsTable}.id = {$feedPostsTable}.postId
      ". $whereClause;

      // Without categories relations. (< 13.2)
    } else {

      $sql = "
        DELETE
          `{$postsTable}`,
          `{$postsExtrasTable}`,
          `{$feedPostsTable}`
        FROM `{$postsTable}`
        LEFT JOIN `{$postsExtrasTable}`
          ON {$postsTable}.id = {$postsExtrasTable}.news_id
        LEFT JOIN `{$feedPostsTable}`
          ON {$postsTable}.id = {$feedPostsTable}.postId
      ". $whereClause;
    }

    $database->exec($sql);

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении новостей произошла ошибка: '. $error[2]
      );

    return $count;
  }

  /**
   * Count posts.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @return int
   * @throws \Exception
   */
  public function countPosts(
    array $where = [],
    array $join = [],
    $columns = '*'
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    if ($join) {

      $count = $database->count(
        'post',
        $join,
        $columns,
        $where
      );

    } else {

      $count = $database->count(
        'post',
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете новостей произошла ошибка: '. $error[2]
      );

    return $count;
  }

  /**
   * Add extra fields to search.
   *
   * @param  $postId
   * @param  array $extraFields
   * @return int
   * @throws \Exception
   */
  public function addExtraFieldsToSearch(
    $postId,
    array $extraFields
  ) {
    Assert::stringNotEmpty($postId);

    $filter = function (ExtraField $field) {
      return $field->linked;
    };

    $fields = array_filter(
      $extraFields,
      $filter
    );

    $array = [];

    foreach ($fields as $field) {

      $values = explode(',', $field->value);
      $values = array_map('trim', $values);

      foreach ($values as $value) {

        $array[] = [
          'news_id' => $postId,
          'tagname' => $field->name,
          'tagvalue' => $value,
        ];
      }
    }

    if (!$array) return 0;

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->insert(
      'xfsearch',
      $array
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При добавлении доп. полей-гиперссылок в поиск произошла ошибка: %s',
        $error[2]
      ));

    return $statement->rowCount();
  }

  /**
   * Remove extra fields from search.
   *
   * @param  string $postId
   * @return int
   * @throws \Exception
   */
  public function removeExtraFieldsFromSearch($postId)
  {
    Assert::stringNotEmpty($postId);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->delete(
      'xfsearch',
      ['news_id' => $postId]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При удалении доп. полей-гиперссылок из поиска произошла ошибка: %s',
        $error[2]
      ));

    return $statement->rowCount();
  }
}
