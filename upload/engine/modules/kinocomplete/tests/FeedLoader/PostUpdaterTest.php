<?php

namespace Kinocomplete\Test\FeedLoader;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\FeedLoader\FeedProcessor;
use Kinocomplete\FeedLoader\PostProcessor;
use Kinocomplete\FeedLoader\StatusSender;
use Kinocomplete\FeedLoader\PostCreator;
use Kinocomplete\FeedLoader\PostUpdater;
use Kinocomplete\FeedLoader\FileParser;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Kinocomplete\Feed\Feeds;
use Webmozart\Assert\Assert;

class PostUpdaterTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var SystemApi
   */
  protected $systemApi;

  /**
   * @var StatusSender
   */
  protected $statusSender;

  /**
   * @var PostUpdater
   */
  protected $postUpdater;

  /**
   * @var PostCreator
   */
  protected $postCreator;

  /**
   * Before each test.
   */
  public function setUp()
  {
    $source = $this->getContainer()->get('moonwalk_source');
    $cacheDir = $this->getContainer()->get('system_cache_dir');
    $this->systemApi = $this->getContainer()->get('system_api');

    $fileParser = new FileParser(
      $cacheDir
    );

    $postProcessor = new PostProcessor(
      $this->getContainer(),
      $source
    );

    $feedProcessor = new FeedProcessor(
      $source,
      $this->systemApi,
      $cacheDir
    );

    $postCreatorStatusSender = $this->getMockBuilder(
      StatusSender::class
    )->setMethods([
      'openConnection',
      'closeConnection',
      'sendStatus'
    ])->getMock();

    $this->postCreator = new PostCreator(
      $fileParser,
      $postProcessor,
      $feedProcessor,
      $postCreatorStatusSender
    );

    $this->statusSender = $this->getMockBuilder(
      StatusSender::class
    )->setMethods([
      'openConnection',
      'closeConnection',
      'sendStatus'
    ])->getMock();

    $this->postUpdater = new PostUpdater(
      $fileParser,
      $postProcessor,
      $feedProcessor,
      $this->statusSender
    );
  }

  /**
   * Testing "update" method.
   *
   * @throws \Kinocomplete\Exception\NotFoundException
   * @throws \ReflectionException
   */
  public function testCanUpdate()
  {
    $expectedItems = 2;

    $feed = Feeds::get(
      'foreign-movies',
      Video::MOONWALK_ORIGIN
    );

    $this->systemApi->removePosts();

    $this->postCreator->create(
      [$feed],
      $expectedItems
    );

    $posts = $this->systemApi->getPosts();

    Assert::count($posts, $expectedItems);

    $firstPost = $posts[0];
    $secondPost = $posts[1];

    $firstTitle = $firstPost->title;
    $secondTitle = $secondPost->title;

    $firstPost->title = Utils::randomString();
    $secondPost->title = Utils::randomString();

    $this->systemApi->updatePost($firstPost);
    $this->systemApi->updatePost($secondPost);

    $posts = $this->systemApi->getPosts();

    Assert::count($posts, $expectedItems);

    Assert::same(
      $posts[0]->title,
      $firstPost->title
    );

    Assert::same(
      $posts[1]->title,
      $secondPost->title
    );

    $parsed = $this->postUpdater->update(
      [$feed],
      $expectedItems
    );

    Assert::same($parsed, $expectedItems);

    $posts = $this->systemApi->getPosts();

    Assert::count($posts, $expectedItems);

    Assert::same(
      $posts[0]->title,
      $firstTitle
    );

    Assert::same(
      $posts[1]->title,
      $secondTitle
    );

    $statusSenderReflection = new \ReflectionObject($this->statusSender);
    $processedItems = $statusSenderReflection->getProperty('processedItems');
    $processedItems->setAccessible(true);
    $processedItems = $processedItems->getValue($this->statusSender);

    Assert::same(
      $processedItems,
      $expectedItems
    );

    $this->systemApi->removePosts();
  }

  /**
   * Testing "update" method without update.
   *
   * @throws \ReflectionException
   */
  public function testCanUpdateWithoutUpdate()
  {
    $expectedItems = 3;

    $feed = Feeds::get(
      'foreign-movies',
      Video::MOONWALK_ORIGIN
    );

    $this->systemApi->removePosts();

    $this->postCreator->create(
      [$feed],
      $expectedItems
    );

    $parsed = $this->postUpdater->update(
      [$feed],
      $expectedItems
    );

    Assert::same($parsed, $expectedItems);

    $statusSenderReflection = new \ReflectionObject($this->statusSender);
    $processedItems = $statusSenderReflection->getProperty('processedItems');
    $processedItems->setAccessible(true);
    $processedItems = $processedItems->getValue($this->statusSender);

    Assert::same($processedItems, 0);

    $this->systemApi->removePosts();
  }
}