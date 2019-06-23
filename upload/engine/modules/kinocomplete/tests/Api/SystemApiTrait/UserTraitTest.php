<?php

namespace Kinocomplete\Test\Api\SystemApiTrait;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Api\SystemApiTrait\UserTrait;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\Container;
use Kinocomplete\User\UserFactory;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Kinocomplete\User\User;
use Medoo\Medoo;

class UserTraitTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var SystemApi
   */
  public $instance;

  /**
   * @var Medoo
   */
  public $database;

  /**
   * SystemApiTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $this->database = $this->getContainer()->get('database');

    $this->instance = $this->getMockBuilder(
      UserTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $this->instance
      ->method('getContainer')
      ->willReturn($this->getContainer());
  }

  /**
   * Testing `addUser` method.
   *
   * @return User
   * @throws \Exception
   */
  public function testCanAddUser()
  {
    $user = new User();
    $user->name = Utils::randomString();
    $user->email = Utils::randomString();

    $addedUser = $this->instance->addUser($user);

    Assert::isInstanceOf(
      $addedUser,
      User::class
    );

    Assert::stringNotEmpty(
      $addedUser->id
    );

    Assert::same(
      $addedUser->name,
      $user->name
    );

    Assert::same(
      $addedUser->email,
      $user->email
    );

    return $addedUser;
  }

  /**
   * Testing `getUser` method.
   *
   * @param   User $user
   * @return  string
   * @throws  NotFoundException
   * @depends testCanAddUser
   */
  public function testCanGetUser(
    User $user
  ) {
    $fetchedUser = $this->instance->getUser(
      $user->id
    );

    Assert::isInstanceOf(
      $fetchedUser,
      User::class
    );

    Assert::same(
      $user->id,
      $fetchedUser->id
    );

    Assert::same(
      $user->name,
      $fetchedUser->name
    );

    Assert::same(
      $user->email,
      $fetchedUser->email
    );

    return $fetchedUser;
  }

  /**
   * Testing `updateUser` method.
   *
   * @param   User $user
   * @return  User
   * @throws  NotFoundException
   * @depends testCanAddUser
   */
  public function testCanUpdateUser(
    User $user
  ) {
    $user->name = Utils::randomString();

    $this->instance->updateUser($user);

    $updatedUser = $this->instance->getUser(
      $user->id
    );

    Assert::same(
      $updatedUser->name,
      $user->name
    );

    Assert::same(
      $updatedUser->email,
      $user->email
    );

    return $updatedUser;
  }

  /**
   * Testing `getUsers` method.
   *
   * @param   User $user
   * @return  mixed
   * @throws  \Exception
   * @depends testCanUpdateUser
   */
  public function testCanGetUsers(
    User $user
  ) {
    $fetchedUsers = $this->instance->getUsers([
      'user_id' => $user->id
    ]);

    Assert::notEmpty($fetchedUsers);

    $fetchedUser = $fetchedUsers[0];

    Assert::isInstanceOf(
      $fetchedUser,
      User::class
    );

    Assert::same(
      $user->id,
      $fetchedUser->id
    );

    Assert::same(
      $user->name,
      $fetchedUser->name
    );

    Assert::same(
      $user->email,
      $fetchedUser->email
    );

    return $fetchedUser->id;
  }

  /**
   * Testing `getUsers` method with "join".
   *
   * @throws \Exception
   */
  public function testCanGetUsersWithJoin()
  {
    $table   = 'users';
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    /** @var UserFactory $factory */
    $factory = $this->getContainer()->get('user_factory');

    $expectedInstance = new User();
    $expectedArray = $factory->toDatabaseArray($expectedInstance);

    $database
      ->expects($this->exactly(2))
      ->method('select')
      ->with(
        $table,
        $join,
        $columns,
        $where
      )->willReturn([$expectedArray]);

    $trait = $this->getMockBuilder(
      UserTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $trait
      ->method('getContainer')
      ->willReturn(new Container([
        'database' => $database,
        'user_factory' => $factory
      ]));

    /** @var UserTrait $trait */
    $instances = $trait->getUsers(
      $where,
      $join,
      $columns
    );

    Assert::count($instances, 1);

    Assert::eq(
      $instances[0],
      $expectedInstance
    );

    $arrays = $trait->getUsers(
      $where,
      $join,
      $columns,
      true
    );

    Assert::count($arrays, 1);

    Assert::same(
      $arrays[0],
      $expectedArray
    );
  }

  /**
   * Testing `hasUsers` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanGetUsers
   */
  public function testCanHasUsers($id)
  {
    Assert::true(
      $this->instance->hasUsers([
        'user_id[~]' => $id
      ])
    );

    Assert::false(
      $this->instance->hasUsers([
        'user_id[~]' => '-20'
      ])
    );

    return $id;
  }

  /**
   * Testing `countUsers` method.
   *
   * @param   string $id
   * @return  mixed
   * @throws  \Exception
   * @depends testCanHasUsers
   */
  public function testCanCountUsers($id)
  {
    $count = $this->instance->countUsers([
      'user_id' => $id
    ]);

    Assert::same($count, 1);

    return $id;
  }

  /**
   * Testing `countUsers` method with join.
   *
   * @throws \Exception
   */
  public function testCanCountUsersWithJoin()
  {
    $where   = ['1'];
    $join    = ['2'];
    $columns = ['3'];

    $expected = 5;

    $database = $this->getMockBuilder(Medoo::class)
      ->disableOriginalConstructor()
      ->getMock();

    $database
      ->expects($this->once())
      ->method('count')
      ->with(
        'users',
        $join,
        $columns,
        $where
      )->willReturn($expected);

    $trait = $this->getMockBuilder(
      UserTrait::class
    )->setMethods([
      'getContainer'
    ])->getMockForTrait();

    $trait
      ->method('getContainer')
      ->willReturn(new Container([
        'database' => $database,
      ]));

    /** @var UserTrait $trait */
    $result = $trait->countUsers(
      $where,
      $join,
      $columns
    );

    Assert::same(
      $result,
      $expected
    );
  }

  /**
   * Testing `removeUser` method.
   *
   * @param   string $id
   * @throws  NotFoundException
   * @depends testCanCountUsers
   */
  public function testCanRemoveUser($id)
  {
    $isRemoved = $this->instance->removeUser($id);

    Assert::true($isRemoved);
  }

  /**
   * Testing `removeUsers` method.
   *
   * @throws \Exception
   */
  public function testCanRemoveUsers()
  {
    $user = new User();
    $user->name = Utils::randomString();
    $user->email = Utils::randomString();

    $addedUser = $this->instance->addUser($user);

    $count = $this->instance->removeUsers([
      'user_id' => $addedUser->id
    ]);

    Assert::same($count, 1);
  }

  /**
   * Testing `getUser` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotGetUser()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->getUser('-20');
  }

  /**
   * Testing `updateUser` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotUpdateUser()
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->instance->updateUser(new User());
  }

  /**
   * Testing `removeUser` method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotRemoveUser()
  {
    $this->expectException(NotFoundException::class);

    $this->instance->removeUser('-20');
  }
}
