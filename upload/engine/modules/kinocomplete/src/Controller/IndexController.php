<?php

namespace Kinocomplete\Controller;

use Slim\Http\Response;
use Slim\Http\Request;

class IndexController extends DefaultController
{
  /**
   * Index action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   */
  public function indexAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $router = $this->container->get('router');
    $module = $this->container->get('module');
    $view   = $this->container->get('view');

    // Not installed.
    if (!$module->isInstalled()) {

      $redirectUrl = $router->resolveUrl('install');

      return $response->withRedirect($redirectUrl);
    }

    return $view->render(
      $response,
      'index.html.twig'
    );
  }
}
