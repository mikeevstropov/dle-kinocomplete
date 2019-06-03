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
use Kinocomplete\Exception\UnexpectedResponseException;
use Kinocomplete\Exception\FileSystemPermissionException;

class MoonwalkApi extends DefaultService implements ApiInterface
{
  /**
   * Error handler.
   *
   * @param  TransferException $exception
   * @throws NotFoundException
   * @throws InvalidTokenException
   * @throws TooLargeResponseException
   * @throws UnexpectedResponseException
   */
  protected function errorHandler(
    TransferException $exception
  ) {
    // Connect exception.
    if ($exception instanceof ConnectException)
      throw new UnexpectedResponseException(
        'Невозможно подключиться к Moonwalk.'
      );

    // Client exception.
    if ($exception instanceof ClientException) {

      $jsonResponse = json_decode(
        $exception->getResponse()->getBody(),
        true
      );

      $error = isset($jsonResponse['error'])
        ? $jsonResponse['error']
        : null;

      switch ($error) {

        case 'videos_not_found':
          throw new NotFoundException(
            'Материалов по данному запросу не найдено.'
          );

        case 'video_not_found':
          throw new NotFoundException(
            'Не удалось найти запрашиваемый материал.'
          );

        case 'movie_not_found':
          throw new NotFoundException(
            'Не удалось найти запрашиваемый материал.'
          );

        case 'serial_not_found':
          throw new NotFoundException(
            'Не удалось найти запрашиваемый материал.'
          );

        case 'undefined_api_token':
          throw new InvalidTokenException(
            'Неверный Moonwalk API Token.'
          );

        case 'undefined_finder_params':
          throw new \LogicException(
            'Неверные параметры поиска материалов.'
          );

        case 'title_min_length_3':
          throw new TooLargeResponseException(
            'Поисковой запрос слишком короткий.'
          );

        case 'video_thumbnails_not_generated':
          throw new NotFoundException(
            'Не удалось получить скриншоты материала.'
          );

        default:
          throw new UnexpectedResponseException(
            'Неожиданный ответ API Moonwalk.'
          );
      }
    }

    // Server exception.
    if ($exception instanceof ServerException)
      throw new UnexpectedResponseException(sprintf(
        'Неожиданный код ответа Moonwalk: %s',
        $exception->getCode()
      ));
  }

  /**
   * Access checking.
   *
   * @param  bool $cache
   * @return bool
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws TooLargeResponseException
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
    $source = $this->container->get('moonwalk_source');

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

    $url = Path::join(
      $scheme,
      $host,
      $basePath,
      'videos.json?api_token='. $token
    );

    try {

      $client->request('GET', $url);

    } catch (TransferException $exception) {

      if ($exception instanceof ClientException) {

        $jsonResponse = json_decode(
          $exception->getResponse()->getBody(),
          true
        );

        $error = isset($jsonResponse['error'])
          ? $jsonResponse['error']
          : null;

        if ($error === 'undefined_finder_params') {

          $moduleCache->addApiToken(
            $token,
            $origin
          );

          return true;
        }
      }

      $this->errorHandler($exception);
    }

    throw new UnexpectedResponseException(
      'Неожиданный ответ API Moonwalk.'
    );
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

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    /** @var Source $source */
    $source = $this->container->get('moonwalk_source');

    $queryString = http_build_query([
      'api_token' => $source->getToken(),
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

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);

    if (!is_array($responseArray))
      throw new UnexpectedResponseException(
        'Не удается обработать ответ Moonwalk.'
      );

    $filter = function ($data) {

      if (
        !is_array($data)
        || !$data
      ) return false;

      if (
        !array_key_exists('token', $data)
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
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws TooLargeResponseException
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
    $source = $this->container->get('moonwalk_source');

    $queryString = http_build_query([
      'api_token' => $source->getToken(),
      'token' => $id
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

    } catch (TransferException $e) {

      $exception = $e;
    }

    // Wait for 0.3 sec.
    usleep(300000);

    try {

      /** @var Response $response */
      $response = $client->request('GET', $seriesUrl);

    } catch (TransferException $e) {

      $exception = $e;
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
      !is_array($data)
      || !$data
      || !array_key_exists('token', $data)
      || !$data['token']
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ Moonwalk.'
    );

    // Screenshots.
    $screenshotsEnabled = $this->container->get(
      'moonwalk_screenshots_enabled'
    );

    if ($screenshotsEnabled) {

      try {

        $data['screenshots'] = $this->getScreenshots($id);

      } catch (NotFoundException $exception) {}
    }

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
   * @throws TooLargeResponseException
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
    $source = $this->container->get('moonwalk_source');

    $requestPath = Templating::renderString(
      $feed->getRequestPath(),
      ['token' => $source->getToken()]
    );

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
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

  /**
   * Get screenshots.
   *
   * @param  string $videoId
   * @return array
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws TooLargeResponseException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\BasePathNotFoundException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\TokenNotFoundException
   */
  public function getScreenshots($videoId)
  {
    Assert::stringNotEmpty(
      $videoId,
      'Идентификатор видео должен быть не пустой строкой.'
    );

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    /** @var Source $source */
    $source = $this->container->get('moonwalk_source');

    $queryString = http_build_query([
      'api_token' => $source->getToken(),
      'token' => $videoId
    ]);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'thumbnails.json?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

    } catch (TransferException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $data = json_decode($responseJson, true);

    // Status 200 with error.
    if (
      is_array($data) &&
      array_key_exists('error', $data)
    ) {

      $this->errorHandler(
        new ClientException(
          '',
          new Request('GET', $url),
          $response
        )
      );
    }

    if (
      !is_array($data)
      || !$data
      || !array_key_exists('thumbnails', $data)
      || !$data['thumbnails']
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ Moonwalk.'
    );

    $result = $data['thumbnails'];

    // Limitation.
    if (count($result) > 30) {

      $result = array_slice(
        $result,
        24,
        6
      );
    }

    return $result;
  }
}
