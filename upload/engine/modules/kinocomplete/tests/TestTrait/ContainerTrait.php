<?php

namespace Kinocomplete\Tests\TestTrait;

use Kinocomplete\Configuration\ConfigurationProvider;
use Kinocomplete\Service\ServiceInjector;
use Kinocomplete\Container\Container;

trait ContainerTrait
{
  /**
   * Container Getter
   *
   * @return Container
   */
  protected function getContainer()
  {
    $workingDir = __DIR__ .'/../../';

    // Load configuration.
    $configuration = ConfigurationProvider::getDefaults($workingDir, [
      'router'                  => null,
      'settings'                => null,
      'system_root_dir'         => realpath(getenv('system_root_dir')),
      'system_cache_dir'        => realpath(getenv('system_cache_dir')),
      'system_upload_dir'       => realpath(getenv('system_upload_dir')),
      'database_host'           => getenv('database_host'),
      'database_name'           => getenv('database_name'),
      'database_user'           => getenv('database_user'),
      'database_pass'           => getenv('database_pass'),
      'database_prefix'         => getenv('database_prefix'),
      'moonwalk_token'          => getenv('moonwalk_token'),
      'tmdb_token'              => getenv('tmdb_token'),
      'hdvb_token'              => getenv('hdvb_token'),
      'feed_loader_posts_limit' => getenv('feed_loader_posts_limit'),
    ]);

    $container = new Container($configuration);

    // Inject services.
    $serviceInjector = new ServiceInjector($container);
    $serviceInjector->inject();

    return $container;
  }
}
