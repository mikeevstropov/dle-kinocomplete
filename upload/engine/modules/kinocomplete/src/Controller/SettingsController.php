<?php

namespace Kinocomplete\Controller;

use Kinocomplete\Configuration\ConfigurationInjector;
use Kinocomplete\Data\Data;
use Slim\Http\Response;
use Slim\Http\Request;

class SettingsController extends DefaultController
{
  /**
   * Settings action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   */
  public function settingsAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $router             = $this->container->get('router');
    $module             = $this->container->get('module');
    $view               = $this->container->get('view');
    $form               = $request->getParam('settings');
    $database           = $this->container->get('database');
    $configurationTable = $this->container->get('database_configuration_table');
    $extraFields        = $this->container->get('extra_fields');

    // Not installed.
    if (!$module->isInstalled()) {

      $redirectUrl = $router->resolveUrl('install');

      return $response->withRedirect($redirectUrl);
    }

    // Incoming form.
    if (is_array($form)) {

      // Committing.
      try {

        foreach ($form as $key => $value) {

          $rows = $database->select(
            $configurationTable,
            'key',
            ['key[=]' => $key]
          );

          if (count($rows) == 1) {

            $database->update(
              $configurationTable,
              ['value' => $value],
              ['key[=]' => $key]
            );

          } else {

            $database->insert($configurationTable, [
              'key' => $key,
              'value' => $value
            ]);
          }
        }

        $configurationInjector = new ConfigurationInjector($this->container);
        $configurationInjector->inject();

      // Not committed.
      } catch (\Exception $exception) {

        $parameters = [
          'status' => 'error',
          'message' => $exception->getMessage()
        ];

        return $view->render(
          $response,
          'settings/index.html.twig',
          $parameters
        );
      }

      $parameters = [
        'status' => 'success'
      ];

      // Committed.
      return $view->render(
        $response,
        'settings/index.html.twig',
        $parameters
      );

    // Initial step.
    } else {

      $settings = $this->container;
      $tmdbLanguages = Data::getTmdbLanguages();
      $postAccessoryVideoFields = Data::getPostAccessoryVideoFields();
      $postUpdaterVideoFields = Data::getPostUpdaterVideoFields();
      $users = $this->container->get('users');

      $parameters = [
        'status'                   => 'initial',
        'settings'                 => $settings,
        'tmdbLanguages'            => $tmdbLanguages,
        'extraFields'              => $extraFields,
        'postAccessoryVideoFields' => $postAccessoryVideoFields,
        'postUpdaterVideoFields'   => $postUpdaterVideoFields,
        'users'                    => $users
      ];

      // Initial view.
      return $view->render(
        $response,
        'settings/index.html.twig',
        $parameters
      );
    }
  }
}
