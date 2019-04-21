<?php

namespace Kinocomplete\FeedLoader;

use Kinocomplete\Source\Source;
use Kinocomplete\Api\SystemApi;
use Webmozart\PathUtil\Path;
use Webmozart\Assert\Assert;
use Kinocomplete\Feed\Feed;

class FeedProcessor
{
  /**
   * Source instance.
   *
   * @var Source
   */
  protected $source;

  /**
   * SystemApi instance.
   *
   * @var SystemApi
   */
  protected $systemApi;

  /**
   * Working dir.
   *
   * @var string
   */
  protected $workingDir;

  /**
   * FeedProcessor constructor.
   *
   * @param Source $source
   * @param SystemApi $systemApi
   * @param string $workingDir
   */
  public function __construct(
    Source $source,
    SystemApi $systemApi,
    $workingDir
  ) {
    Assert::fileExists(
      $workingDir,
      'Рабочая директория фидов отсутствует.'
    );

    $this->source = $source;
    $this->systemApi = $systemApi;
    $this->workingDir = $workingDir;
  }

  /**
   * Download feed.
   *
   * @param  Feed $feed
   * @param  callable|null $onProgress
   * @return string
   */
  public function downloadFeed(
    Feed $feed,
    callable $onProgress = null
  ) {
    $filePath = Path::join(
      $this->workingDir,
      $feed->getFileName()
    );

    $this->source->getApi()->downloadFeed(
      $feed,
      $filePath,
      $onProgress
    );

    return $filePath;
  }

  /**
   * Remove feed file.
   *
   * @param  Feed $feed
   * @throws \Exception
   */
  public function removeFeedFile(
    Feed $feed
  ) {
    $filePath = Path::join(
      $this->workingDir,
      $feed->getFileName()
    );

    Assert::fileExists(
      $filePath,
      'Файл фида назначенный на удаление не найден.'
    );

    if (!@unlink($filePath))
      throw new \Exception(
        'Не удалось удалить файл фида.'
      );
  }
}