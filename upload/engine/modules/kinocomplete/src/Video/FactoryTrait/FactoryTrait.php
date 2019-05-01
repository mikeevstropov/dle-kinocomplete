<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Video\Video;
use Psr\Container\ContainerInterface;

/**
 * Trait FactoryTrait.
 *
 * @package Kinocomplete\Video\FactoryTrait
 * @property ContainerInterface $container
 */
trait FactoryTrait
{
  /**
   * Apply patterns.
   *
   * @param  Video $video
   * @return Video
   */
  abstract public function applyPatterns(Video $video);
}
