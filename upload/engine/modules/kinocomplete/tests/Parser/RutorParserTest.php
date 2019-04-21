<?php

namespace Kinocomplete\Test\Post;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Source\Source;
use Kinocomplete\Tests\TestTrait\ContainerTrait;
use Kinocomplete\Parser\RutorParser;
use PHPUnit\Framework\TestCase;
use Webmozart\Assert\Assert;

class RutorParserTest extends TestCase
{
  use ContainerTrait;

  /**
   * @var RutorParser
   */
  public $instance;

  /**
   * RutorParserTest constructor.
   *
   * @param null   $name
   * @param array  $data
   * @param string $dataName
   */
  public function __construct($name = null, array $data = [], $dataName = '')
  {
    parent::__construct($name, $data, $dataName);

    $this->instance = new RutorParser(
      $this->getContainer()
    );
  }

  /**
   * Instantiation testing.
   */
  public function testInstanceCreated()
  {
    Assert::isInstanceOf(
      $this->instance,
      RutorParser::class
    );
  }

  /**
   * Testing "parseSearchPage" method.
   *
   * @throws NotFoundException
   */
  public function testCanParseSearchPage()
  {
    $contents = file_get_contents(
      FIXTURES_DIR .'/rutor-pages/search.txt'
    );

    $result = $this->instance->parseSearchPage($contents);

    /** @var Source $source */
    $source = $this->getContainer()->get('rutor_source');

    $scheme = $source->getScheme();
    $host = $source->getHost();

    $expected = [
      'id'          => '693795',
      'title'       => 'Игра престолов / Game of Thrones',
      'page_link'   => "${scheme}${host}/torrent/693795/igra-prestolov_game-of-thrones",
      'magnet_link' => '',
      'file_link'   => "${scheme}${host}/parse/d.rutor.org/download/693795",
      'file_size'   => 698246758,
      'seeds'       => 5249,
      'leeches'     => 906
    ];

    Assert::eq(
      $result[0],
      $expected
    );
  }

  /**
   * Testing "parseSearchPage" method exceptions.
   *
   * @throws NotFoundException
   */
  public function testCannotParseSearchPage()
  {
    $this->expectException(NotFoundException::class);

    $contents = file_get_contents(
      FIXTURES_DIR .'/rutor-pages/not-found.txt'
    );

    $this->instance->parseSearchPage($contents);
  }

  /**
   * Testing "parseEntityPage" method.
   *
   * @throws NotFoundException
   * @throws \Kinocomplete\Exception\ParsingException
   */
  public function testCanParseEntityPage()
  {
    $contents = file_get_contents(
      FIXTURES_DIR .'/rutor-pages/entity.txt'
    );

    $result = $this->instance->parseEntityPage($contents);

    /** @var Source $source */
    $source = $this->getContainer()->get('rutor_source');

    $scheme = $source->getScheme();
    $host = $source->getHost();

    $magnetLink = 'magnet:?xt=urn:btih:13ebd86aea3ee9a585878a3'
      .'07f9c93622467fa93&dn=rutor.org_%D0%9C%D0%B0%D1%80%D1%8'
      .'1%D0%B8%D0%B0%D0%BD%D0%B8%D0%BD+%2F+The+Martian+%28201'
      .'5%29+WEB-DLRip+%D0%BE%D1%82+Scarabey+%7C+%D0%A7%D0%B8%'
      .'D1%81%D1%82%D1%8B%D0%B9+%D0%B7%D0%B2%D1%83%D0%BA&tr=ud'
      .'p://opentor.org:2710&tr=udp://bt.rutor.org:2710&tr=ret'
      .'racker.local/announce';

    $expected = [
      'id'          => '',
      'title'       => 'Марсианин / The Martian (2015) WEB-DLRip от Scarabey | Чистый звук',
      'page_link'   => '',
      'magnet_link' => $magnetLink,
      'file_link'   => "${scheme}${host}/parse/d.rutor.org/download/478003",
      'file_size'   => 1556925644,
      'seeds'       => 1393,
      'leeches'     => 29
    ];

    Assert::eq(
      $result,
      $expected
    );
  }

  /**
   * Testing "parseEntityPage" method exceptions.
   *
   * @throws NotFoundException
   * @throws \Kinocomplete\Exception\ParsingException
   */
  public function testCannotParseEntityPage()
  {
    $this->expectException(NotFoundException::class);

    $contents = file_get_contents(
      FIXTURES_DIR .'/rutor-pages/not-found.txt'
    );

    $this->instance->parseEntityPage($contents);
  }
}
