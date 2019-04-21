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
   * Testing "createByMoonwalk" method.
   */
  public function testCanCreateByMoonwalk()
  {
    $array = ['token' => 'identifier'];

    $video = $this->instance->createByMoonwalk($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "createByMoonwalk" method exceptions.
   */
  public function testCannotCreateByMoonwalk()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->createByMoonwalk([]);
  }

  /**
   * Testing "createByTmdb" method.
   */
  public function testCanCreateByTmdb()
  {
    $array = ['id' => 'identifier'];

    $video = $this->instance->createByTmdb($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "createByMoonwalk" method exceptions.
   */
  public function testCannotCreateByTmdb()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->createByTmdb([]);
  }

  /**
   * Testing "createByHdvb" method.
   */
  public function testCanCreateByHdvb()
  {
    $array = ['token' => 'identifier'];

    $video = $this->instance->createByHdvb($array);

    Assert::isInstanceOf(
      $video,
      Video::class
    );
  }

  /**
   * Testing "createByHdvb" method exceptions.
   */
  public function testCannotCreateByHdvb()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->createByHdvb([]);
  }

  /**
   * Testing "createByRutor" method.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanCreateByRutor()
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

    $video = $this->instance->createByRutor($array);

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
   * Testing "createByRutor" method exceptions.
   */
  public function testCannotCreateByRutor()
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->instance->createByRutor([]);
  }
}
