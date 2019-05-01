<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Exception\UnexpectedResponseException;
use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\Container;
use Kinocomplete\Module\ModuleCache;
use Kinocomplete\Api\VideoCdnApi;
use Kinocomplete\Source\Source;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\HdvbApi;
use Kinocomplete\Video\Video;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;

class VideoCdnApiTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var HdvbApi
   */
  public $instance;

  /**
   * HdvbApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();
    $this->instance = new VideoCdnApi($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      VideoCdnApi::class
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
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testCanAccessCheckingWithCache()
  {
    /** @var Source $source */
    $source = clone $this->getContainer()->get('video_cdn_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $moduleCache->clean();
    $invalidToken = Utils::randomString();

    // Invalid token.
    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'video_cdn_source' => $source,
    ]);

    $instance = new VideoCdnApi($container);

    $exception = null;

    try {

      $instance->accessChecking(true);

    } catch (UnexpectedResponseException $e) {

      $exception = $e;
    }

    Assert::isInstanceOf(
      $exception,
      UnexpectedResponseException::class
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
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testCannotAccessChecking()
  {
    $this->expectException(UnexpectedResponseException::class);

    /** @var Source $source */
    $source = $this->getContainer()->get('video_cdn_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $invalidToken = Utils::randomString();

    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'video_cdn_source' => $source,
    ]);

    $instance = new VideoCdnApi($container);

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
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testCanGetVideo()
  {
    $id = '4430';

    $video = $this->instance->getVideo($id);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing `getVideo` method exceptions.
   *
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testCannotGetVideo()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getVideo(md5('id'));
  }
}
