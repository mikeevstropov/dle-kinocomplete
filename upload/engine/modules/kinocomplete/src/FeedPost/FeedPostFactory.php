<?php

namespace Kinocomplete\FeedPost;

use Kinocomplete\Post\PostFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Video\Video;
use Kinocomplete\Post\Post;

class FeedPostFactory extends DefaultService
{
  /**
   * Create instance from an array
   * received from Database response.
   *
   * @param  array $array
   * @return FeedPost
   * @throws \Exception
   */
  public function fromDatabaseArray(
    array $array
  ) {
    $feedPost = new FeedPost();

    $feedPost->id          = $array['id'];
    $feedPost->postId      = $array['postId'];
    $feedPost->videoId     = $array['videoId'];
    $feedPost->videoOrigin = $array['videoOrigin'];

    if (array_key_exists('post', $array) && $array['post']) {

      /** @var PostFactory $postFactory */
      $postFactory = $this->container->get('post_factory');

      $feedPost->post = $postFactory->fromDatabaseArray(
        $array['post']
      );
    }

    return $feedPost;
  }

  /**
   * Convert instance to Database array.
   *
   * @param  FeedPost $feedPost
   * @return array
   */
  public function toDatabaseArray(
    FeedPost $feedPost
  ) {
    return [
      'id'          => $feedPost->id,
      'postId'      => $feedPost->postId,
      'videoId'     => $feedPost->videoId,
      'videoOrigin' => $feedPost->videoOrigin
    ];
  }

  /**
   * Create instance from Post.
   *
   * @param  Post $post
   * @return FeedPost
   */
  public function fromPost(
    Post $post
  ) {
    $feedPost = new FeedPost();

    $feedPost->postId = $post->id;

    return $feedPost;
  }

  /**
   * Create instance from Post and Video.
   *
   * @param  Post $post
   * @param  Video $video
   * @return FeedPost
   */
  public function fromPostAndVideo(
    Post $post,
    Video $video
  ) {
    $feedPost = new FeedPost();

    $feedPost->postId = $post->id;
    $feedPost->videoId = $video->id;
    $feedPost->videoOrigin = $video->origin;

    return $feedPost;
  }
}