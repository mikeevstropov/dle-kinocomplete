<?php

namespace Kinocomplete\Api;

use Kinocomplete\Feed\Feed;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use GuzzleHttp\Psr7\Response;
use Kinocomplete\Video\Video;
use Kinocomplete\Source\Source;
use GuzzleHttp\ClientInterface;
use Kinocomplete\Module\ModuleCache;
use Kinocomplete\Templating\Templating;
use Kinocomplete\Service\DefaultService;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Exception\EmptyQueryException;
use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Exception\TooLargeResponseException;
use Kinocomplete\Exception\FeedsHostNotFoundException;
use Kinocomplete\Exception\UnexpectedResponseException;
use Kinocomplete\Exception\FileSystemPermissionException;

class KodikApi extends DefaultService implements ApiInterface
{
  /**
   * Error handler.
   *
   * @param  \Exception $exception
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   */
  protected function errorHandler(
    \Exception $exception
  ) {
    // Connect exception.
    if ($exception instanceof ConnectException)
      throw new UnexpectedResponseException(
        'Невозможно подключиться к Kodik.'
      );

    // Client exception.
    if ($exception instanceof ClientException)
      throw new UnexpectedResponseException(sprintf(
        'Неожиданный код ответа Kodik: %s',
        $exception->getCode()
      ));

    // Server exception.
    if ($exception instanceof ServerException) {

      $jsonResponse = json_decode(
        $exception->getResponse()->getBody(),
        true
      );

      $error = isset($jsonResponse['error'])
        ? $jsonResponse['error']
        : null;

      switch ($error) {

        case 'Отсутствует или неверный токен':
          throw new InvalidTokenException(
            'Неверный Kodik API Token.'
          );

        case 'Неправильный формат: id':
          throw new NotFoundException(
            'Не удалось найти запрашиваемый материал.'
          );

        default:
          throw new UnexpectedResponseException(
            'Неожиданный ответ API Kodik.'
          );
      }
    }

    throw $exception;
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
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\TokenNotFoundException
   */
  public function accessChecking(
    $cache = false
  ) {
    /** @var Source $source */
    $source = $this->container->get('kodik_source');

    /** @var ModuleCache $moduleCache */
    $moduleCache = $this->container->get('module_cache');

    // Following getters will throw an
    // errors if value are not defined.
    // So it must be placed before a
    // cache checking block.
    $token  = $source->getToken();
    $origin = $source->getVideoOrigin();
    $scheme = $source->getScheme();
    $host   = $source->getHost();

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
      'token' => $token,
      'id' => 'movie-0',
    ]);

    $url = Path::join(
      $scheme,
      $host,
      'search?'. $queryString
    );

    try {

      /** @var Response $response */
      $client->request('GET', $url);

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
    $source = $this->container->get('kodik_source');

    $queryString = http_build_query([
      'token' => $source->getToken(),
      'title' => $title
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      'search?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);
    $responseArray = $responseArray['results'];

    $filter = function ($data) {

      if (
        !$data
        || !is_array($data)
        || !array_key_exists('id', $data)
        || !$data['id']
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
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
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
    $source = $this->container->get('kodik_source');

    $queryString = http_build_query([
      'token' => $source->getToken(),
      'id' => $id
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      'search?'. $queryString
    );

    $response = null;

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);
    $responseArray = $responseArray['results'];

    $data = current($responseArray);

    if (
      !$data
      || !is_array($data)
      || !array_key_exists('id', $data)
      || !$data['id']
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ Kodik.'
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
   * @return string
   * @throws FileSystemPermissionException
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Throwable
   * @throws \Twig_Error_Loader
   * @throws \Twig_Error_Syntax
   */
  public function downloadFeed(
    Feed $feed,
    $filePath,
    callable $onProgress = null
  ) {
    Assert::stringNotEmpty($filePath);

    if (file_exists($filePath) && !unlink($filePath))
      throw new FileSystemPermissionException(
        'Не удалось удалить файл фида.'
      );

    if (!touch($filePath))
      throw new FileSystemPermissionException(
        'Не удалось создать файл фида.'
      );

    /** @var Source $source */
    $source = $this->container->get('kodik_source');

    $requestPath = Templating::renderString(
      $feed->getRequestPath(),
      ['token' => $source->getToken()]
    );

    $feedsHost = $this->container->get('kodik_feeds_host');

    if (!$feedsHost)
      throw new FeedsHostNotFoundException(
        'Домен для загрузки фидов не установлен.'
      );

    $url = Path::join(
      $source->getScheme(),
      $feedsHost,
      $requestPath
    );

    $progressHandler = function (
      $totalBytes,
      $loadedBytes
    ) use (
      $onProgress,
      $feed
    ) {
      if ($onProgress) {

        $totalBytes = $totalBytes
          ?: $feed->getSize();

        $onProgress(
          $totalBytes,
          $loadedBytes
        );
      }
    };

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    try {

      $client->request('GET', $url, [
        'sink' => $filePath,
        'progress' => $progressHandler
      ]);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    return $filePath;
  }
}
