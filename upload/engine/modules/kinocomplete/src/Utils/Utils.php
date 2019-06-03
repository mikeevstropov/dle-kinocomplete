<?php

namespace Kinocomplete\Utils;

use Psr\Container\ContainerInterface;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class Utils
{
  /**
   * Resolve string variables
   *
   * @param  string $string
   * @param  ContainerInterface|array $variables
   * @throws \Exception
   * @throws \InvalidArgumentException
   * @return string
   */
  static public function resolveStringVariables(
    $string,
    $variables
  ) {

    Assert::string($string);

    $regex = '/\{\{(.*?)\}\}/i';

    if (is_array($variables)) {

      $processor = function ($match) use ($variables) {
        return isset($variables[$match[1]])
          ? $variables[$match[1]]
          : $match[0];
      };

    } else if ($variables instanceof ContainerInterface) {

      $processor = function ($match) use ($variables) {
        return $variables->has($match[1])
          ? $variables->get($match[1])
          : $match[0];
      };

    } else {

      throw new \Exception(
        'Variables must be an array or instance of Psr\Container\ContainerInterface.'
      );
    }

    return preg_replace_callback(
      $regex,
      $processor,
      $string
    );
  }

  /**
   * Get tag name by system version
   *
   * @param  string|number $version
   * @throws \Exception
   * @return string
   */
  static public function getVersionTag(
    $version
  ) {

    if ($version >= '12.0')
      return '12-plus';

    if ($version >= '11.0')
      return '11-plus';

    if ($version >= '10.0')
      return '10-plus';

    if ($version >= '9.0')
      return '9-plus';

    if ($version >= '8.0')
      return '8-plus';

    throw new \Exception(
      "Версия DataLife Engine $version не поддерживается."
    );
  }

  /**
   * To capital case.
   *
   * @param  string $string
   * @param  string $encoding
   * @return string
   */
  static public function toCapitalCase(
    $string,
    $encoding = 'UTF-8'
  ) {
    $string = mb_strtolower($string);
    $strLen = mb_strlen($string, $encoding);
    $firstChar = mb_substr($string, 0, 1, $encoding);
    $then = mb_substr($string, 1, $strLen - 1, $encoding);
    return mb_strtoupper($firstChar, $encoding) . $then;
  }

  /**
   * Snake to camel case.
   *
   * @param  string $string
   * @return string
   */
  static public function snakeToCamel($string)
  {
    $string = str_replace('_', '', ucwords($string, '_'));

    return lcfirst($string);
  }

  /**
   * Hyphen to snake case.
   *
   * @param  string $string
   * @return string
   */
  static public function hyphenToSnake($string)
  {
    $string = str_replace('-', '_', $string);

    return $string;
  }

  /**
   * Get random string.
   *
   * @param int $length
   * @return bool|string
   */
  static public function randomString($length = 10)
  {
    Assert::greaterThanEq($length, 1);

    return substr(str_shuffle(MD5(microtime())), 0, $length);
  }

  /**
   * Resolve directory.
   *
   * @param  string $path
   * @return string
   * @throws \Exception
   */
  static public function resolveDir($path)
  {
    Assert::stringNotEmpty($path);

    if (!file_exists($path)) {

      $created = @mkdir($path, 0755, true);

      if (!$created)
        throw new \Exception(sprintf(
          'Не удалось создать директорию: %s',
          $path
        ));

    } else {

      Assert::readable(
        $path,
        'Директория недоступна для чтения: %s'
      );

      Assert::writable(
        $path,
        'Директория недоступна для записи: %s'
      );
    }

    return realpath($path);
  }

  /**
   * Remove dir recursively.
   *
   * @param  string $dirPath
   * @return bool
   */
  static public function removeDir($dirPath)
  {
    Assert::directory($dirPath);

    Assert::readable(
      $dirPath,
      'Удаляемая директория недоступна для чтения: %s'
    );

    $fileNames = array_diff(
      scandir($dirPath),
      ['.', '..']
    );

    foreach ($fileNames as $fileName) {

      $filePath = Path::join(
        $dirPath,
        $fileName
      );

      $writable = is_writable($filePath);

      if (!$writable) {

        $permitted = chmod($filePath, 0777);

        Assert::true($permitted, sprintf(
          'Нет прав для удаления файла: %s',
          $filePath
        ));
      }

      $removed = is_dir($filePath)
        ? self::removeDir($filePath)
        : unlink($filePath);

      Assert::true($removed, sprintf(
        'Не удалось удалить файл: %s',
        $filePath
      ));
    }

    $writable = is_writable($dirPath);

    if (!$writable) {

      $permitted = chmod($dirPath, 0777);

      Assert::true($permitted, sprintf(
        'Нет прав для удаления директории: %s',
        $dirPath
      ));
    }

    $removed = @rmdir($dirPath);

    Assert::true($removed, sprintf(
      'Не удалось удалить директорию: %s',
      $dirPath
    ));

    return $removed;
  }

  /**
   * Copy dir recursively.
   *
   * @param string $dirPath
   * @param string $targetPath
   */
  static public function copyDir($dirPath, $targetPath)
  {
    Assert::directory($dirPath);
    Assert::stringNotEmpty($targetPath);

    Assert::readable(
      $dirPath,
      'Копируемая директория недоступна для чтения: %s'
    );

    $resource = opendir($dirPath);
    $created = @mkdir($targetPath, 0755);

    Assert::true($created, sprintf(
      'Не удалось создать директорию: %s',
      $targetPath
    ));

    while (false !== ($fileName = readdir($resource))) {

      if ($fileName === '.' || $fileName === '..')
        continue;

      $filePath = $dirPath .'/'. $fileName;
      $targetFilePath = $targetPath .'/'. $fileName;

      if (is_dir($filePath)) {

        self::copyDir(
          $filePath,
          $targetFilePath
        );

      } else {

        copy(
          $filePath,
          $targetFilePath
        );
      }
    }

    closedir($resource);
  }

  /**
   * Convert to bytes.
   *
   * @param  $from
   * @return int
   */
  static public function convertToBytes ($from)
  {
    Assert::stringNotEmpty($from);

    $units  = array_flip(['B', 'KB', 'MB', 'GB', 'TB', 'PB']);
    $number = substr($from, 0, -2);
    $suffix = strtoupper(substr($from,-2));

    if(is_numeric(substr($suffix, 0, 1)))
      return (int) preg_replace('/[^\d]/', '', $from);

    $exponent = array_key_exists($suffix, $units)
      ? $units[$suffix]
      : null;

    if($exponent === null)
      return null;

    return (int) ($number * (1024 ** $exponent));
  }

  /**
   * Bytes to human.
   *
   * @param  int $size
   * @param  string $unit
   * @return string
   */
  static public function bytesToHuman ($size, $unit = "")
  {
    if( (!$unit && $size >= 1<<30) || $unit == "GB")
      return number_format($size/(1<<30),2) ." GB";

    if( (!$unit && $size >= 1<<20) || $unit == "MB")
      return number_format($size/(1<<20),2) ." MB";

    if( (!$unit && $size >= 1<<10) || $unit == "KB")
      return number_format($size/(1<<10),2) ." KB";

    return number_format($size)." bytes";
  }
}
