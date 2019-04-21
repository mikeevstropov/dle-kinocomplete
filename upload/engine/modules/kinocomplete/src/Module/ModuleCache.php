<?php

namespace Kinocomplete\Module;

use Kinocomplete\Service\DefaultService;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class ModuleCache extends DefaultService
{
  /**
   * @var string
   */
  public $apiTokenFile = 'api-token.json';

  /**
   * Get working dir.
   *
   * @return string
   * @throws \Exception
   */
  public function getWorkingDir()
  {
    $systemCacheDir = $this->container->get('system_cache_dir');
    $moduleName = $this->container->get('module_name');

    $workingDir = Path::join(
      $systemCacheDir,
      $moduleName
    );

    return Utils::resolveDir(
      $workingDir
    );
  }

  /**
   * Clean.
   *
   * @return bool
   * @throws \Exception
   */
  public function clean()
  {
    $systemCacheDir = $this->container->get('system_cache_dir');
    $moduleName = $this->container->get('module_name');

    $workingDir = Path::join(
      $systemCacheDir,
      $moduleName
    );

    if (!file_exists($workingDir))
      return false;

    Assert::writable(
      $workingDir,
      'Директория кэша модуля недоступна для записи.'
    );

    $removed = Utils::removeDir($workingDir);

    if (!$removed)
      throw new \Exception(
        'Невозможно удалить директорию кэша модуля.'
      );

    return true;
  }

  /**
   * Add api token.
   *
   * @param  string $token
   * @param  string $origin
   * @return bool
   * @throws \Exception
   */
  public function addApiToken(
    $token,
    $origin
  ) {
    Assert::stringNotEmpty(
      $token,
      'Api-токен должен быть не пустой строкой.'
    );
    
    Assert::stringNotEmpty(
      $origin,
      'Принадлежность Api-токена должна быть не пустой строкой.'
    );

    $filePath = Path::join(
      $this->getWorkingDir(),
      $this->apiTokenFile
    );

    if (!file_exists($filePath))
      file_put_contents($filePath, '[]');

    Assert::readable(
      $filePath,
      'Директория кэша Api-токенов недоступна для чтения.'
    );

    $json = file_get_contents(
      $filePath
    );

    $array = json_decode($json, true);

    Assert::isArray(
      $array,
      'Некорректное содержимое кэш-файла Api-токенов.'
    );

    $founded = false;

    foreach ($array as $item) {

      if (
        array_key_exists('token', $item) &&
        array_key_exists('origin', $item) &&
        $item['token'] === $token &&
        $item['origin'] === $origin
      ) {
        $founded = true;
        break;
      }
    }

    if ($founded)
      return false;

    $array[] = [
      'token' => $token,
      'origin' => $origin
    ];

    Assert::writable(
      $filePath,
      'Директория кэша Api-токенов недоступна для записи.'
    );

    file_put_contents(
      $filePath,
      json_encode($array)
    );

    return true;
  }

  /**
   * Has api token.
   *
   * @param  string $token
   * @param  string $origin
   * @return bool
   * @throws \Exception
   */
  public function hasApiToken(
    $token,
    $origin
  ) {
    Assert::stringNotEmpty(
      $token,
      'Api-токен должен быть не пустой строкой.'
    );

    Assert::stringNotEmpty(
      $origin,
      'Принадлежность Api-токена должна быть не пустой строкой.'
    );

    $filePath = Path::join(
      $this->getWorkingDir(),
      $this->apiTokenFile
    );

    if (!file_exists($filePath))
      return false;

    Assert::readable(
      $filePath,
      'Директория кэша Api-токенов недоступна для чтения.'
    );

    $json = file_get_contents(
      $filePath
    );

    $array = json_decode($json, true);

    Assert::isArray(
      $array,
      'Некорректное содержимое кэш-файла Api-токенов.'
    );

    $founded = false;

    foreach ($array as $item) {

      if (
        array_key_exists('token', $item) &&
        array_key_exists('origin', $item) &&
        $item['token'] === $token &&
        $item['origin'] === $origin
      ) {
        $founded = true;
        break;
      }
    }

    return $founded;
  }

  /**
   * Remove api token.
   *
   * @param  string $token
   * @param  string $origin
   * @return bool
   * @throws \Exception
   */
  public function removeApiToken(
    $token,
    $origin
  ) {
    Assert::stringNotEmpty(
      $token,
      'Api-токен должен быть не пустой строкой.'
    );

    Assert::stringNotEmpty(
      $origin,
      'Принадлежность Api-токена должна быть не пустой строкой.'
    );

    $has = $this->hasApiToken(
      $token,
      $origin
    );

    if (!$has)
      return false;

    $filePath = Path::join(
      $this->getWorkingDir(),
      $this->apiTokenFile
    );

    Assert::readable(
      $filePath,
      'Директория кэша Api-токенов недоступна для чтения.'
    );

    $json = file_get_contents(
      $filePath
    );

    $array = json_decode($json, true);

    Assert::isArray(
      $array,
      'Некорректное содержимое кэш-файла Api-токенов.'
    );

    $newArray = [];

    foreach ($array as $item) {

      if (
        array_key_exists('token', $item) &&
        array_key_exists('origin', $item)
      ) {

        if (
          $item['token'] !== $token ||
          $item['origin'] !== $origin
        ) $newArray[] = $item;
      }
    }

    Assert::writable(
      $filePath,
      'Директория кэша Api-токенов недоступна для записи.'
    );

    file_put_contents(
      $filePath,
      json_encode($newArray)
    );

    return true;
  }
}