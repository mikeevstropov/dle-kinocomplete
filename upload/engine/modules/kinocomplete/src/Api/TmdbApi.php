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
use Kinocomplete\Service\DefaultService;
use GuzzleHttp\Exception\ClientException;
use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Exception\EmptyQueryException;
use Kinocomplete\Exception\InvalidTokenException;
use Kinocomplete\Exception\UnexpectedResponseException;

class TmdbApi extends DefaultService implements ApiInterface
{
  /**
   * Error handler.
   *
   * @param  ClientException $exception
   * @throws EmptyQueryException
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   */
  protected function errorHandler(
    ClientException $exception
  ) {
    $jsonResponse = json_decode(
      $exception->getResponse()->getBody(),
      true
    );

    $error = null;

    // Getting the first error.
    if (isset($jsonResponse['errors'])) {

      $errors = $jsonResponse['errors'];

      $error = array_key_exists(0, $errors)
        ? $errors[0]
        : null;

    // Or getting "status_message".
    } else if (isset($jsonResponse['status_message'])) {

      $error = $jsonResponse['status_message'];
    }

    switch ($error) {

      case 'query must be provided':
        throw new EmptyQueryException(
          'Поисковой запрос отсутствует.'
        );

      case 'The resource you requested could not be found.':
        throw new NotFoundException(
          'Не удалось найти запрашиваемый материал.'
        );

      case 'Invalid API key: You must be granted a valid key.':
        throw new InvalidTokenException(
          'Неверный TMDB API Token.'
        );

      default:
        throw new UnexpectedResponseException(
          'Неожиданный ответ TMDB.'
        );
    }
  }

  /**
   * Access checking.
   *
   * @param  bool $cache
   * @return bool
   * @throws EmptyQueryException
   * @throws InvalidTokenException
   * @throws NotFoundException
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
    $source = $this->container->get('tmdb_source');

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
      'authentication/token/new?api_key='. $token
    );

    $exception = null;

    try {

      $client->request('GET', $url);

    } catch (ClientException $e) {

      $exception = $e;
    }

    if ($exception) {

      $moduleCache->removeApiToken(
        $token,
        $origin
      );

      $this->errorHandler($exception);

    } else {

      $moduleCache->addApiToken(
        $token,
        $origin
      );
    }

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

    /** @var Source $source */
    $source = $this->container->get('tmdb_source');

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    $queryString = http_build_query([
      'language' => $source->getLanguage(),
      'api_key' => $source->getToken(),
      'query' => $title
    ], null, '&', PHP_QUERY_RFC3986);

    $url = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'search/multi?'. $queryString
    );

    try {

      /** @var Response $response */
      $response = $client->request('GET', $url);

    } catch (ClientException $exception) {

      $this->errorHandler($exception);
    }

    $responseJson = $response->getBody();
    $responseArray = json_decode($responseJson, true);

    if (!is_array($responseArray))
      throw new UnexpectedResponseException(
        'Не удается обработать ответ TMDB.'
      );

    if (!array_key_exists('results', $responseArray))
      throw new UnexpectedResponseException(
        'Неожиданный ответ TMDB.'
      );

    $resultsFilter = function ($data) {

      if (
        !is_array($data)
        || !$data
      ) return false;

      if (
        !array_key_exists('id', $data)
        || !$data['id']
      ) return false;

      if (
        !array_key_exists('media_type', $data)
        || !$data['media_type']
      ) return false;

      if (
        $data['media_type'] !== 'movie'
        && $data['media_type'] !== 'tv'
      ) return false;

      return true;
    };

    $results = array_filter(
      $responseArray['results'],
      $resultsFilter
    );

    if (!count($results))
      throw new NotFoundException(
        'Материалов по данному запросу не найдено.'
      );

    $instantiatedItems = array_map(
      $source->getVideoFactory(),
      $results
    );

    return $instantiatedItems;
  }

  /**
   * Get video.
   *
   * @param  string $id
   * @return Video
   * @throws EmptyQueryException
   * @throws InvalidTokenException
   * @throws NotFoundException
   * @throws UnexpectedResponseException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\BasePathNotFoundException
   * @throws \Kinocomplete\Exception\HostNotFoundException
   * @throws \Kinocomplete\Exception\TokenNotFoundException
   *
   */
  public function getVideo($id)
  {
    Assert::stringNotEmpty(
      $id,
      'Идентификатор запрашиваемого материала отсутствует.'
    );

    /** @var Source $source */
    $source = $this->container->get('tmdb_source');

    /** @var ClientInterface $client */
    $client = $this->container->get('client');

    $queryString = http_build_query([
      'append_to_response' => 'credits',
      'language' => $source->getLanguage(),
      'api_key' => $source->getToken()
    ], null, '&', PHP_QUERY_RFC3986);

    $movieUrl = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'movie/'. $id .'?'. $queryString
    );

    $seriesUrl = Path::join(
      $source->getScheme(),
      $source->getHost(),
      $source->getBasePath(),
      'tv/'. $id .'?'. $queryString
    );

    $response = null;
    $exception = null;
    $isMovie = null;

    try {

      /** @var Response $response */
      $response = $client->request('GET', $movieUrl);

      $isMovie = true;

    } catch (ClientException $e) {

      $exception = $e;
    }

    // Wait for 0.3 sec.
    usleep(300000);

    try {

      /** @var Response $response */
      $response = $client->request('GET', $seriesUrl);

      $isMovie = false;

    } catch (ClientException $e) {

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
      || !array_key_exists('id', $data)
      || !$data['id']
    ) throw new UnexpectedResponseException(
      'Не удается обработать ответ TMDB.'
    );

    $data['media_type'] = $isMovie
      ? 'movie'
      : 'tv';

    $videoFactory = $source->getVideoFactory();

    return $videoFactory($data);
  }

  /**
   * Download feed.
   *
   * @param Feed $feed
   * @param string $filePath
   * @param callable $onProgress
   */
  public function downloadFeed(Feed $feed,
   $filePath,
   callable $onProgress
  ) {
    // TODO: Implement downloadFeed() method.
  }
}
