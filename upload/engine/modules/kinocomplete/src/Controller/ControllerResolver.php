<?php

namespace Kinocomplete\Controller;

use Slim\Http\Response;
use Slim\Http\Request;

class ControllerResolver extends DefaultController
{
  /**
   * Magic method invoke.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return IndexController|Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\NotFoundException
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function __invoke(
    Request $request,
    Response $response,
    $arguments
  ) {
    $action = $request->getQueryParam('action');

    switch ($action) {

      case 'install':

        $this->cronNotAllowed();
        $this->ajaxNotAllowed();

        $controller = new InstallController($this->container);

        return $controller->installAction(
          $request,
          $response,
          $arguments
        );

      case 'uninstall':

        $this->cronNotAllowed();
        $this->ajaxNotAllowed();

        $controller = new InstallController($this->container);

        return $controller->uninstallAction(
          $request,
          $response,
          $arguments
        );

      case 'settings':

        $this->cronNotAllowed();
        $this->ajaxNotAllowed();

        $controller = new SettingsController($this->container);

        return $controller->settingsAction(
          $request,
          $response,
          $arguments
        );

      case 'feed-loader':

        $this->cronNotAllowed();
        $this->ajaxNotAllowed();

        $controller = new FeedLoaderController($this->container);

        return $controller->indexAction(
          $request,
          $response,
          $arguments
        );

      case 'get-configuration':

        $this->cronNotAllowed();

        $controller = new ApiController($this->container);

        return $controller->getConfigurationAction(
          $request,
          $response,
          $arguments
        );

      case 'get-videos':

        $this->cronNotAllowed();

        $controller = new ApiController($this->container);

        return $controller->getVideosAction(
          $request,
          $response,
          $arguments
        );

      case 'get-video':

        $this->cronNotAllowed();

        $controller = new ApiController($this->container);

        return $controller->getVideoAction(
          $request,
          $response,
          $arguments
        );

      case 'get-autocomplete-video':

        $this->cronNotAllowed();

        $controller = new ApiController($this->container);

        return $controller->getAutocompleteVideoAction(
          $request,
          $response,
          $arguments
        );

      case 'create-moonwalk-posts':

        $controller = new ApiController($this->container);

        return $controller->createMoonwalkPostsAction(
          $request,
          $response,
          $arguments
        );

      case 'update-moonwalk-posts':

        $controller = new ApiController($this->container);

        return $controller->updateMoonwalkPostsAction(
          $request,
          $response,
          $arguments
        );

      case 'clean-moonwalk-posts':

        $controller = new ApiController($this->container);

        return $controller->cleanMoonwalkPostsAction(
          $request,
          $response,
          $arguments
        );

      default:

        $this->cronNotAllowed();
        $this->ajaxNotAllowed();

        $controller = new IndexController($this->container);

        return $controller->indexAction(
          $request,
          $response,
          $arguments
        );
    }
  }

  /**
   * Checking "cron" in query.
   */
  protected function cronNotAllowed()
  {
    if (
      !array_key_exists('cron', $_REQUEST) ||
      $_REQUEST['cron'] === null
    ) return;

    header('content-type: text/plain; charset=utf-8');
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
  }

  /**
   * Checking "ajax" in query.
   */
  protected function ajaxNotAllowed()
  {
    if (
      !array_key_exists('ajax', $_REQUEST) ||
      $_REQUEST['ajax'] === null
    ) return;

    header('content-type: text/plain; charset=utf-8');
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
  }
}
