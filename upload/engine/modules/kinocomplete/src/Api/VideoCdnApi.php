<?php

namespace Kinocomplete\Api;

use Kinocomplete\Exception\UnexpectedResponseException;
use Kinocomplete\Exception\TooLargeResponseException;
use Kinocomplete\Exception\EmptyQueryException;
use Kinocomplete\Exception\NotFoundException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ClientException;
use Kinocomplete\Service\DefaultService;
use Psr\Http\Message\ResponseInterface;
use Kinocomplete\Module\ModuleCache;
use GuzzleHttp\ClientInterface;
use Kinocomplete\Source\Source;
use GuzzleHttp\Psr7\Response;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use Kinocomplete\Feed\Feed;

class VideoCdnApi extends DefaultService implements ApiInterface
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
    // Connect exception.
    if ($exception instanceof ConnectException)
      throw new UnexpectedResponseException(
        'Невозможно подключиться к VideoCdn.'
      );

    // Client exception.
    if ($exception instanceof ClientException)
      throw new UnexpectedResponseException(sprintf(
        'Неожиданный код ответа VideoCdn: %s',
        $exception->getCode()
      ));

    // Server exception.
    if ($exception instanceof ServerException)
      throw new UnexpectedResponseException(sprintf(
        'Неожиданный код ответа VideoCdn: %s',
        $exception->getCode()
      ));

    throw $exception;
  }

  /**
   * Response validator.
   *
   * @param  ResponseInterface $response
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   */
  protected function responseValidator(
    ResponseInterface $response
  ) {
    $json = $response->getBody();
    $array = json_decode($json, true);

    if (
      !is_array($array) ||
      !array_key_exists('data', $array) ||
      !is_array($array['data'])
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ VideoCdn.'
    );

    if (!$array['data'])
      throw new NotFoundException(
        'Не удалось найти запрашиваемый материал.'
      );
  }

  /**
   * Access checking.
   *
   * @param  bool $cache
   * @return bool
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\BasePathNotFoundException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\TokenNotFoundException
   */
  public function accessChecking(
    $cache = false
  ) {
    /** @var Source $source */
    $source = $this->container->get('video_cdn_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->container->get('module_cache');

    // Following getters will throw an
    // errors if value are not defined.
    // So it must be placed before a
    // cache checking block.
    $token    = $source->getToken();
    $origin   = $source->getVideoOrigin();
    $scheme   = $source->getScheme();
    $host     = $source->getHost();
    $basePath = $source->getBasePath();

    if ($cache) {

      $cached = $moduleCache->hasApiToken(
        $token,
        $origin
      );

      if ($cached)
        return true;
    }

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    $queryString = http_build_query([
      'kinopoisk_id' => 'a',
      'api_token' => $token
    ]);

    $url = Path::join(
      $scheme,
      $host,
      $basePath,
      'short?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

      $this->responseValidator($response);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);

    } catch (NotFoundException $exception) {

      $moduleCache->addApiToken(
        $token,
        $origin
      );

      return true;
    }

    throw new \LogicException(
      'Логическая ошибка проверки доступа VideoCdn.'
    );
  }

  /**
   * Get videos.
   *
   * @param  string $title
   * @return array
   * @throws EmptyQueryException
   * @throws NotFoundException
   * @throws TooLargeResponseException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\BasePathNotFoundException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\TokenNotFoundException
   */
  public function getVideos($title)
  {
    Assert::string($title);

    if (!$title)
      throw new EmptyQueryException(
        'Поисковой запрос отсутствует.'
      );

    if (strlen($title) < 3)
      throw new TooLargeResponseException(
        'Поисковой запрос слишком короткий.'
      );

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    /** @var Source $source */
    $source = $this->container->get('video_cdn_source');

    $queryString = http_build_query([
      'api_token' => $source->getToken(),
      'title' => $title
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'short?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

      $this->responseValidator($response);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);
    $responseArray = $responseArray['data'];

    $filter = function ($data) {

      if (
        !$data
        || !is_array($data)
        || !array_key_exists('kp_id', $data)
        || !$data['kp_id']
      ) return false;

      return true;
    };

    $filteredArray = array_filter(
      $responseArray,
      $filter
    );

    if (!count($filteredArray))
      throw new NotFoundException(
        'Материалов по данному запросу не найдено.'
      );

    $instantiatedItems = array_map(
      $source->getVideoFactory(),
      $filteredArray
    );

    return $instantiatedItems;
  }

  /**
   * Get video.
   *
   * @param  string $id
   * @return \Kinocomplete\Video\Video
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\BasePathNotFoundException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\TokenNotFoundException
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
    $source = $this->container->get('video_cdn_source');

    $queryString = http_build_query([
      'api_token' => $source->getToken(),
      'kinopoisk_id' => $id
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'short?'. $queryString
    );

    $response = null;

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

      $this->responseValidator($response);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);
    $responseArray = $responseArray['data'];

    $data = current($responseArray);

    if (
      !$data
      || !is_array($data)
      || !array_key_exists('kp_id', $data)
      || !$data['kp_id']
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ VideoCdn.'
    );

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
