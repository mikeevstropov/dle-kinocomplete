<?php

namespace Kinocomplete\Test\Post;

use Kinocomplete\ExtraField\ExtraField;
use Kinocomplete\Post\Post;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Utils\Utils;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class PostTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "getExtraField" method.
   */
  public function testCanGetExtraField()
  {
    $post = new Post();

    $extraField = new ExtraField();
    $extraField->name = 'Name';
    $extraField->value = 'Value';

    Assert::false(
      $post->getExtraField(
        $extraField->name
      )
    );

    $post->extraFields = [$extraField];

    $founded = $post->getExtraField(
      $extraField->name
    );

    Assert::isInstanceOf(
      $founded,
      ExtraField::class
    );

    Assert::same(
      $founded->name,
      $extraField->name
    );

    Assert::same(
      $founded->value,
      $extraField->value
    );
  }

  /**
   * Testing "getExtraField" method exceptions.
   */
  public function testCannotGetExtraField()
  {
    $this->expectException(\InvalidArgumentException::class);

    $post = new Post();
    $post->getExtraField('');
  }

  /**
   * Testing "removeExtraField" method.
   */
  public function testCanRemoveExtraField()
  {
    $post = new Post();

    $extraField = new ExtraField();
    $extraField->name = 'Name';
    $extraField->value = 'Value';

    $post->extraFields = [$extraField];

    $post->removeExtraField(
      $extraField->name
    );

    Assert::isEmpty(
      $post->extraFields
    );
  }

  /**
   * Testing "removeExtraField" method exceptions.
   */
  public function testCannotRemoveExtraField()
  {
    $this->expectException(\InvalidArgumentException::class);

    $post = new Post();
    $post->removeExtraField(Utils::randomString());
  }

  /**
   * Testing "setExtraFieldValue" method.
   */
  public function testCanSetExtraFieldValue()
  {
    $post = new Post();

    $extraField = new ExtraField();
    $extraField->name = 'Name';
    $extraField->value = 'Value';

    $post->extraFields = [$extraField];

    $expectedValue = 'Value2';

    $post->setExtraFieldValue(
      $extraField->name,
      $expectedValue
    );

    Assert::same(
      $post->extraFields[0]->value,
      $expectedValue
    );
  }

  /**
   * Testing "setExtraFieldValue" method exceptions.
   */
  public function testCannotSetExtraFieldValue()
  {
    $this->expectException(\InvalidArgumentException::class);

    $post = new Post();

    $post->setExtraFieldValue(
      Utils::randomString(),
      Utils::randomString()
    );
  }
}