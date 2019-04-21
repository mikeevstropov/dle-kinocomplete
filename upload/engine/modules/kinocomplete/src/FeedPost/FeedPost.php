<?php

namespace Kinocomplete\FeedPost;

use Kinocomplete\Post\Post;

class FeedPost
{
  /**
   * @var string
   */
  public $id = '';

  /**
   * @var string
   */
  public $postId = null;

  /**
   * @var string
   */
  public $videoId = '';

  /**
   * @var string
   */
  public $videoOrigin = '';

  /**
   * @var Post
   */
  public $post = null;
}