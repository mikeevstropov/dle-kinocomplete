<?php

namespace Kinocomplete\Container;

use Slim\Exception\ContainerValueNotFoundException;
use Pimple\Container as BaseContainer;
use Psr\Container\ContainerInterface;

class Container extends BaseContainer implements ContainerInterface
{
  /**
   * Finds an entry by its identifier.
   *
   * @param  string $id Identifier of the entry to look for.
   * @throws ContainerValueNotFoundException No entry was found for this identifier.
   * @return mixed.
   */
  public function get($id)
  {
    if (!$this->offsetExists($id))
      throw new ContainerValueNotFoundException(
        "Identifier \"$id\" is not defined."
      );

    return $this->offsetGet($id);
  }

  /**
   * Existence check by identifier.
   *
   * @param  string $id Identifier of the entry to look for.
   * @return boolean
   */
  public function has($id)
  {
    return $this->offsetExists($id);
  }
}