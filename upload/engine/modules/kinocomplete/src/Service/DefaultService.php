<?php

namespace Kinocomplete\Service;

use Psr\Container\ContainerInterface;

class DefaultService
{
  /**
   * @var ContainerInterface
   */
  protected $container;

  /**
   * Service constructor.
   *
   * @param ContainerInterface $container
   */
  public function __construct(
    ContainerInterface $container
  ) {
    $this->container = $container;
  }
}