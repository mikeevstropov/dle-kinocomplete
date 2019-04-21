<?php

namespace Kinocomplete\Test\FeedLoader;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\FeedLoader\FeedProcessor;
use Kinocomplete\Api\MoonwalkApi;
use Kinocomplete\Source\Source;
use Kinocomplete\Utils\Utils;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use Kinocomplete\Feed\Feed;
use Webmozart\PathUtil\Path;

class FeedProcessorTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "downloadFeed" method.
   */
  public function testCanDownloadFeed()
  {
    $workingDir = $this->getContainer()->get('system_cache_dir');
    $fileName   = 'feed-file';
    $filePath   = $workingDir .'/'. $fileName;
    $onProgress = function () {};

    $feed = $this->getMockBuilder(Feed::class)
      ->getMock();

    $feed
      ->expects($this->once())
      ->method('getFileName')
      ->willReturn($fileName);

    $api = $this->getMockBuilder(MoonwalkApi::class)
      ->disableOriginalConstructor()
      ->getMock();

    $api
      ->expects($this->once())
      ->method('downloadFeed')
      ->with(
        $feed,
        $filePath,
        $onProgress
      );

    $source = $this->getMockBuilder(Source::class)
      ->disableOriginalConstructor()
      ->getMock();

    $source
      ->expects($this->once())
      ->method('getApi')
      ->willReturn($api);

    $instance = new FeedProcessor(
      $source,
      $this->getContainer()->get('system_api'),
      $workingDir
    );

    $receivedFilePath = $instance->downloadFeed(
      $feed,
      $onProgress
    );

    Assert::same(
      $receivedFilePath,
      $filePath
    );
  }

  /**
   * Testing "removeFeedFile" method.
   *
   * @throws \Exception
   */
  public function testCanRemoveFeedFile()
  {
    $workingDir = $this->getContainer()->get('system_cache_dir');
    $fileName = Utils::randomString();
    $filePath = Path::join($workingDir, $fileName);

    $feed = $this->getMockBuilder(Feed::class)
      ->getMock();

    $feed
      ->expects($this->once())
      ->method('getFileName')
      ->willReturn($fileName);

    $instance = new FeedProcessor(
      new Source(),
      $this->getContainer()->get('system_api'),
      $workingDir
    );

    touch($filePath);

    $instance->removeFeedFile($feed);

    Assert::false(
      file_exists($filePath)
    );
  }

  /**
   * Testing "removeFeedFile" method exceptions.
   */
  public function testCannotRemoveFeedFile()
  {
    $this->expectException(\InvalidArgumentException::class);

    $feed = $this->getMockBuilder(Feed::class)
      ->getMock();

    $feed
      ->expects($this->once())
      ->method('getFileName')
      ->willReturn(Utils::randomString());

    $instance = new FeedProcessor(
      new Source(),
      $this->getContainer()->get('system_api'),
      $this->getContainer()->get('system_cache_dir')
    );

    $instance->removeFeedFile($feed);
  }
}