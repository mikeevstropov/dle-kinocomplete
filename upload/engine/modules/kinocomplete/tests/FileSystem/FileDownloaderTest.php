<?php

namespace Kinocomplete\Test\FileSystem;

use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\FileSystem\FileDownloader;
use Kinocomplete\Container\Container;
use Kinocomplete\Video\Video;
use PHPUnit\Framework\TestCase;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;

class FileDownloaderTest extends TestCase
{
  use ContainerTrait;

  /**
   * Testing "isLocalPath" method.
   */
  public function testCanIsLocalPath()
  {
    /** @var FileDownloader $instance */
    $instance = $this->getContainer()->get('file_downloader');

    Assert::false(
      $instance->isLocalPath('http://path')
    );

    Assert::false(
      $instance->isLocalPath('https://path')
    );

    Assert::false(
      $instance->isLocalPath('//path')
    );

    Assert::true(
      $instance->isLocalPath('/path')
    );

    Assert::true(
      $instance->isLocalPath('path')
    );

    Assert::true(
      $instance->isLocalPath('path/path')
    );
  }

  /**
   * Testing "localPathToRelativeUrl" method.
   *
   * @throws \Exception
   */
  public function testCanLocalPathToRelativeUrl()
  {
    /** @var FileDownloader $instance */
    $instance = $this->getContainer()->get('file_downloader');

    $cacheDir = $this->getContainer()->get('system_cache_dir');

    Assert::same(
      $instance->localPathToRelativeUrl($cacheDir),
      '/engine/cache'
    );
  }

  /**
   * Testing "downloadImageByLink" method.
   *
   * @throws \Exception
   */
  public function testCanDownloadImageByLink()
  {
    $path = Utils::randomString();

    $container = new Container([
      'system_upload_dir' => $this->getContainer()->get('system_upload_dir'),
      'images_download_path' => $path,
      'images_overwrite_download' => 0
    ]);

    $link = 'https://dummyimage.com/600x400/000/fff';

    $instance = new FileDownloader($container);

    $downloadedFile = $instance->downloadImageByLink($link);

    Assert::fileExists($downloadedFile);

    unlink($downloadedFile);
    rmdir(dirname($downloadedFile));
  }

  /**
   * Testing "downloadTorrentByLink" method.
   *
   * @throws \Exception
   */
  public function testCanDownloadTorrentByLink()
  {
    $path = Utils::randomString();

    $container = new Container([
      'system_upload_dir' => $this->getContainer()->get('system_upload_dir'),
      'torrents_download_path' => $path,
      'torrents_overwrite_download' => 0
    ]);

    $link = 'https://dummyimage.com/600x400/000/fff';

    $instance = new FileDownloader($container);

    $downloadedFile = $instance->downloadTorrentByLink($link);

    Assert::fileExists($downloadedFile);

    unlink($downloadedFile);
    rmdir(dirname($downloadedFile));
  }

  /**
   * Testing "downloadVideoImages" method.
   *
   * @throws \Exception
   */
  public function testCanDownloadVideoImages()
  {
    $link = 'https://dummyimage.com/600x400/000/fff';

    $video = new Video();
    $video->poster = $link;

    $path = Utils::randomString();

    $container = new Container([
      'system_root_dir' => $this->getContainer()->get('system_root_dir'),
      'system_upload_dir' => $this->getContainer()->get('system_upload_dir'),
      'images_download_path' => $path,
      'images_overwrite_download' => 0,
    ]);

    $instance = new FileDownloader($container);
    $instance->downloadVideoImages($video);

    Assert::notEq(
      $video->poster,
      $link
    );

    $resolvedPath = Path::join(
      $this->getContainer()->get('system_root_dir'),
      $video->poster
    );

    Assert::fileExists(
      $resolvedPath
    );

    unlink($resolvedPath);
    rmdir(dirname($resolvedPath));
  }

  /**
   * Testing "downloadVideoTorrents" method.
   *
   * @throws \Exception
   */
  public function testCanDownloadVideoTorrents()
  {
    $link = 'https://dummyimage.com/600x400/000/fff';

    $video = new Video();
    $video->torrentFile = $link;

    $path = Utils::randomString();

    $container = new Container([
      'system_root_dir' => $this->getContainer()->get('system_root_dir'),
      'system_upload_dir' => $this->getContainer()->get('system_upload_dir'),
      'torrents_download_path' => $path,
      'torrents_overwrite_download' => 0,
    ]);

    $instance = new FileDownloader($container);
    $instance->downloadVideoTorrents($video);

    Assert::notEq(
      $video->torrentFile,
      $link
    );

    $resolvedPath = Path::join(
      $this->getContainer()->get('system_root_dir'),
      $video->torrentFile
    );

    Assert::fileExists(
      $resolvedPath
    );

    unlink($resolvedPath);
    rmdir(dirname($resolvedPath));
  }
}
