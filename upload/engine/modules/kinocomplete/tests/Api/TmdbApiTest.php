<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\Container;
use Kinocomplete\Module\ModuleCache;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Source\Source;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Api\TmdbApi;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

class TmdbApiTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var TmdbApi
   */
  public $instance;

  /**
   * TmdbApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();
    $this->instance = new TmdbApi($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      TmdbApi::class
    );
  }

  /**
   * Testing `accessChecking` method.
   *
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\EmptyQueryException
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
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanAccessCheckingWithCache()
  {
    /** @var Source $source */
    $source = clone $this->getContainer()->get('tmdb_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $moduleCache->clean();
    $invalidToken = Utils::randomString();

    // Invalid token.
    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'tmdb_source' => $source,
    ]);

    $instance = new TmdbApi($container);

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
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotAccessChecking()
  {
    $this->expectException(InvalidTokenException::class);

    /** @var Source $source */
    $source = $this->getContainer()->get('tmdb_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->getContainer()->get('module_cache');

    $invalidToken = Utils::randomString();

    $source->setToken($invalidToken);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'module_cache' => $moduleCache,
      'tmdb_source' => $source,
    ]);

    $instance = new TmdbApi($container);

    $instance->accessChecking();
  }

  /**
   * Testing `getVideos` method.
   *
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\InvalidTokenException
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
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCanGetVideo()
  {
    $id = '286217';

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
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function testCannotGetVideo()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getVideo(md5('id'));
  }
}
