<?php

namespace Kinocomplete\Test\User;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\User\UserFactory;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;
use Kinocomplete\User\User;

class UserFactoryTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "fromDatabaseArray" method.
   */
  public function testCanFromDatabaseArray()
  {
    $arrayJson = file_get_contents(
      FIXTURES_DIR .'/user/user.json'
    );

    $array = json_decode($arrayJson, true);

    /** @var UserFactory $userFactory */
    $userFactory = $this->getContainer()->get('user_factory');

    $user = $userFactory->fromDatabaseArray($array);

    Assert::isInstanceOf(
      $user,
      User::class
    );

    Assert::same(
      $user->id,
      $array['user_id']
    );

    Assert::same(
      $user->name,
      $array['name']
    );
  }

  /**
   * Testing "toDatabaseArray" method.
   */
  public function testCanToDatabaseArray()
  {
    $user = new User();
    $user->id = '1';
    $user->name = 'name';

    /** @var UserFactory $userFactory */
    $userFactory = $this->getContainer()->get('user_factory');

    $array = $userFactory->toDatabaseArray($user);

    Assert::same(
      $array['user_id'],
      $user->id
    );

    Assert::same(
      $array['name'],
      $user->name
    );
  }
}