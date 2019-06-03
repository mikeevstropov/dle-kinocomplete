<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use GuzzleHttp\Exception\RequestException;
use Kinocomplete\Container\Container;
use Kinocomplete\Module\ModuleCache;
use Kinocomplete\Api\MoonwalkApi;
use Kinocomplete\Source\Source;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Video\Video;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Feed\Feeds;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class MoonwalkApiTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var MoonwalkApi
   */
  public $instance;

  /**
   * MoonwalkApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();
    $this->instance = new MoonwalkApi($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      MoonwalkApi::class
    );
  }

  /**
   * Testing `accessChecking` method.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanAccessChecking()
  {
    $this->instance->accessChecking();
  }

  /**
   * Testing `accessChecking` method with cache.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanAccessCheckingWithCache()
  {
    /** @var Source $source */
    $source = clone $this->getContainer()->get('moonwalk_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $moduleCache->clean();
    $invalidToken = Utils::randomString();

    // Invalid token.
    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'moonwalk_source' => $source,
    ]);

    $instance = new MoonwalkApi($container);

    $exception = null;

    try {

      $instance->accessChecking(true);

    } catch (InvalidTokenException $e) {

      $exception = $e;
    }

    Assert::isInstanceOf(
      $exception,
      InvalidTokenException::class
    );

    // Invalid token with cache.
    $moduleCache->addApiToken(
      $invalidToken,
      $source->getVideoOrigin()
    );

    $instance->accessChecking(true);
  }

  /**
   * Testing `accessChecking` method exceptions.
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotAccessChecking()
  {
    $this->expectException(InvalidTokenException::class);

    /** @var Source $source */
    $source = $this->getContainer()->get('moonwalk_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $invalidToken = Utils::randomString();

    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'moonwalk_source' => $source,
    ]);

    $instance = new MoonwalkApi($container);

    $instance->accessChecking();
  }

  /**
   * Testing `getVideos` method.
   *
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetVideos()
  {
    $title = 'Джерри Магуайер';

    $array = $this->instance->getVideos($title);

    Assert::isArray($array);
    Assert::notEmpty(count($array));

    $video = $array[0];

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing `getVideos` method exceptions.
   *
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotGetVideos()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getVideos(md5('title'));
  }

  /**
   * Testing `getVideo` method.
   *
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetVideo()
  {
    $id = '7b2515322229e7e85539587fc47962dd';

    $video = $this->instance->getVideo($id);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing `getVideo` method with screenshots.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetVideoWithScreenshots()
  {
    $id = '7da65d5f3a98d26e';

    $container = new Container([
      'moonwalk_source' => $this->getContainer()->get('moonwalk_source'),
      'client' => $this->getContainer()->get('client'),
      'moonwalk_screenshots_enabled' => true
    ]);

    $instance = new MoonwalkApi($container);

    $video = $instance->getVideo($id);

    Assert::isArray(
      $video->screenshots
    );

    Assert::greaterThan(
      count($video->screenshots),
      0
    );

    foreach ($video->screenshots as $screenshot) {

      Assert::stringNotEmpty($screenshot);
    }
  }

  /**
   * Testing `getVideo` method with screenshots not found.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetVideoWithScreenshotsNotFound()
  {
    $id = 'c963de22bf697c3e';

    $container = new Container([
      'moonwalk_source' => $this->getContainer()->get('moonwalk_source'),
      'client' => $this->getContainer()->get('client'),
      'moonwalk_screenshots_enabled' => true
    ]);

    $instance = new MoonwalkApi($container);

    $video = $instance->getVideo($id);

    Assert::isArray(
      $video->screenshots
    );

    Assert::count(
      $video->screenshots,
      0
    );
  }

  /**
   * Testing `getVideo` method exceptions.
   *
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotGetVideo()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getVideo(md5('id'));
  }

  /**
   * Testing `downloadFeed` method exceptions.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\FileSystemPermissionException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanDownloadFeed()
  {
    $feed = Feeds::get(
      'foreign-movies',
      Video::MOONWALK_ORIGIN
    );

    $onProgress = function (
      $totalBytes,
      $loadedBytes
    ) {
      if ($loadedBytes > 100)
        throw new \OverflowException();
    };

    $filePath = Path::join(
      $this->getContainer()->get('system_cache_dir'),
      $feed->getFileName()
    );

    try {

      $this->instance->downloadFeed(
        $feed,
        $filePath,
        $onProgress
      );

    } catch (\OverflowException $exception) {
    } catch (RequestException $exception) {

      $previous = $exception->getPrevious();

      $overflowed = $previous
        && $previous instanceof \OverflowException;

      if (!$overflowed)
        throw $exception;
    }

    Assert::fileExists($filePath);
    Assert::true(unlink($filePath));
  }

  /**
   * Testing `getScreenshots` method.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetScreenshots()
  {
    $videoId = '92bf227b3aa334e2';

    $screenshots = $this->instance->getScreenshots($videoId);

    Assert::isArray($screenshots);
    Assert::notEmpty($screenshots);
  }

  /**
   * Testing `getScreenshots` method exceptions.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotGetScreenshots()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getScreenshots(Utils::randomString());
  }
}
