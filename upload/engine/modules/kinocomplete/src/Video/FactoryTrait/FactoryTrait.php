<?php

namespace Kinocomplete\Video\FactoryTrait;

use Psr\Container\ContainerInterface;
use Kinocomplete\Video\Video;

trait FactoryTrait
{
  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * Apply patterns.
   *
   * @param  Video $video
   * @return Video
   */
  abstract public function applyPatterns(Video $video);
}
