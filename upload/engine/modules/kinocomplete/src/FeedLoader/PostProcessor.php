<?php

namespace Kinocomplete\FeedLoader;

use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\Service\DefaultService;
use Psr\Container\ContainerInterface;
use Kinocomplete\Post\PostFactory;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Source\Source;
use Kinocomplete\Video\Video;
use Kinocomplete\Post\Post;

class PostProcessor extends DefaultService
{
  /**
   * Source instance.
   *
   * @var Source
   */
  protected $source;

  /**
   * PostProcessor constructor.
   *
   * @param ContainerInterface $container
   * @param Source $source
   */
  public function __construct(
    ContainerInterface $container,
    Source $source
  ) {
    parent::__construct($container);

    $this->source = $source;
  }

  /**
   * Checking video array in persisted posts.
   *
   * @param  array $array
   * @return bool
   * @throws \Exception
   */
  public function isVideoArrayInPosts(
    array $array
  ) {
    $videoFactory = $this->source->getVideoFactory();
    $video = $videoFactory($array);

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    return $systemApi->isVideoInPosts(
      $video
    );
  }

  /**
   * Add post from video array.
   *
   * @param  array $array
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function addPostFromVideoArray(
    array $array
  ) {
    $videoFactory = $this->source->getVideoFactory();
    $video = $videoFactory($array);

    // Download images.
    $downloadImages = $this->container->get('images_auto_download');

    if ($downloadImages) {

      $fileDownloader = $this->container->get('file_downloader');
      $fileDownloader->downloadVideoImages($video);
    }

    // Download torrents.
    $downloadTorrents = $this->container->get('torrents_auto_download');

    if ($downloadTorrents) {

      $fileDownloader = $this->container->get('file_downloader');
      $fileDownloader->downloadVideoTorrents($video);
    }

    /** @var PostFactory $postFactory */
    $postFactory = $this->container->get('post_factory');

    $post = $postFactory->fromVideo(
      $video,
      CategoryFactory::CREATE_NOT_EXISTED
    );

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    $systemApi->addPost($post);

    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->container->get('feed_post_factory');

    $feedPost = $feedPostFactory->fromPostAndVideo(
      $post,
      $video
    );

    $systemApi->addFeedPost($feedPost);
  }

  /**
   * Get posts by video array.
   *
   * @param  array $array
   * @return array
   * @throws \Exception
   */
  public function getPostsByVideoArray(
    array $array
  ) {
    $videoFactory = $this->source->getVideoFactory();
    $video = $videoFactory($array);

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    return $systemApi->getPostsByVideo(
      $video
    );
  }

  /**
   * Update post by video array.
   *
   * @param  Post $post
   * @param  array $array
   * @return bool
   * @throws \Exception
   */
  public function updatePostByVideoArray(
    Post $post,
    array $array
  ) {
    $post = clone $post;

    $updated = false;

    $systemApi              = $this->container->get('system_api');
    $postFactory            = $this->container->get('post_factory');
    $videoFactory           = $this->source->getVideoFactory();
    $postUpdaterNewDate     = (bool) $this->container->get('post_updater_new_date');
    $postUpdaterVideoFields = $this->container->get('post_updater_video_fields');

    /** @var Video $video */
    $video = $videoFactory($array);

    // Download images.
    $downloadImages = $this->container->get('images_auto_download');

    if ($downloadImages) {

      $fileDownloader = $this->container->get('file_downloader');
      $fileDownloader->downloadVideoImages($video);
    }

    // Download torrents.
    $downloadTorrents = $this->container->get('torrents_auto_download');

    if ($downloadTorrents) {

      $fileDownloader = $this->container->get('file_downloader');
      $fileDownloader->downloadVideoTorrents($video);
    }

    /** @var Post $updatedPost */
    $updatedPost = $postFactory->fromVideo($video);
    $updatedPost->id = $post->id;

    $videoFields = ContainerFactory::fromNamespace(
      $this->container,
      'video_field',
      true
    );

    $filteredVideoFields = ContainerFactory::filterByKeys(
      $videoFields,
      $postUpdaterVideoFields,
      true
    );

    if ($filteredVideoFields)
      $videoFields = $filteredVideoFields;

    // Field "title".
    if (array_key_exists('title', $videoFields)) {

      if ($post->title !== $updatedPost->title) {

        $post->title = $updatedPost->title;
        $updated = true;
      }
    }

    // Field "description".
    if (array_key_exists('description', $videoFields)) {

      if ($post->shortStory !== $updatedPost->shortStory) {

        $post->shortStory = $updatedPost->shortStory;
        $updated = true;
      }

      if ($post->fullStory !== $updatedPost->fullStory) {

        $post->fullStory = $updatedPost->fullStory;
        $updated = true;
      }
    }

    // Extra fields.
    foreach ($videoFields as $videoField => $extraField) {

      if (!$extraField)
        continue;

      $field = $post->getExtraField($extraField);
      $fieldExist = $field !== false;

      $updatedField = $updatedPost->getExtraField($extraField);
      $updatedFieldExist = $updatedField !== false;

      if (!$fieldExist && !$updatedFieldExist) {

        continue;

      } if ($fieldExist && !$updatedFieldExist) {

        /*
        $post->removeExtraField($extraField);
        $updated = true;
        */

      } else if (!$fieldExist && $updatedFieldExist) {

        $post->extraFields[] = $updatedField;
        $updated = true;

      } else if ((string) $field->value !== (string) $updatedField->value) {

        $post->setExtraFieldValue(
          $updatedField->name,
          $updatedField->value
        );

        $updated = true;
      }
    }

    // Update.
    if ($updated) {

      if ($postUpdaterNewDate)
        $post->date = $updatedPost->date;

      $systemApi->updatePost($post);
    }

    return $updated;
  }
}
