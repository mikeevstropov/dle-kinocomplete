<?php

namespace Kinocomplete\Api\SystemApiTrait;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\Container\Container;
use Kinocomplete\FeedPost\FeedPost;
use Webmozart\Assert\Assert;
use Medoo\Medoo;

trait FeedPostTrait {

  /**
   * Get container.
   *
   * @return Container
   */
  abstract public function getContainer();

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

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $data = $feedPostFactory->toDatabaseArray($feedPost);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

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
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

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
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

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
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $data = $feedPostFactory->toDatabaseArray(
      $feedPost
    );

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

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
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

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
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

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
      $feedPostFactory = $this->getContainer()->get('feed_post_factory');

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
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

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
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

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
   * @param  array $join
   * @param  array|string $columns
   * @return int
   * @throws \Exception
   */
  public function countFeedPosts(
    array $where = [],
    array $join = [],
    $columns = '*'
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $feedPostsTable = $this->getContainer()->get('database_feed_posts_table');

    if ($join) {

      $count = $database->count(
        $feedPostsTable,
        $join,
        $columns,
        $where
      );

    } else {

      $count = $database->count(
        $feedPostsTable,
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете новостей фида произошла ошибка: '. $error[2]
      );

    return $count;
  }
}
