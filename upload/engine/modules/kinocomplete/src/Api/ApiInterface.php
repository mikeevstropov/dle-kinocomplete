<?php

namespace Kinocomplete\Api;

use GuzzleHttp\Psr7\Request;
use Kinocomplete\Feed\Feed;
use Kinocomplete\Video\Video;

interface ApiInterface
{
  /**
   * Access checking
   *
   * @return boolean
   */
  public function accessChecking();

  /**
   * Get videos
   *
   * @param  string
   * @return array
   */
  public function getVideos($title);

  /**
   * Get video
   *
   * @param  $id
   * @return Video
   */
  public function getVideo($id);

  /**
   * Download feed.
   *
   * @param  Feed $feed
   * @param  string $filePath
   * @param  callable $onProgress
   * @return string Returns filePath
   */
  public function downloadFeed(
    Feed $feed,
    $filePath,
    callable $onProgress
  );
}