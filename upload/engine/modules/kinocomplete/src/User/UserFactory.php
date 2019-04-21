<?php

namespace Kinocomplete\User;

use Kinocomplete\Service\DefaultService;

class UserFactory extends DefaultService
{
  /**
   * From database array.
   *
   * @param  array $array
   * @return User
   */
  public function fromDatabaseArray(
    array $array
  ) {
    $user = new User();

    if (
      array_key_exists('user_id', $array)
      && $array['user_id']
    ) $user->id = $array['user_id'];

    if (
      array_key_exists('name', $array)
      && is_string($array['name'])
    ) $user->name = $array['name'];

    if (
      array_key_exists('email', $array)
      && is_string($array['email'])
    ) $user->email = $array['email'];

    return $user;
  }

  /**
   * To database array.
   *
   * @param  User $user
   * @return array
   */
  public function toDatabaseArray(
    User $user
  ) {
    $array = [
      'name' => $user->name,
      'email' => $user->email
    ];

    if ($user->id)
      $array['user_id'] = $user->id;

    return $array;
  }
}