<?php

namespace Kinocomplete\Test\Api;

use Kinocomplete\Exception\UnexpectedResponseException;
use Kinocomplete\Exception\TooLargeResponseException;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\Container;
use Kinocomplete\Source\Source;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\RutorApi;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

class RutorApiTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var RutorApi
   */
  public $instance;

  /**
   * RutorApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $container = $this->getContainer();
    $this->instance = new RutorApi($container);
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      RutorApi::class
    );
  }

  /**
   * Testing `accessChecking` method.
   *
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function testCanAccessChecking()
  {
    $this->instance->accessChecking();
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
    $source = $this->getContainer()->get('rutor_source');

    $invalidHost = $source->getHost() .'.'. Utils::randomString(3);
    $source->setHost($invalidHost);

    $container = new Container([
      'client' => $this->getContainer()->get('client'),
      'rutor_source' => $source
    ]);

    $instance = new RutorApi($container);

    $instance->accessChecking();
  }

  /**
   * Testing `getVideos` method.
   *
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   */
  public function testCanGetVideos()
  {
    $title = 'марсианин';

    $array = $this->instance->getVideos($title);

    Assert::isArray($array);
    Assert::notEmpty($array);

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
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   */
  public function testCannotGetVideos()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getVideos(Utils::randomString(5));
  }

  /**
   * Testing `getVideos` method exceptions by short query.
   *
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   */
  public function testCannotGetVideosByShortQuery()
  {
    $this->expectException(TooLargeResponseException::class);

    $this->instance->getVideos(Utils::randomString(2));
  }

  /**
   * Testing `getVideo` method.
   *
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\ParsingException
   */
  public function testCanGetVideo()
  {
    $id = '478003';

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
   * @throws \Kinocomplete\Exception\ParsingException
   */
  public function testCannotGetVideo()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getVideo(md5('id'));
  }
}
