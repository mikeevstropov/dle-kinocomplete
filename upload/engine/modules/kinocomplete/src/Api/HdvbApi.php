<?php

namespace Kinocomplete\Api;

use Kinocomplete\Feed\Feed;
use GuzzleHttp\Psr7\Request;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use GuzzleHttp\Psr7\Response;
use Kinocomplete\Video\Video;
use Kinocomplete\Source\Source;
use GuzzleHttp\ClientInterface;
use Kinocomplete\Module\ModuleCache;
use Psr\Http\Message\ResponseInterface;
use Kinocomplete\Service\DefaultService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Exception\EmptyQueryException;
use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Exception\TooLargeResponseException;
use Kinocomplete\Exception\UnexpectedResponseException;

class HdvbApi extends DefaultService implements ApiInterface
{
  /**
   * Error handler.
   *
   * @param  \Exception $exception
   * @throws UnexpectedResponseException
   */
  protected function errorHandler(
    \Exception $exception
  ) {
    if ($exception instanceof ConnectException)
      throw new UnexpectedResponseException(
        'Невозможно подключиться к HDVB.'
      );

    throw $exception;
  }

  /**
   * Response validator.
   *
   * @param  ResponseInterface $response
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   */
  protected function responseValidator(
    ResponseInterface $response
  ) {
    $json = $response->getBody();
    $array = json_decode($json, true);

    if (!is_array($array))
      throw new UnexpectedResponseException(
        'Не удается обработать ответ HDVB.'
      );

    if (array_key_exists('code', $array)) {

      switch ($array['code']) {

        case 5:
          throw new InvalidTokenException(
            'Неверный HDVB API Token.'
          );

        case 7:
          throw new NotFoundException(
            'Не удалось найти запрашиваемый материал.'
          );

        default:

          $message = array_key_exists('text', $array)
            ? $array['text']
            : 'Неизвестная ошибка';

          throw new ClientException(
            $message,
            new Request('GET', '/'),
            $response
          );
      }
    }
  }

  /**
   * Access checking.
   *
   * @param  bool $cache
   * @return bool
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function accessChecking(
    $cache = false
  ) {
    /** @var Source $source */
    $source = $this->container->get('hdvb_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->container->get('module_cache');

    $token = $source->getToken();
    $origin = $source->getVideoOrigin();

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
      'id_kp' => 666,
      'token' => $token
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'videos.json?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

      $this->responseValidator($response);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $moduleCache->addApiToken(
      $token,
      $origin
    );

    return true;
  }

  /**
   * Get videos.
   *
   * @param  string $title
   * @return array
   * @throws EmptyQueryException
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws TooLargeResponseException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
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
    $source = $this->container->get('hdvb_source');

    $queryString = http_build_query([
      'token' => $source->getToken(),
      'title' => $title
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'videos.json?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

      $this->responseValidator($response);

    } catch (ClientException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);

    $filter = function ($data) {

      if (
        !$data
        || !is_array($data)
        || !array_key_exists('token', $data)
        || !$data['token']
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
   * @return Video
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
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
    $source = $this->container->get('hdvb_source');

    $queryString = http_build_query([
      'token' => $source->getToken(),
      'video_token' => $id
    ]);

    $movieUrl = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'movie.json?'. $queryString
    );

    $seriesUrl = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'serial.json?'. $queryString
    );

    $response = null;
    $exception = null;

    try {

      /** @var Response $response */
      $response = $client->request('GET', $movieUrl);

      $this->responseValidator($response);

    } catch (\Exception $e) {

      $response = null;
      $exception = $e;
    }

    if (!$response) {

      // Wait for 0.3 sec.
      usleep(300000);

      try {

        /** @var Response $response */
        $response = $client->request('GET', $seriesUrl);

        $this->responseValidator($response);

      } catch (\Exception $e) {

        $response = null;
        $exception = $e;
      }
    }

    // No response, no exception.
    if (!$response && !$exception)
      throw new \LogicException(
        'Произошла логическая ошибка.'
      );

    // No response, exception.
    if (!$response && $exception)
      $this->errorHandler($exception);

    $responseJson = $response->getBody();
    $data = json_decode($responseJson, true);

    if (
      !$data
      || !is_array($data)
      || !array_key_exists('token', $data)
      || !$data['token']
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ HDVB.'
    );

    $videoFactory = $source->getVideoFactory();

    return $videoFactory($data);
  }

  /**
   * Download feed.
   *
   * @param  Feed $feed
   * @param  string $filePath
   * @param  callable|null $onProgress
   * @return string|void
   */
  public function downloadFeed(
    Feed $feed,
    $filePath,
    callable $onProgress = null
  ) {
    // TODO: Implement downloadFeed() method.
  }
}
