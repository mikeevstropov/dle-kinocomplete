<?php

namespace Kinocomplete\Video\FactoryTrait;

use Kinocomplete\Video\Video;
use Kinocomplete\Utils\Utils;
use Webmozart\Assert\Assert;

trait RutorFactoryTrait
{
  use FactoryTrait;

  /**
   * Create by Rutor.
   *
   * @param  array $data
   * @return Video
   * @throws \Throwable
   */
  public function fromRutor(
    array $data
  ) {
    Assert::isArray($data);

    if (!array_key_exists('id', $data) || !$data['id'])
      throw new \InvalidArgumentException(
        'Идентификатор видео отсутствует.'
      );

    $video = new Video();

    $video->id = (string) $data['id'];
    $video->origin = Video::RUTOR_ORIGIN;

    // Field "title".
    if (array_key_exists('title', $data)) {

      if (is_string($data['title']))
        $video->title = $data['title'];
    }

    // Field "magnetLink".
    if (array_key_exists('magnet_link', $data)) {

      if (is_string($data['magnet_link']))
        $video->magnetLink = $data['magnet_link'];
    }

    // Field "torrentFile".
    if (array_key_exists('file_link', $data)) {

      if (is_string($data['file_link']))
        $video->torrentFile = $data['file_link'];
    }

    // Field "torrentSize".
    if (array_key_exists('file_size', $data)) {

      if (is_int($data['file_size']))
        $video->torrentSize = Utils::bytesToHuman(
          $data['file_size']
        );
    }

    // Field "torrentSeeds".
    if (array_key_exists('seeds', $data)) {

      if (is_int($data['seeds']))
        $video->torrentSeeds = $data['seeds'];
    }

    // Field "torrentLeeches".
    if (array_key_exists('leeches', $data)) {

      if (is_int($data['leeches']))
        $video->torrentLeeches = $data['leeches'];
    }

    return $this->applyPatterns($video);
  }
}
