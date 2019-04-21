<?php

namespace Kinocomplete\Test\Templating;

use Kinocomplete\Templating\SystemTemplating;
use Kinocomplete\ExtraField\ExtraField;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class SystemTemplatingTest extends TestCase
{
  /**
   * Testing "resolveXFList" method.
   */
  public function testCanResolveXFList()
  {
    $template = '[kc_xflist_field index="2"]';
    $expected = 'second';

    $extraField = new ExtraField();
    $extraField->name = 'field';
    $extraField->value = 'first, second';

    $result = SystemTemplating::resolveXFList(
      $template,
      [$extraField]
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Alternative testing "resolveXFList" method.
   */
  public function testCannotResolveXFList()
  {
    $template = '[kc_xflist_field index="0"]';
    $expected = '';

    $extraField = new ExtraField();
    $extraField->name = 'field';
    $extraField->value = 'first, second';

    $result = SystemTemplating::resolveXFList(
      $template,
      [$extraField]
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing "resolveXFListHas" method.
   */
  public function testCanResolveXFListHas()
  {
    $template = '[kc_xflist_has_field index="1"]Hi[/kc_xflist_has_field]';
    $expected = 'Hi';

    $extraField = new ExtraField();
    $extraField->name = 'field';
    $extraField->value = 'Hi, Hello';

    $result = SystemTemplating::resolveXFListHas(
      $template,
      [$extraField]
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Alternative testing "resolveXFListHas" method.
   */
  public function testCannotResolveXFListHas()
  {
    $template = '[kc_xflist_has_field index="3"]Hi[/kc_xflist_has_field]';
    $expected = '';

    $extraField = new ExtraField();
    $extraField->name = 'field';
    $extraField->value = 'Hi, Hello';

    $result = SystemTemplating::resolveXFListHas(
      $template,
      [$extraField]
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing "resolveXFListNot" method.
   */
  public function testCanResolveXFListNot()
  {
    $template = '[kc_xflist_not_field index="3"]Empty[/kc_xflist_not_field]';
    $expected = 'Empty';

    $extraField = new ExtraField();
    $extraField->name = 'field';
    $extraField->value = 'Hi, Hello';

    $result = SystemTemplating::resolveXFListNot(
      $template,
      [$extraField]
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Alternative testing "resolveXFListNot" method.
   */
  public function testCannotResolveXFListNot()
  {
    $template = '[kc_xflist_not_field index="1"]Empty[/kc_xflist_not_field]';
    $expected = '';

    $extraField = new ExtraField();
    $extraField->name = 'field';
    $extraField->value = 'Hi, Hello';

    $result = SystemTemplating::resolveXFListNot(
      $template,
      [$extraField]
    );

    Assert::same(
      $result,
      $expected
    );
  }
}
