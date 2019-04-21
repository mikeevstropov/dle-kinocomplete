<?php

namespace Kinocomplete\Test\FeedLoader;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\FeedLoader\FeedProcessor;
use Kinocomplete\FeedLoader\PostProcessor;
use Kinocomplete\FeedLoader\StatusSender;
use Kinocomplete\FeedLoader\PostCreator;
use Kinocomplete\FeedLoader\FileParser;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;
use Kinocomplete\Feed\Feeds;

class PostCreatorTest extends TestCase
{
  use ContainerTrait;

  public function testCanLoad()
  {
    $source         = $this->getContainer()->get('moonwalk_source');
    $systemApi      = $this->getContainer()->get('system_api');
    $systemCacheDir = $this->getContainer()->get('system_cache_dir');

    $postProcessor = new PostProcessor(
      $this->getContainer(),
      $source
    );

    $feedProcessor = new FeedProcessor(
      $source,
      $systemApi,
      $systemCacheDir
    );

    $fileParser = new FileParser(
      $systemCacheDir
    );

    $statusSender = $this->getMockBuilder(
      StatusSender::class
    )->getMock();

    $postCreator = new PostCreator(
      $fileParser,
      $postProcessor,
      $feedProcessor,
      $statusSender
    );

    $feed = Feeds::get(
      'foreign-movies',
      Video::MOONWALK_ORIGIN
    );

    $expectedItemsLoaded = 3;

    $itemsLoaded = $postCreator->create(
      [$feed],
      $expectedItemsLoaded
    );

    Assert::same(
      $itemsLoaded,
      $expectedItemsLoaded
    );

    $removedItems = $systemApi->removeFeedPostsAndRelatedPosts([
      'videoOrigin' => $source->getVideoOrigin()
    ]);

    Assert::greaterThanEq(
      $removedItems,
      $expectedItemsLoaded
    );
  }
}
