<?php

namespace Kinocomplete\Test\FeedLoader;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\FeedLoader\PostCleaner;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Source\Source;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use Kinocomplete\Post\Post;

class PostCleanerTest extends TestCase
{
  use ContainerTrait;

  public function testCanClean()
  {
    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    /** @var SystemApi $systemApi */
    $systemApi = $this->getContainer()->get('system_api');

    /** @var FeedPostFactory $feedPostFactory */
    $feedPostFactory = $this->getContainer()->get('feed_post_factory');

    $post = new Post();
    $addedPost = $systemApi->addPost($post);
    $feedPost = $feedPostFactory->fromPost($addedPost);
    $feedPost->videoOrigin = $source->getVideoOrigin();
    $addedFeedPost = $systemApi->addFeedPost($feedPost);

    $feedPostsTable = $this->getContainer()
      ->get('database_feed_posts_table');

    $postCleaner = new PostCleaner(
      $source,
      $systemApi,
      $feedPostsTable
    );

    $expectedRemovedItems = 1;
    $removedItems = $postCleaner->clean();

    Assert::greaterThanEq(
      $removedItems,
      $expectedRemovedItems
    );

    $expectedExceptions = 2;
    $exceptions = 0;

    try {
      $systemApi->getPost($addedPost->id);
    } catch (\Exception $e) {
      ++$exceptions;
    }

    try {
      $systemApi->getFeedPost($addedFeedPost->id);
    } catch (\Exception $e) {
      ++$exceptions;
    }

    Assert::same(
      $exceptions,
      $expectedExceptions
    );
  }
}