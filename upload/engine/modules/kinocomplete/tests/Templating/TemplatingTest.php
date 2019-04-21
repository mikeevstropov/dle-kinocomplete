<?php

namespace Kinocomplete\Test\Templating;

use Kinocomplete\Container\Container;
use Kinocomplete\Templating\Templating;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class TemplatingTest extends TestCase
{
  /**
   * Testing "renderString" method.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanRenderString()
  {
    $template = 'My {{variable}}.';
    $expected = 'My template.';

    $result = Templating::renderString(
      $template,
      ['variable' => 'template']
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing "renderString" method by container.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanRenderStringByContainer()
  {
    $template = 'My {{variable}}.';
    $expected = 'My template.';

    $context = new Container([
      'variable' => 'template'
    ]);

    $result = Templating::renderString(
      $template,
      $context
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing "renderString" method by single brackets.
   *
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function testCanRenderStringBySingleBrackets()
  {
    $template = 'My {% if third is not defined %}{first}{% endif %} {second}.';
    $expected = 'My amazing template.';

    $context = new Container([
      'first' => 'amazing',
      'second' => 'template'
    ]);

    $result = Templating::renderString(
      $template,
      $context
    );

    Assert::same(
      $result,
      $expected
    );
  }
}