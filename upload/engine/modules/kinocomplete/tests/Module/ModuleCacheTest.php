<?php

namespace Kinocomplete\Test\Module;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class ModuleCacheTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "addApiToken" method.
   */
  public function testCanAddApiToken()
  {
    $instance = $this->getContainer()->get('module_cache');

    $systemCacheDir = $this->getContainer()->get('system_cache_dir');
    $moduleName = $this->getContainer()->get('module_name');
    $fileName = $instance->apiTokenFile;

    $filePath = Path::join(
      $systemCacheDir,
      $moduleName,
      $fileName
    );

    $token = Utils::randomString();
    $origin = Utils::randomString();

    $instance->addApiToken(
      $token,
      $origin
    );

    Assert::fileExists($filePath);

    $json = file_get_contents($filePath);
    $array = json_decode($json, 1);

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

    Assert::true($founded);

    unlink($filePath);
  }

  public function testCanClean()
  {
    $systemCacheDir = $this->getContainer()->get('system_cache_dir');
    $moduleName = $this->getContainer()->get('module_name');

    $workingDir = Path::join(
      $systemCacheDir,
      $moduleName
    );

    Utils::resolveDir($workingDir);

    Assert::true(
      file_exists($workingDir)
    );

    $instance = $this->getContainer()->get('module_cache');

    $instance->clean();

    Assert::false(
      file_exists($workingDir)
    );
  }

  /**
   * Testing "hasApiToken" method.
   */
  public function testCanHasApiToken()
  {
    $instance = $this->getContainer()->get('module_cache');

    $systemCacheDir = $this->getContainer()->get('system_cache_dir');
    $moduleName = $this->getContainer()->get('module_name');
    $fileName = $instance->apiTokenFile;

    $filePath = Path::join(
      $systemCacheDir,
      $moduleName,
      $fileName
    );

    $token = Utils::randomString();
    $origin = Utils::randomString();

    $has = $instance->hasApiToken(
      $token,
      $origin
    );

    Assert::false($has);

    $instance->addApiToken(
      $token,
      $origin
    );

    $has = $instance->hasApiToken(
      $token,
      $origin
    );

    Assert::true($has);

    unlink($filePath);
  }

  /**
   * Testing "removeApiToken" method.
   */
  public function testCanRemoveApiToken()
  {
    $instance = $this->getContainer()->get('module_cache');

    $token = Utils::randomString();
    $origin = Utils::randomString();

    $has = $instance->hasApiToken(
      $token,
      $origin
    );

    Assert::false($has);

    $instance->addApiToken(
      $token,
      $origin
    );

    $has = $instance->hasApiToken(
      $token,
      $origin
    );

    Assert::true($has);

    $instance->removeApiToken(
      $token,
      $origin
    );

    $has = $instance->hasApiToken(
      $token,
      $origin
    );

    Assert::false($has);

    $systemCacheDir = $this->getContainer()->get('system_cache_dir');
    $moduleName = $this->getContainer()->get('module_name');
    $fileName = $instance->apiTokenFile;

    $filePath = Path::join(
      $systemCacheDir,
      $moduleName,
      $fileName
    );

    unlink($filePath);
  }
}