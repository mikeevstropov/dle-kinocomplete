<?php

namespace Kinocomplete\Api;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Category\Category;
use Kinocomplete\FeedPost\FeedPost;
use Kinocomplete\Post\PostFactory;
use Kinocomplete\User\User;
use Kinocomplete\User\UserFactory;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;
use Medoo\Medoo;

class BaseSystemApi extends DefaultService implements BaseSystemApiInterface
{
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
    $categoryFactory = $this->container->get('category_factory');

    $data = $categoryFactory->toDatabaseArray(
      $category
    );

    $versionId = $this->container
      ->get('system')
      ->get('version_id');

    // Remove specific field which
    // not existed in old versions.
    if (
      $versionId <= '11.0' &&
      array_key_exists('disable_search', $data)
    ) unset($data['disable_search']);

    /** @var Medoo $database */
    $database = $this->container->get('database');

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
    $database = $this->container->get('database');

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
    $categoryFactory = $this->container->get('category_factory');

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
    $categoryFactory = $this->container->get('category_factory');

    $data = $categoryFactory->toDatabaseArray(
      $category
    );

    $versionId = $this->container
      ->get('system')
      ->get('version_id');

    // Remove specific field which
    // not existed in old versions.
    if (
      $versionId <= '11.0' &&
      array_key_exists('disable_search', $data)
    ) unset($data['disable_search']);

    /** @var Medoo $database */
    $database = $this->container->get('database');

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
    $database = $this->container->get('database');

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
    $database = $this->container->get('database');

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
      $categoryFactory = $this->container->get('category_factory');

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
    $database = $this->container->get('database');

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
    $database = $this->container->get('database');

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
   * @return int
   * @throws \Exception
   */
  public function countCategories(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $count = $database->count(
      'category',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете категорий произошла ошибка: '. $error[2]
      );

    return $count;
  }

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
    $postFactory = $this->container->get('post_factory');

    $data = $postFactory->toDatabaseArray($post);

    /** @var Medoo $database */
    $database = $this->container->get('database');

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

    $versionId = $this->container
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
    $database = $this->container->get('database');

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
    $postFactory = $this->container->get('post_factory');

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
    $postFactory = $this->container->get('post_factory');

    $data = $postFactory->toDatabaseArray(
      $post
    );

    /** @var Medoo $database */
    $database = $this->container->get('database');

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

    $versionId = $this->container
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
    $database = $this->container->get('database');

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
    $feedPostsTable = $this->container->get(
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

    $versionId = $this->container
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
    $database = $this->container->get('database');

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
      $postFactory = $this->container->get('post_factory');

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
    $database = $this->container->get('database');

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
    $database = $this->container->get('database');

    $count                = $this->countPosts($where);
    $prefix               = $this->container->get('database_prefix');
    $feedPostsTable       = $this->container->get('database_feed_posts_table');
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

    $versionId = $this->container
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
   * @return int
   * @throws \Exception
   */
  public function countPosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $count = $database->count(
      'post',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете новостей произошла ошибка: '. $error[2]
      );

    return $count;
  }

  /**
   * Add feed post.
   *
   * @param  FeedPost $feedPost
   * @return FeedPost
   * @throws \Exception
   */
  public function addFeedPost(
    FeedPost $feedPost
  ) {
    Assert::notEmpty($feedPost);

    $feedPostsTable = $this->container->get('database_feed_posts_table');
    $feedPostFactory = $this->container->get('feed_post_factory');

    $data = $feedPostFactory->toDatabaseArray($feedPost);

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $database->insert(
      $feedPostsTable,
      $data
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При добавлении новости фида произошла ошибка: '. $error[2]
      );

    $feedPost->id = $database->id();

    return $feedPost;
  }

  /**
   * Get feed post.
   *
   * @param  $id
   * @return FeedPost
   * @throws NotFoundException
   */
  public function getFeedPost($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $items = $database->select(
      $feedPostsTable,
      '*',
      ['id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе новости фида произошла ошибка: '. $error[2]
      );

    $item = current($items);

    if (!$item)
      throw new NotFoundException(
        'Не удалось найти новость фида.'
      );

    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->container->get('feed_post_factory');

    return $feedPostFactory->fromDatabaseArray($item);
  }

  /**
   * Update feed post.
   *
   * @param  FeedPost $feedPost
   * @return FeedPost
   * @throws NotFoundException
   */
  public function updateFeedPost(
    FeedPost $feedPost
  ) {
    Assert::stringNotEmpty(
      $feedPost->id,
      'Идентификатор обновляемой новости фида не найден.'
    );

    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->container->get('feed_post_factory');

    $data = $feedPostFactory->toDatabaseArray(
      $feedPost
    );

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $statement = $database->update(
      $feedPostsTable,
      $data,
      ['id' => $feedPost->id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При обновлении новости фида произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти новость фида для обновления.'
      );

    return $feedPost;
  }

  /**
   * Remove feed post.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeFeedPost($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $statement = $database->delete(
      $feedPostsTable,
      ['id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении новости фида произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти новость фида для удаления.'
      );

    return true;
  }

  /**
   * Get feed posts.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getFeedPosts(
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
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    if ($join) {

      $items = $database->select(
        $feedPostsTable,
        $join,
        $columns,
        $where
      );

    } else {

      $items = $database->select(
        $feedPostsTable,
        $columns,
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе новостей фида произошла ошибка: '. $error[2]
      );

    if (!$raw) {

      /** @var FeedPostFactory $feedPostFactory */
      $feedPostFactory = $this->container->get('feed_post_factory');

      return array_map(
        [$feedPostFactory, 'fromDatabaseArray'],
        $items
      );

    } else {

      return $items;
    }
  }

  /**
   * Has feed posts.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasFeedPosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $has = $database->has(
      $feedPostsTable,
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При проверке наличия новостей фида произошла ошибка: '. $error[2]
      );

    return $has;
  }

  /**
   * Remove feed posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeFeedPosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $statement = $database->delete(
      $feedPostsTable,
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении новостей фидов произошла ошибка: '. $error[2]
      );

    return $statement->rowCount();
  }

  /**
   * Count feed posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function countFeedPosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $count = $database->count(
      $feedPostsTable,
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете новостей фида произошла ошибка: '. $error[2]
      );

    return $count;
  }

  /**
   * Add user.
   *
   * @param  User $user
   * @return User
   * @throws \Exception
   */
  public function addUser(
    User $user
  ) {
    Assert::notEmpty($user);

    /** @var UserFactory $userFactory */
    $userFactory = $this->container->get('user_factory');

    $data = $userFactory->toDatabaseArray(
      $user
    );

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $database->insert(
      'users',
      $data
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При добавлении пользователя произошла ошибка: '. $error[2]
      );

    $user->id = $database->id();

    return $user;
  }

  /**
   * Get user.
   *
   * @param  string $id
   * @return User
   * @throws NotFoundException
   */
  public function getUser($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $items = $database->select(
      'users',
      '*',
      ['user_id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе пользователя произошла ошибка: '. $error[2]
      );

    $item = current($items);

    if (!$item)
      throw new NotFoundException(
        'Не удалось найти пользователя.'
      );

    /** @var UserFactory $userFactory */
    $userFactory = $this->container->get('user_factory');

    return $userFactory->fromDatabaseArray($item);
  }

  /**
   * Update user.
   *
   * @param  User $user
   * @return User
   * @throws NotFoundException
   */
  public function updateUser(
    User $user
  ) {
    Assert::stringNotEmpty(
      $user->id,
      'Идентификатор обновляемого пользователя не найден.'
    );

    /** @var UserFactory $userFactory */
    $userFactory = $this->container->get('user_factory');

    $data = $userFactory->toDatabaseArray(
      $user
    );

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $statement = $database->update(
      'users',
      $data,
      ['user_id' => $user->id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При обновлении пользователя произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти пользователя для обновления.'
      );

    return $user;
  }

  /**
   * Remove user.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeUser($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $statement = $database->delete(
      'users',
      ['user_id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении пользователя произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти пользователя для удаления.'
      );

    return true;
  }

  /**
   * Get users.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getUsers(
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
    $database = $this->container->get('database');

    if ($join) {

      $items = $database->select(
        'users',
        $join,
        $columns,
        $where
      );

    } else {

      $items = $database->select(
        'users',
        $columns,
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе пользователей произошла ошибка: '. $error[2]
      );

    if (!$raw) {

      /** @var UserFactory $userFactory */
      $userFactory = $this->container->get('user_factory');

      return array_map(
        [$userFactory, 'fromDatabaseArray'],
        $items
      );

    } else {

      return $items;
    }
  }

  /**
   * Has users.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasUsers(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $has = $database->has(
      'users',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При проверке наличия пользователей произошла ошибка: '. $error[2]
      );

    return $has;
  }

  /**
   * Remove users.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeUsers(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $statement = $database->delete(
      'users',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении пользователей произошла ошибка: '. $error[2]
      );

    return $statement->rowCount();
  }

  /**
   * Count users.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function countUsers(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $count = $database->count(
      'users',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете пользователей произошла ошибка: '. $error[2]
      );

    return $count;
  }
}
