<?php

namespace Kinocomplete\Controller;

use Psr\Container\ContainerInterface;

class DefaultController
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