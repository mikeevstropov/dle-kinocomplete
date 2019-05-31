<?php

namespace Kinocomplete\Video;

use Kinocomplete\Video\FactoryTrait\VideoCdnFactoryTrait;
use Kinocomplete\Video\FactoryTrait\MoonwalkFactoryTrait;
use Kinocomplete\Video\FactoryTrait\RutorFactoryTrait;
use Kinocomplete\Video\FactoryTrait\KodikFactoryTrait;
use Kinocomplete\Video\FactoryTrait\TmdbFactoryTrait;
use Kinocomplete\Video\FactoryTrait\HdvbFactoryTrait;
use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Templating\Templating;
use Kinocomplete\Utils\Utils;

class VideoFactory extends DefaultService
{
  use MoonwalkFactoryTrait;
  use TmdbFactoryTrait;
  use KodikFactoryTrait;
  use HdvbFactoryTrait;
  use VideoCdnFactoryTrait;
  use RutorFactoryTrait;

  /**
   * Apply patterns.
   *
   * @param  Video $video
   * @return Video
   * @throws \Throwable
   */
  public function applyPatterns(Video $video)
  {
    $patterns = ContainerFactory::fromNamespace(
      $this->container,
      'video_pattern',
      true
    );

    $context = (array) $video;

    foreach ($patterns as $field => $pattern) {

      $field = Utils::snakeToCamel($field);

      $video->$field = Templating::renderString(
        $pattern,
        $context
      );
    }

    return $video;
  }
}
