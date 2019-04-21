<?php

namespace Kinocomplete\Router;

use Slim\Router as BaseRouter;

class Router extends BaseRouter
{
  public function resolveUrl($action = null, $query = [])
  {
    $moduleName = $this->container->get('module_name');

    $defaultQuery = [
      'mod' => $moduleName,
      'action' => $action
    ];

    $mergedQuery = array_merge(
      $defaultQuery,
      $query
    );

    $computedQuery = http_build_query($mergedQuery);

    return $this->basePath .'?'. $computedQuery;
  }

  public function resolveModuleUrl($module = null, $action = null, $query = [])
  {
    $defaultQuery = [
      'mod' => $module,
      'action' => $action
    ];

    $mergedQuery = array_merge(
      $defaultQuery,
      $query
    );

    $computedQuery = http_build_query($mergedQuery);

    return $this->basePath .'?'. $computedQuery;
  }
}