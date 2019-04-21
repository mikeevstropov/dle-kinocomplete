<?php

namespace Kinocomplete\Controller;

use Slim\Http\Response;
use Slim\Http\Request;

class InstallController extends DefaultController
{
  /**
   * Install action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   */
  public function installAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $router = $this->container->get('router');
    $module = $this->container->get('module');
    $view   = $this->container->get('view');
    $step   = $request->getQueryParam('step');

    // Install step.
    if ($step === 'install') {

      // Installing.
      try {

        $module->install();

      // Not installed.
      } catch (\Exception $exception) {

        $parameters = [
          'status' => 'error',
          'message' => $exception->getMessage()
        ];

        return $view->render(
          $response,
          'install/index.html.twig',
          $parameters
        );
      }

      $parameters = [
        'status' => 'success'
      ];

      // Installed.
      return $view->render(
        $response,
        'install/index.html.twig',
        $parameters
      );

    // Initial step.
    } else {

      // Already installed.
      if ($module->isInstalled()) {

        $redirectUrl = $router->resolveUrl();

        return $response->withRedirect($redirectUrl);
      }

      $parameters = [
        'status' => 'initial'
      ];

      // Initial view.
      return $view->render(
        $response,
        'install/index.html.twig',
        $parameters
      );
    }
  }

  /**
   * Uninstall action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   */
  public function uninstallAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $router = $this->container->get('router');
    $module = $this->container->get('module');
    $view   = $this->container->get('view');
    $step   = $request->getQueryParam('step');

    // Uninstall step.
    if ($step === 'uninstall') {

      // Uninstalling.
      try {

        $module->uninstall();

      // Not uninstalled.
      } catch (\Exception $exception) {

        $parameters = [
          'status' => 'error',
          'message' => $exception->getMessage()
        ];

        return $view->render(
          $response,
          'uninstall/index.html.twig',
          $parameters
        );
      }

      $parameters = [
        'status' => 'success'
      ];

      // Uninstalled.
      return $view->render(
        $response,
        'uninstall/index.html.twig',
        $parameters
      );

    // Initial step.
    } else {

      // Not installed.
      if (!$module->isInstalled()) {

        $redirectPath = $router->resolveUrl('install');

        return $response->withRedirect($redirectPath);
      }

      $parameters = [
        'status' => 'initial'
      ];

      // Initial view.
      return $view->render(
        $response,
        'uninstall/index.html.twig',
        $parameters
      );
    }
  }
}
