<?php

namespace Kinocomplete\Test\Video;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Container\Container;
use Kinocomplete\Video\VideoFactory;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;

class VideoFactoryTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var VideoFactory
   */
  public $instance;

  /**
   * VideoFactoryTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $this->instance = new VideoFactory(
      $this->getContainer()
    );
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      VideoFactory::class
    );
  }

  /**
   * Testing "applyPatterns" method.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanApplyPatterns()
  {
    $video = new Video();
    $video->title = 'My title';
    $video->duration = 300;

    $expected = 'My title - 300 sec.';

    $container = new Container([
      'video_pattern_world_title' => '{title} - {duration} sec.'
    ]);

    $factory = new VideoFactory($container);

    Assert::same(
      $factory->applyPatterns($video)->worldTitle,
      $expected
    );
  }

  /**
   * Alternative testing "applyPatterns" method.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCannotApplyPatterns()
  {
    $video = new Video();
    $video->worldTitle = 'My title';

    $expected = 'My title';

    $container = new Container([]);
    $factory = new VideoFactory($container);

    Assert::same(
      $factory->applyPatterns($video)->worldTitle,
      $expected
    );
  }

  /**
   * Testing "fromMoonwalk" method.
   */
  public function testCanFromMoonwalk()
  {
    $array = ['token' => 'identifier'];

    $video = $this->instance->fromMoonwalk($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "fromMoonwalk" method exceptions.
   */
  public function testCannotFromMoonwalk()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->fromMoonwalk([]);
  }

  /**
   * Testing "fromTmdb" method.
   */
  public function testCanFromTmdb()
  {
    $array = ['id' => 'identifier'];

    $video = $this->instance->fromTmdb($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "fromMoonwalk" method exceptions.
   */
  public function testCannotFromTmdb()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->fromTmdb([]);
  }

  /**
   * Testing "fromKodik" method.
   */
  public function testCanFromKodik()
  {
    $array = ['id' => 'identifier'];

    $video = $this->instance->fromKodik($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "fromKodik" method exceptions.
   */
  public function testCannotFromKodik()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->fromKodik([]);
  }

  /**
   * Testing "fromHdvb" method.
   */
  public function testCanFromHdvb()
  {
    $array = ['token' => 'identifier'];

    $video = $this->instance->fromHdvb($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "fromHdvb" method exceptions.
   */
  public function testCannotFromHdvb()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->fromHdvb([]);
  }

  /**
   * Testing "fromVideoCdn" method.
   */
  public function testCanFromVideoCdn()
  {
    $array = ['kp_id' => 'identifier'];

    $video = $this->instance->fromVideoCdn($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "fromVideoCdn" method exceptions.
   */
  public function testCannotFromVideoCdn()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->fromVideoCdn([]);
  }

  /**
   * Testing "fromRutor" method.
   *
   * @throws \Throwable
   */
  public function testCanFromRutor()
  {
    $array = [
      'id'          => 'id',
      'title'       => 'title',
      'magnet_link' => 'magnetLink',
      'file_link'   => 'fileLink',
      'file_size'   => 1200,
      'seeds'       => 40,
      'leeches'     => 30
    ];

    $video = $this->instance->fromRutor($array);

    Assert::same(
      $video->id,
      $array['id']
    );

    Assert::same(
      $video->title,
      $array['title']
    );

    Assert::same(
      $video->magnetLink,
      $array['magnet_link']
    );

    Assert::same(
      $video->torrentFile,
      $array['file_link']
    );

    Assert::same(
      $video->torrentSize,
      '1.17 KB'
    );

    Assert::same(
      $video->torrentSeeds,
      $array['seeds']
    );

    Assert::same(
      $video->torrentLeeches,
      $array['leeches']
    );
  }

  /**
   * Testing "fromRutor" method exceptions.
   */
  public function testCannotFromRutor()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->fromRutor([]);
  }
}
