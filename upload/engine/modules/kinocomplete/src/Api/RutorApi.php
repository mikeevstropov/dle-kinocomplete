<?php

namespace Kinocomplete\Api;

use Kinocomplete\Exception\UnexpectedResponseException;
use Kinocomplete\Exception\TooLargeResponseException;
use Kinocomplete\Exception\NotFoundException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Parser\RutorParser;
use GuzzleHttp\ClientInterface;
use Kinocomplete\Source\Source;
use GuzzleHttp\Psr7\Response;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use Kinocomplete\Feed\Feed;

class RutorApi extends DefaultService implements ApiInterface
{
  /**
   * Error handler.
   *
   * @param  TransferException $exception
   * @throws UnexpectedResponseException
   */
  public function errorHandler(
    TransferException $exception
  ) {
    if ($exception instanceof ConnectException)
      throw new UnexpectedResponseException(
        'Невозможно подключиться к Rutor.'
      );

    if ($exception instanceof ClientException)
      throw new UnexpectedResponseException(sprintf(
        'Неожиданный код ответа Rutor: %s',
        $exception->getCode()
      ));

    // Server exception.
    if ($exception instanceof ServerException)
      throw new UnexpectedResponseException(sprintf(
        'Неожиданный код ответа Rutor: %s',
        $exception->getCode()
      ));

    throw $exception;
  }

  /**
   * Access checking.
   *
   * @return bool
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   */
  public function accessChecking()
  {
    /** @var Source $source */
    $source = $this->container->get('rutor_source');

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      'search/'
    );

    $response = null;

    try {

      $response = $client->request('GET', $url);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $contents = $response->getBody()->__toString();

    $hasForm = strpos($contents, 'id="inputtext_id"');

    if (!$hasForm)
      throw new UnexpectedResponseException(
        'Неожиданный ответ Rutor.'
      );

    return true;
  }

  /**
   * Get videos.
   *
   * @param  string $title
   * @return array
   * @throws NotFoundException
   * @throws TooLargeResponseException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   */
  public function getVideos($title)
  {
    Assert::string($title);

    if (strlen($title) < 3)
      throw new TooLargeResponseException(
        'Поисковой запрос слишком короткий.'
      );

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    /** @var Source $source */
    $source = $this->container->get('rutor_source');

    $filter = '0/0/000/2';

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      'search',
      $filter,
      $title .'/'
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $body = $response->getBody()->__toString();

    /** @var RutorParser $parser */
    $parser = $this->container->get('rutor_parser');

    $items = array_slice(
      $parser->parseSearchPage($body),
      0,
      19
    );

    return array_map(
      $source->getVideoFactory(),
      $items
    );
  }

  /**
   * Get video.
   *
   * @param  string $id
   * @return \Kinocomplete\Video\Video
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\ParsingException
   */
  public function getVideo($id)
  {
    Assert::stringNotEmpty(
      $id,
      'Идентификатор запрашиваемого материала отсутствует.'
    );

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    /** @var Source $source */
    $source = $this->container->get('rutor_source');

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      'torrent',
      $id .'/'
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $body = $response->getBody()->__toString();

    /** @var RutorParser $parser */
    $parser = $this->container->get('rutor_parser');

    $data = $parser->parseEntityPage($body);

    $data['id'] = $id;
    $data['page_link'] = $url;

    $videoFactory = $source->getVideoFactory();

    return $videoFactory($data);
  }

  /**
   * Download feed.
   *
   * @param  Feed $feed
   * @param  string $filePath
   * @param  callable $onProgress
   * @return string|void
   */
  public function downloadFeed(Feed $feed,
    $filePath,
    callable $onProgress
  ) {
    // TODO: Implement downloadFeed() method.
  }
}
