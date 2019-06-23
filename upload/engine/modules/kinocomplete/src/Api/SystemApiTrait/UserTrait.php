<?php

namespace Kinocomplete\Api\SystemApiTrait;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\Container;
use Kinocomplete\User\UserFactory;
use Webmozart\Assert\Assert;
use Kinocomplete\User\User;
use Medoo\Medoo;

trait UserTrait {

  /**
   * Get container.
   *
   * @return Container
   */
  abstract public function getContainer();

  /**
   * Add user.
   *
   * @param  User $user
   * @return User
   * @throws \Exception
   */
  public function addUser(
    User $user
  ) {
    Assert::notEmpty($user);

    /** @var UserFactory $userFactory */
    $userFactory = $this->getContainer()->get('user_factory');

    $data = $userFactory->toDatabaseArray(
      $user
    );

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $database->insert(
      'users',
      $data
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При добавлении пользователя произошла ошибка: '. $error[2]
      );

    $user->id = $database->id();

    return $user;
  }

  /**
   * Get user.
   *
   * @param  string $id
   * @return User
   * @throws NotFoundException
   */
  public function getUser($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $items = $database->select(
      'users',
      '*',
      ['user_id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе пользователя произошла ошибка: '. $error[2]
      );

    $item = current($items);

    if (!$item)
      throw new NotFoundException(
        'Не удалось найти пользователя.'
      );

    /** @var UserFactory $userFactory */
    $userFactory = $this->getContainer()->get('user_factory');

    return $userFactory->fromDatabaseArray($item);
  }

  /**
   * Update user.
   *
   * @param  User $user
   * @return User
   * @throws NotFoundException
   */
  public function updateUser(
    User $user
  ) {
    Assert::stringNotEmpty(
      $user->id,
      'Идентификатор обновляемого пользователя не найден.'
    );

    /** @var UserFactory $userFactory */
    $userFactory = $this->getContainer()->get('user_factory');

    $data = $userFactory->toDatabaseArray(
      $user
    );

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->update(
      'users',
      $data,
      ['user_id' => $user->id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При обновлении пользователя произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти пользователя для обновления.'
      );

    return $user;
  }

  /**
   * Remove user.
   *
   * @param  string $id
   * @return bool
   * @throws NotFoundException
   */
  public function removeUser($id)
  {
    Assert::stringNotEmpty($id);

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->delete(
      'users',
      ['user_id' => $id]
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении пользователя произошла ошибка: '. $error[2]
      );

    if (!$statement->rowCount())
      throw new NotFoundException(
        'Не удалось найти пользователя для удаления.'
      );

    return true;
  }

  /**
   * Get users.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @param  boolean $raw
   * @return array
   * @throws \Exception
   */
  public function getUsers(
    array $where = [],
    array $join = [],
    $columns = '*',
    $raw = false
  ) {
    if (!is_array($columns) && !is_string($columns))
      throw new \InvalidArgumentException(sprintf(
        'Argument 2 must be an array or string, got %s.',
        $columns
      ));

    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    if ($join) {

      $items = $database->select(
        'users',
        $join,
        $columns,
        $where
      );

    } else {

      $items = $database->select(
        'users',
        $columns,
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При запросе пользователей произошла ошибка: '. $error[2]
      );

    if (!$raw) {

      /** @var UserFactory $userFactory */
      $userFactory = $this->getContainer()->get('user_factory');

      return array_map(
        [$userFactory, 'fromDatabaseArray'],
        $items
      );

    } else {

      return $items;
    }
  }

  /**
   * Has users.
   *
   * @param  array $where
   * @return bool
   * @throws \Exception
   */
  public function hasUsers(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $has = $database->has(
      'users',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При проверке наличия пользователей произошла ошибка: '. $error[2]
      );

    return $has;
  }

  /**
   * Remove users.
   *
   * @param  array $where
   * @return int
   * @throws \Exception
   */
  public function removeUsers(
    array $where = []
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    $statement = $database->delete(
      'users',
      $where
    );

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении пользователей произошла ошибка: '. $error[2]
      );

    return $statement->rowCount();
  }

  /**
   * Count users.
   *
   * @param  array $where
   * @param  array $join
   * @param  array|string $columns
   * @return int
   * @throws \Exception
   */
  public function countUsers(
    array $where = [],
    array $join = [],
    $columns = '*'
  ) {
    /** @var Medoo $database */
    $database = $this->getContainer()->get('database');

    if ($join) {

      $count = $database->count(
        'users',
        $join,
        $columns,
        $where
      );

    } else {

      $count = $database->count(
        'users',
        $where
      );
    }

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При подсчете пользователей произошла ошибка: '. $error[2]
      );

    return $count;
  }
}
