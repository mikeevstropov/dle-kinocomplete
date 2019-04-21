<?php

namespace Kinocomplete\Templating;

use Kinocomplete\Container\ContainerFactory;
use Psr\Container\ContainerInterface;

class Templating
{
  /**
   * Get instance.
   *
   * @return \Twig_Environment
   */
  static protected function getInstance()
  {
    static $instance = null;

    if (!$instance) {

      $instance = new \Twig_Environment(
        new \Twig_Loader_Array()
      );
    }

    return $instance;
  }

  /**
   * Render string template.
   *
   * @param  $string
   * @param  array|ContainerInterface $context
   * @return false|string
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  static public function renderString(
    $string,
    $context = []
  ) {
    // Single brackets to double.
    $string = preg_replace(
      '/(?<![{%]){([^{}%]+?)}(?![}%])/',
      '{{$1}}',
      $string
    );

    $context = $context instanceof ContainerInterface
      ? ContainerFactory::toArray($context)
      : $context;

    $instance = self::getInstance();

    $template = $instance->createTemplate($string);

    return $template->render($context);
  }
}