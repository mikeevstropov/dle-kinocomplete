<?php

namespace Kinocomplete\Api;

use Kinocomplete\ExtraField\ExtraFieldFactory;
use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\PathUtil\Path;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;
use Medoo\Medoo;

class SystemApi extends BaseSystemApi
{
  /**
   * Get table engine.
   *
   * @param  string $name
   * @return mixed
   * @throws \Exception
   */
  public function getTableEngine($name)
  {
    Assert::stringNotEmpty(
      $name,
      'Имя таблицы должно быть не пустой строкой.'
    );

    /** @var Medoo $database */
    $database = $this->container->get('database');

    $prefix = $this->container->get('database_prefix');
    $prefixedName = $prefix . $name;

    $query = "SHOW TABLE STATUS WHERE `Name` = '$prefixedName';";
    $result = $database->query($query)->fetchAll();

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При получении подсистемы таблицы "%s" произошла ошибка: %s2',
        $prefixedName,
        $error[2]
      ));

    Assert::count($result, 1, sprintf(
      'Невозможно найти таблицу "%s".',
      $prefixedName
    ));

    return $result[0]['Engine'];
  }

  /**
   * Remove file of system cache by name.
   *
   * @param  $name
   * @param  string $extension
   * @return bool
   * @throws \Exception
   */
  public function clearSystemCache(
    $name,
    $extension = 'php'
  ) {
    Assert::stringNotEmpty($name);

    $systemCacheDir = $this->container->get('system_cache_dir');

    $systemFolder = Path::join(
      $systemCacheDir,
      'system'
    );

    if (!file_exists($systemFolder))
      throw new \Exception(
        'Директория временных файлов системы отсутствует.'
      );

    if (!is_writable($systemFolder))
      throw new \Exception(
        'Директория временных файлов системы недоступна для записи.'
      );

    $fileName = $name;
    $fileName .= $extension
      ? '.'. $extension
      : '';

    $cacheFile = Path::join(
      $systemFolder,
      $fileName
    );

    if (file_exists($cacheFile)) {

      if (!unlink($cacheFile))
        throw new \Exception(sprintf(
          'Не удалось удалить системный кэш "%s".',
          $name
        ));

      return true;
    }

    return false;
  }

  /**
   * Checking video in persisted posts.
   *
   * @param  Video $video
   * @return bool
   * @throws \Exception
   */
  public function isVideoInPosts(
    Video $video
  ) {
    $videoFields = ContainerFactory::fromNamespace(
      $this->container,
      'video_field',
      true
    );

    $postAccessoryVideoFields = $this->container->get(
      'post_accessory_video_fields'
    );

    $filteredVideoFields = ContainerFactory::filterByKeys(
      $videoFields,
      $postAccessoryVideoFields,
      true
    );

    // Field "id".
    if (
      $video->id &&
      array_key_exists('id', $filteredVideoFields)
    ) {

      $has = $this->hasFeedPosts([
        'videoId' => $video->id
      ]);

      if ($has) return $has;
    }

    // Field "title".
    if (
      $video->title &&
      array_key_exists('title', $filteredVideoFields)
    ) {
      $has = $this->hasPosts([
        'title' => $video->title
      ]);

      if ($has) return $has;
    }

    // Extra fields.
    $conditions = [];

    /** @var ExtraFieldFactory $extraFieldFactory */
    $extraFieldFactory = $this->container->get('extra_field_factory');

    foreach ($filteredVideoFields as $videoField => $extraField) {

      if (
        !$extraField ||
        $videoField === 'id' ||
        $videoField === 'title'
      ) continue;

      $instanceField = Utils::snakeToCamel($videoField);

      $extraFieldInstance = new ExtraField();
      $extraFieldInstance->name = $extraField;
      $extraFieldInstance->value = $video->$instanceField;

      if (!$extraFieldInstance->value)
        continue;

      try {

        $conditions[] = $extraFieldFactory->toValue(
          $extraFieldInstance
        );

      } catch (\Exception $e) {}
    }

    if ($conditions) {

      return $this->hasPosts([
        'xfields[~]' => ['OR' => $conditions]
      ]);
    }

    return false;
  }

  /**
   * Get posts by video.
   *
   * @param  Video $video
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getPostsByVideo(
    Video $video,
    $raw = false
  ) {
    $postFactory = $this->container->get('post_factory');
    $feedPostsTable = $this->container->get('database_feed_posts_table');

    $result = [];

    $videoFields = ContainerFactory::fromNamespace(
      $this->container,
      'video_field',
      true
    );

    $postAccessoryVideoFields = $this->container->get(
      'post_accessory_video_fields'
    );

    $filteredVideoFields = ContainerFactory::filterByKeys(
      $videoFields,
      $postAccessoryVideoFields,
      true
    );

    // Field "id".
    if (
      $video->id &&
      array_key_exists('id', $filteredVideoFields)
    ) {
      $post = new Post();
      $postArray = $postFactory->toDatabaseArray($post);

      $postFields = array_keys($postArray);
      $prefixedPostFields = ['post.id'];

      foreach ($postFields as $key) {
        $prefixedPostFields[] = 'post.'. $key;
      }

      $videoIdField = $feedPostsTable .'.videoId';
      $joinKey = '[><]'. $feedPostsTable;

      $posts = $this->getPosts(
        [$videoIdField => $video->id],
        [$joinKey => ['id' => 'postId']],
        $prefixedPostFields,
        $raw
      );

      $result = array_merge(
        $result,
        $posts
      );
    }

    // Field "title".
    if (
      $video->title &&
      array_key_exists('title', $filteredVideoFields)
    ) {
      $posts = $this->getPosts(
        ['title' => $video->title],
        [],
        '*',
        $raw
      );

      $result = array_merge(
        $result,
        $posts
      );
    }

    // Extra fields.
    $conditions = [];

    /** @var ExtraFieldFactory $extraFieldFactory */
    $extraFieldFactory = $this->container->get('extra_field_factory');

    foreach ($filteredVideoFields as $videoField => $extraField) {

      if (
        !$extraField ||
        $videoField === 'id' ||
        $videoField === 'title'
      ) continue;

      $instanceField = Utils::snakeToCamel($videoField);

      $extraFieldInstance = new ExtraField();
      $extraFieldInstance->name = $extraField;
      $extraFieldInstance->value = $video->$instanceField;

      if (!$extraFieldInstance)
        continue;

      try {

        $conditions[] = $extraFieldFactory->toValue(
          $extraFieldInstance
        );

      } catch (\Exception $e) {}
    }

    if ($conditions) {

      $posts = $this->getPosts(
        ['xfields[~]' => ['OR' => $conditions]],
        [],
        '*',
        $raw
      );

      $result = array_merge(
        $result,
        $posts
      );
    }

    $result = array_unique(
      $result,
      SORT_REGULAR
    );

    $result = array_values(
      $result
    );

    return $result;
  }

  /**
   * Remove feed posts and related posts.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeFeedPostsAndRelatedPosts(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $prefix = $this->container->get('database_prefix');

    $count                = $this->countFeedPosts($where);
    $feedPostsTable       = $this->container->get('database_feed_posts_table');
    $feedPostsTable       = $prefix . $feedPostsTable;
    $postsTable           = $prefix .'post';
    $postsExtrasTable     = $prefix .'post_extras';
    $postsCategoriesTable = $prefix .'post_extras_cats';
    $whereClause          = $where ? ' WHERE' : '';

    foreach ($where as $key => $value) {

      $prefixedKey = $feedPostsTable .'.'. $key;
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
          `{$feedPostsTable}`,
          `{$postsTable}`,
          `{$postsExtrasTable}`,
          `{$postsCategoriesTable}`
        FROM `{$feedPostsTable}`
        INNER JOIN `{$postsTable}`
          ON {$feedPostsTable}.postId = {$postsTable}.id
        LEFT JOIN `{$postsExtrasTable}`
          ON {$feedPostsTable}.postId = {$postsExtrasTable}.news_id
        LEFT JOIN `{$postsCategoriesTable}`
          ON {$feedPostsTable}.postId = {$postsCategoriesTable}.news_id
      ". $whereClause;

    // Without categories relations. (< 13.2)
    } else {

      $sql = "
        DELETE
          `{$feedPostsTable}`,
          `{$postsTable}`,
          `{$postsExtrasTable}`
        FROM `{$feedPostsTable}`
        INNER JOIN `{$postsTable}`
          ON {$feedPostsTable}.postId = {$postsTable}.id
        LEFT JOIN `{$postsExtrasTable}`
          ON {$feedPostsTable}.postId = {$postsExtrasTable}.news_id
      ". $whereClause;
    }

    $database->exec($sql);

    $error = $database->error();

    if ($error[1])
      throw new \Exception(sprintf(
        'При удалении новостей фидов и связанных новостей произошла ошибка: %s',
        $error[2]
      ));

    return $count;
  }
}
