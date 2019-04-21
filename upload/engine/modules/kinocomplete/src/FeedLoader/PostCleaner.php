<?php

namespace Kinocomplete\FeedLoader;

use Kinocomplete\Api\SystemApi;
use Kinocomplete\Source\Source;

class PostCleaner
{
  /**
   * Source instance.
   *
   * @var Source
   */
  protected $source;

  /**
   * SystemApi instance.
   *
   * @var SystemApi
   */
  protected $systemApi;

  /**
   * PostCleaner constructor.
   *
   * @param Source $source
   * @param SystemApi $systemApi
   */
  public function __construct(
    Source $source,
    SystemApi $systemApi
  ) {
    $this->source = $source;
    $this->systemApi = $systemApi;
  }

  /**
   * Remove all posts created by feeds.
   *
   * @return float|int
   * @throws \Exception
   */
  public function clean()
  {
    return $this->systemApi->removeFeedPostsAndRelatedPosts([
      'videoOrigin' => $this->source->getVideoOrigin()
    ]);
  }
}
