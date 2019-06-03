<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use GuzzleHttp\Exception\RequestException;
use Kinocomplete\Container\Container;
use Kinocomplete\Module\ModuleCache;
use Kinocomplete\Source\Source;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\KodikApi;
use Kinocomplete\Video\Video;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Feed\Feeds;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class KodikApiTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var KodikApi
   */
  public $instance;

  /**
   * KodikApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();
    $this->instance = new KodikApi($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      KodikApi::class
    );
  }

  /**
   * Testing `accessChecking` method.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanAccessChecking()
  {
    $this->instance->accessChecking();
  }

  /**
   * Testing `accessChecking` method with cache.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanAccessCheckingWithCache()
  {
    /** @var Source $source */
    $source = clone $this->getContainer()->get('kodik_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $moduleCache->clean();
    $invalidToken = Utils::randomString();

    // Invalid token.
    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'kodik_source' => $source,
    ]);

    $instance = new KodikApi($container);

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
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotAccessChecking()
  {
    $this->expectException(InvalidTokenException::class);

    /** @var Source $source */
    $source = $this->getContainer()->get('kodik_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $invalidToken = Utils::randomString();

    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'kodik_source' => $source,
    ]);

    $instance = new KodikApi($container);

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
    $title = 'Зеленая миля';

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
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetVideo()
  {
    $id = 'movie-7652';

    $video = $this->instance->getVideo($id);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing `getVideo` method exceptions.
   *
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
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
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanDownloadFeed()
  {
    $feed = Feeds::get(
      'movies',
      Video::KODIK_ORIGIN
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
}
