<?php

namespace Kinocomplete\Controller;

use Kinocomplete\Exception\NotFoundException;
use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\FeedLoader\FeedProcessor;
use Kinocomplete\FeedLoader\PostProcessor;
use Kinocomplete\FeedLoader\StatusSender;
use Kinocomplete\Diagnostics\Diagnostics;
use Kinocomplete\FeedLoader\PostUpdater;
use Kinocomplete\FeedLoader\PostCleaner;
use Kinocomplete\FeedLoader\PostCreator;
use Kinocomplete\FeedLoader\FileParser;
use Kinocomplete\Api\ApiInterface;
use Kinocomplete\Api\MoonwalkApi;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Api\RutorApi;
use Kinocomplete\Api\HdvbApi;
use Kinocomplete\Video\Video;
use Kinocomplete\Api\TmdbApi;
use Kinocomplete\Feed\Feeds;
use Slim\Http\Response;
use Slim\Http\Request;

class ApiController extends DefaultController
{
  /**
   * Get configuration action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function getConfigurationAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    /** @var Diagnostics $diagnostics */
    $diagnostics = $this->container->get('diagnostics');

    $videoFields = ContainerFactory::fromNamespace(
      $this->container,
      'video_field',
      true
    );

    $postPatterns = ContainerFactory::fromNamespace(
      $this->container,
      'post_pattern',
      true
    );

    $autocompleteCategories = !!$this->container->get('categories_from_video_type')
      || !!$this->container->get('categories_from_video_genres');

    $response = $response->withJson([
      'module_version'                 => $this->container->get('module_version'),
      'module_label'                   => $this->container->get('module_label'),
      'module_name'                    => $this->container->get('module_name'),
      'module_description'             => $this->container->get('module_description'),
      'module_errors'                  => $diagnostics->getErrors(),
      'autocomplete_add_post_enabled'  => !!$this->container->get('autocomplete_add_post_enabled'),
      'autocomplete_edit_post_enabled' => !!$this->container->get('autocomplete_edit_post_enabled'),
      'autocomplete_categories'        => $autocompleteCategories,
      'system_version_id'              => $this->container->get('system')->get('version_id'),
      'system_version_tag'             => $this->container->get('system')->get('version_tag'),
      'extra_fields'                   => $this->container->get('extra_fields'),
      'video_fields'                   => $videoFields,
      'post_patterns'                  => $postPatterns,
    ]);

    return $response;
  }

  /**
   * Get videos action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   * @throws NotFoundException
   * @throws \GuzzleHttp\Exception\GuzzleException
   * @throws \Kinocomplete\Exception\EmptyQueryException
   * @throws \Kinocomplete\Exception\InvalidTokenException
   * @throws \Kinocomplete\Exception\TooLargeResponseException
   * @throws \Kinocomplete\Exception\UnexpectedResponseException
   */
  public function getVideosAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $title           = $request->getQueryParam('title');
    $moonwalkEnabled = $this->container->get('moonwalk_enabled');
    $tmdbEnabled     = $this->container->get('tmdb_enabled');
    $hdvbEnabled     = $this->container->get('hdvb_enabled');
    $rutorEnabled    = $this->container->get('rutor_enabled');

    /** @var MoonwalkApi $moonwalkApi */
    $moonwalkApi = $this->container->get('moonwalk_api');

    /** @var TmdbApi $tmdbApi */
    $tmdbApi = $this->container->get('tmdb_api');

    /** @var HdvbApi $hdvbApi */
    $hdvbApi = $this->container->get('hdvb_api');

    /** @var RutorApi $rutorApi */
    $rutorApi = $this->container->get('rutor_api');

    // Moonwalk.
    try {

      $moonwalkResults = $moonwalkEnabled
        ? $moonwalkApi->getVideos($title)
        : [];

    } catch (NotFoundException $exception) {

      $moonwalkResults = [];
    }

    // Tmdb.
    try {

      $tmdbResults = $tmdbEnabled
        ? $tmdbApi->getVideos($title)
        : [];

    } catch (NotFoundException $exception) {

      $tmdbResults = [];
    }

    // Hdvb.
    try {

      $hdvbResults = $hdvbEnabled
        ? $hdvbApi->getVideos($title)
        : [];

    } catch (NotFoundException $exception) {

      $hdvbResults = [];
    }

    // Rutor.
    try {

      $rutorResults = $rutorEnabled
        ? $rutorApi->getVideos($title)
        : [];

    } catch (NotFoundException $exception) {

      $rutorResults = [];
    }

    $results = array_merge(
      $moonwalkResults,
      $tmdbResults,
      $hdvbResults,
      $rutorResults
    );

    if (!$results)
      throw new NotFoundException(
        'Материалов по данному запросу не найдено.'
      );

    $response = $response->withJson($results);

    return $response;
  }

  /**
   * Get video action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   */
  public function getVideoAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $id     = $request->getQueryParam('id');
    $origin = $request->getQueryParam('origin');

    /** @var ApiInterface $api */
    $api = null;

    if ($origin === Video::MOONWALK_ORIGIN) {

      $api = $this->container->get('moonwalk_api');

    } else if ($origin === Video::TMDB_ORIGIN) {

      $api = $this->container->get('tmdb_api');

    } else if ($origin === Video::HDVB_ORIGIN) {

      $api = $this->container->get('hdvb_api');

    } else if ($origin === Video::RUTOR_ORIGIN) {

      $api = $this->container->get('rutor_api');

    } else {

      throw new \InvalidArgumentException(
        'Неизвестный источник запроса материала.'
      );
    }

    $result = $api->getVideo($id);

    $response = $response->withJson($result);

    return $response;
  }

  /**
   * Get autocomplete video action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   * @throws \Exception
   */
  public function getAutocompleteVideoAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $id     = $request->getQueryParam('id');
    $origin = $request->getQueryParam('origin');

    /** @var ApiInterface $api */
    $api = null;

    if ($origin === Video::MOONWALK_ORIGIN) {

      $api = $this->container->get('moonwalk_api');

    } else if ($origin === Video::TMDB_ORIGIN) {

      $api = $this->container->get('tmdb_api');

    } else if ($origin === Video::HDVB_ORIGIN) {

      $api = $this->container->get('hdvb_api');

    } else if ($origin === Video::RUTOR_ORIGIN) {

      $api = $this->container->get('rutor_api');

    } else {

      throw new \InvalidArgumentException(
        'Неизвестный источник запроса материала.'
      );
    }

    $video = $api->getVideo($id);

    // Download images.
    $downloadImages = $this->container->get('images_auto_download');

    if ($downloadImages) {

      $fileDownloader = $this->container->get('file_downloader');
      $fileDownloader->downloadVideoImages($video);
    }

    // Download torrents.
    $downloadTorrents = $this->container->get('torrents_auto_download');

    if ($downloadTorrents) {

      $fileDownloader = $this->container->get('file_downloader');
      $fileDownloader->downloadVideoTorrents($video);
    }

    $result = (array) $video;

    // Categories
    $result['categories'] = $video->getCategories(
      $this->container->get('category_factory'),
      (bool) $this->container->get('categories_from_video_type'),
      (bool) $this->container->get('categories_from_video_genres'),
      CategoryFactory::CREATE_NOT_EXISTED,
      (int) $this->container->get('categories_case')
    );

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    $systemApi->clearSystemCache('category');

    $response = $response->withJson($result);

    return $response;
  }

  /**
   * Create moonwalk posts action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @throws \Exception
   */
  public function createMoonwalkPostsAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $postProcessor = new PostProcessor(
      $this->container,
      $this->container->get('moonwalk_source')
    );

    $feedProcessor = new FeedProcessor(
      $this->container->get('moonwalk_source'),
      $this->container->get('system_api'),
      $this->container->get('system_cache_dir')
    );

    $fileParser = new FileParser(
      $this->container->get('system_cache_dir')
    );

    $statusSender = new StatusSender();

    $creator = new PostCreator(
      $fileParser,
      $postProcessor,
      $feedProcessor,
      $statusSender
    );

    $feeds = [];

    if ($this->container->get('moonwalk_foreign_movies_feed_enabled'))
      $feeds[] = Feeds::get('foreign-movies', Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_russian_movies_feed_enabled'))
      $feeds[] = Feeds::get('russian-movies', Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_camrip_movies_feed_enabled'))
      $feeds[] = Feeds::get('camrip-movies',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_foreign_series_feed_enabled'))
      $feeds[] = Feeds::get('foreign-series',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_russian_series_feed_enabled'))
      $feeds[] = Feeds::get('russian-series',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_anime_movies_feed_enabled'))
      $feeds[] = Feeds::get('anime-movies',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_anime_series_feed_enabled'))
      $feeds[] = Feeds::get('anime-series',  Video::MOONWALK_ORIGIN);

    try {

      $creator->create(
        $feeds,
        (int) $this->container->get('feed_loader_posts_limit')
      );

    } catch (\Exception $exception) {

      if (!$statusSender->isConnected())
        $statusSender->openConnection();

      $statusSender->closeConnection(
        $exception
      );

    }

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    $systemApi->clearSystemCache('category');

    exit();
  }

  /**
   * Update moonwalk posts action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @throws \Exception
   */
  public function updateMoonwalkPostsAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $postProcessor = new PostProcessor(
      $this->container,
      $this->container->get('moonwalk_source')
    );

    $feedProcessor = new FeedProcessor(
      $this->container->get('moonwalk_source'),
      $this->container->get('system_api'),
      $this->container->get('system_cache_dir')
    );

    $fileParser = new FileParser(
      $this->container->get('system_cache_dir')
    );

    $statusSender = new StatusSender();

    $creator = new PostUpdater(
      $fileParser,
      $postProcessor,
      $feedProcessor,
      $statusSender
    );

    $feeds = [];

    if ($this->container->get('moonwalk_foreign_movies_feed_enabled'))
      $feeds[] = Feeds::get('foreign-movies', Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_russian_movies_feed_enabled'))
      $feeds[] = Feeds::get('russian-movies', Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_camrip_movies_feed_enabled'))
      $feeds[] = Feeds::get('camrip-movies',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_foreign_series_feed_enabled'))
      $feeds[] = Feeds::get('foreign-series',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_russian_series_feed_enabled'))
      $feeds[] = Feeds::get('russian-series',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_anime_movies_feed_enabled'))
      $feeds[] = Feeds::get('anime-movies',  Video::MOONWALK_ORIGIN);

    if ($this->container->get('moonwalk_anime_series_feed_enabled'))
      $feeds[] = Feeds::get('anime-series',  Video::MOONWALK_ORIGIN);

    try {

      $creator->update(
        $feeds,
        (int) $this->container->get('feed_loader_posts_limit')
      );

    } catch (\Exception $exception) {

      if (!$statusSender->isConnected())
        $statusSender->openConnection();

      $statusSender->closeConnection(
        $exception
      );

    }

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    $systemApi->clearSystemCache('category');

    exit();
  }

  /**
   * Clean moonwalk posts action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   * @throws \Exception
   */
  public function cleanMoonwalkPostsAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $postCleaner = new PostCleaner(
      $this->container->get('moonwalk_source'),
      $this->container->get('system_api')
    );

    $postCleaner->clean();

    $response = $response->withJson([
      'status' => 'success',
    ]);

    return $response;
  }
}
