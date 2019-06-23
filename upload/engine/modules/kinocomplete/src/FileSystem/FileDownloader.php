<?php

namespace Kinocomplete\FileSystem;

use Kinocomplete\Service\DefaultService;
use Psr\Container\ContainerInterface;
use Kinocomplete\Utils\Utils;
use Kinocomplete\Video\Video;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class FileDownloader extends DefaultService
{
  /**
   * @var string
   */
  protected $workingDir;

  /**
   * FileDownloader constructor.
   *
   * @param ContainerInterface $container
   */
  public function __construct(ContainerInterface $container)
  {
    parent::__construct($container);

    $this->workingDir = $container->get('system_upload_dir');
  }

  /**
   * Checking path is local.
   *
   * @param  string $path
   * @return bool
   */
  public function isLocalPath($path)
  {
    Assert::stringNotEmpty($path);

    $matches = [];

    preg_match(
      '/^(http:|https:)?\/\//',
      $path,
      $matches
    );

    return !$matches;
  }

  /**
   * Local path to relative url.
   *
   * @param  string $path
   * @return mixed
   */
  public function localPathToRelativeUrl($path)
  {
    Assert::stringNotEmpty($path);

    $path = realpath($path);

    $systemRootDir = realpath(
      $this->container->get('system_root_dir')
    );

    $relativeUrl = str_replace(
      $systemRootDir,
      '',
      $path
    );

    return str_replace(
      '\\',
      '/',
      $relativeUrl
    );
  }

  /**
   * Download image by link.
   *
   * @param  string $link
   * @return string
   * @throws \Exception
   */
  public function downloadImageByLink($link)
  {
    Assert::stringNotEmpty(
      $link,
      'Ссылка загружаемого изображения должна быть не пустой строкой.'
    );

    $targetDir = Path::join(
      $this->workingDir,
      $this->container->get('images_download_path')
    );

    $targetDir = Utils::resolveDir($targetDir);

    $extension = pathinfo($link, PATHINFO_EXTENSION);
    $extension = $extension ?: 'jpg';

    $filePath = Path::join(
      $targetDir,
      md5($link) .'.'. $extension
    );

    $fileExists = file_exists($filePath);

    $overwrite = $this->container->get('images_overwrite_download');

    if ($fileExists && !$overwrite)
      return $filePath;

    file_put_contents(
      $filePath,
      fopen($link, 'r')
    );

    return $filePath;
  }

  /**
   * Download torrent by link.
   *
   * @param  string $link
   * @return string
   * @throws \Exception
   */
  public function downloadTorrentByLink($link)
  {
    Assert::stringNotEmpty(
      $link,
      'Ссылка загружаемого торрент-файла должна быть не пустой строкой.'
    );

    $targetDir = Path::join(
      $this->workingDir,
      $this->container->get('torrents_download_path')
    );

    $targetDir = Utils::resolveDir($targetDir);

    $extension = pathinfo($link, PATHINFO_EXTENSION);
    $extension = $extension ?: 'torrent';

    $filePath = Path::join(
      $targetDir,
      md5($link) .'.'. $extension
    );

    $fileExists = file_exists($filePath);

    $overwrite = $this->container->get('torrents_overwrite_download');

    if ($fileExists && !$overwrite)
      return $filePath;

    file_put_contents(
      $filePath,
      fopen($link, 'r')
    );

    return $filePath;
  }

  /**
   * Download images of video and
   * update their links.
   *
   * @param  Video $video
   * @return Video
   * @throws \Exception
   */
  public function downloadVideoImages(
    Video $video
  ) {
    // Field "poster".
    if ($video->poster) {

      $isExternal = !$this->isLocalPath(
        $video->poster
      );

      if ($isExternal) {

        $filePath = $this->downloadImageByLink(
          $video->poster
        );

        $video->poster = $this->localPathToRelativeUrl(
          $filePath
        );
      }
    }

    // Field "thumbnail".
    if ($video->thumbnail) {

      $isExternal = !$this->isLocalPath(
        $video->thumbnail
      );

      if ($isExternal) {

        $filePath = $this->downloadImageByLink(
          $video->thumbnail
        );

        $video->thumbnail = $this->localPathToRelativeUrl(
          $filePath
        );
      }
    }

    // Field "screenshots".
    if ($video->screenshots) {

      $localScreenshots = [];

      foreach ($video->screenshots as $screenshot) {

        $isExternal = !$this->isLocalPath(
          $screenshot
        );

        if ($isExternal) {

          $filePath = $this->downloadImageByLink(
            $screenshot
          );

          $localScreenshots[] = $this->localPathToRelativeUrl(
            $filePath
          );

        } else {

          $localScreenshots[] = $screenshot;
        }
      }

      $video->screenshots = $localScreenshots;
    }

    return $video;
  }

  /**
   * Download torrent-files of video and
   * update their links.
   *
   * @param  Video $video
   * @return Video
   * @throws \Exception
   */
  public function downloadVideoTorrents(
    Video $video
  ) {
    // Field "torrentFile".
    if ($video->torrentFile) {

      $isExternal = !$this->isLocalPath(
        $video->torrentFile
      );

      if ($isExternal) {

        $filePath = $this->downloadTorrentByLink(
          $video->torrentFile
        );

        $video->torrentFile = $this->localPathToRelativeUrl(
          $filePath
        );
      }
    }

    return $video;
  }
}
