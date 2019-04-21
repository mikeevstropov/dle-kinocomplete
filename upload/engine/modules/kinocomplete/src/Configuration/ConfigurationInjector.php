<?php

namespace Kinocomplete\Configuration;

use Psr\Container\ContainerInterface;

class ConfigurationInjector
{
  protected $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function inject()
  {
    $database           = $this->container->get('database');
    $module             = $this->container->get('module');
    $configurationTable = $this->container->get('database_configuration_table');

    if (!$module->isInstalled())
      return false;

    $data = $database->select($configurationTable, '*');

    foreach ($data as $row) {

      $key = $row['key'];
      $value = $row['value'];

      $serialized = @unserialize($value);

      $this->container[$key] = $serialized !== false
        ? $serialized
        : $value;
    }

    return true;
  }
}