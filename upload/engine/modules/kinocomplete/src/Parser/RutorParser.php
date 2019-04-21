<?php

namespace Kinocomplete\Parser;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Exception\ParsingException;
use Symfony\Component\DomCrawler\Crawler;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Utils\Utils;
use Webmozart\PathUtil\Path;

class RutorParser extends DefaultService
{
  /**
   * Parse search page.
   *
   * @param  string $contents
   * @return array
   * @throws NotFoundException
   */
  public function parseSearchPage ($contents)
  {
    $source = $this->container->get('rutor_source');

    $crawler = new Crawler($contents);

    $itemsFilter = function (Crawler $node) use ($source) {

      // Is it downloadable?
      try {

        $fileLink = Path::join(
          $source->getScheme(),
          $source->getHost(),
          $node->filter('a.downgif')->attr('href')
        );

      } catch (\InvalidArgumentException $exception) {

        return null;
      }

      // Parsing.
      try {

        $title    = $node->filter('a')->last()->text();
        $fileSize = $node->filter('td')->eq(3)->text();
        $leeches  = $node->filter('span.red')->text();
        $seeds    = $node->filter('span.green')->text();

        $pageLink = Path::join(
          $source->getScheme(),
          $source->getHost(),
          $node->filter('a')->last()->attr('href')
        );

      } catch (\InvalidArgumentException $exception) {

        throw new ParsingException(
          'Не удалось разобрать ответ Rutor.'
        );
      }

      $idMatches = [];

      preg_match(
        '/torrent\/(\d+?)\//',
        $pageLink,
        $idMatches
      );

      if (
        !array_key_exists(1, $idMatches) ||
        !$idMatches[1]
      ) throw new ParsingException(
        'Не удалось разобрать ответ Rutor.'
      );

      $id = $idMatches[1];

      return [
        'id'          => $id,
        'title'       => $title,
        'page_link'   => $pageLink,
        'magnet_link' => '',
        'file_link'   => $fileLink,
        'file_size'   => Utils::convertToBytes($fileSize),
        'seeds'       => (int) preg_replace('/[^\d]/', '', $seeds),
        'leeches'     => (int) preg_replace('/[^\d]/', '', $leeches)
      ];
    };

    $items = $crawler
      ->filter('#index tr:not(.backgr)')
      ->each($itemsFilter);

    $items = array_filter(
      $items
    );

    if (!count($items))
      throw new NotFoundException(
        'Материалов по данному запросу не найдено.'
      );

    return $items;
  }

  /**
   * Parse entity page.
   *
   * @param  string $contents
   * @return array
   * @throws NotFoundException
   * @throws ParsingException
   */
  public function parseEntityPage($contents)
  {
    $source = $this->container->get('rutor_source');

    $crawler = new Crawler($contents);

    try {

      // Field "title".
      $title = $crawler
        ->filter('h1')
        ->text();

      if (strpos($title, 'Страница пока') !== false)
        throw new NotFoundException(
          'Не удалось найти запрашиваемый материал.'
        );

      if (!$title)
        throw new \InvalidArgumentException(
          'Не удалось получить заголовок.'
        );

      // Field "magnet_link".
      $magnetLink = $crawler
        ->filter('#download a')
        ->eq(0)
        ->attr('href');

      if (strpos($magnetLink, 'magnet:') === false)
        throw new \InvalidArgumentException(
          'Не удалось получить Magnet-ссылку.'
        );

      // Field "file_link".
      $fileLink = $crawler
        ->filter('#download a')
        ->eq(1)
        ->attr('href');

      if (strpos($fileLink, '/') === false)
        throw new \InvalidArgumentException(
          'Не удалось получить торрент-файл.'
        );

      $fileLink = Path::join(
        $source->getScheme(),
        $source->getHost(),
        $fileLink
      );

      // Parse detail nodes.
      $details = $crawler
        ->filter('#details > tr')
        ->each(function ($node) {
          return $node;
        });

      if (count($details) < 8)
        throw new \InvalidArgumentException(
          'Недостаток полей таблицы "details".'
        );

      $details = array_reverse($details);

      // Field "file_size".
      $fileSize = $details[3]
        ->filter('td')
        ->last()
        ->text();

      $fileSize = preg_replace(
        '/\s+\(.+?\)/',
        '',
        $fileSize
      );

      if (!$fileSize)
        throw new \InvalidArgumentException(
          'Не удалось получить размер файла.'
        );

      $fileSize = Utils::convertToBytes(
        $fileSize
      );

      // Field "seeds".
      $seeds = $details[7]
        ->filter('td')
        ->last()
        ->text();

      $seeds = (int) preg_replace(
        '/[^\d]/',
        '',
        $seeds
      );

      if ($seeds === '' || $seeds === false)
        throw new \InvalidArgumentException(
          'Не удалось получить количество раздающих.'
        );

      // Field "leeches".
      $leeches = $details[6]
        ->filter('td')
        ->last()
        ->text();

      $leeches = (int) preg_replace(
        '/[^\d]/',
        '',
        $leeches
      );

      if ($leeches === '' || $leeches === false)
        throw new \InvalidArgumentException(
          'Не удалось получить количество скачивающих.'
        );

      // Collect data.
      $data = [
        'id'          => '',
        'title'       => $title,
        'page_link'   => '',
        'magnet_link' => $magnetLink,
        'file_link'   => $fileLink,
        'file_size'   => $fileSize,
        'seeds'       => $seeds,
        'leeches'     => $leeches
      ];

    } catch (\InvalidArgumentException $exception) {

      throw new ParsingException(sprintf(
        'Не удалось разобрать ответ Rutor: %s',
        $exception->getMessage()
      ));
    }

    return $data;
  }
}
