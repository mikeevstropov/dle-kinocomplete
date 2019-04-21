<?php

namespace Kinocomplete\Controller;

use Kinocomplete\Api\SystemApi;
use Kinocomplete\Video\Video;
use Slim\Http\Response;
use Slim\Http\Request;

class FeedLoaderController extends DefaultController
{
  /**
   * Index action.
   *
   * @param  Request $request
   * @param  Response $response
   * @param  $arguments
   * @return Response
   * @throws \Exception
   */
  public function indexAction(
    Request $request,
    Response $response,
    $arguments
  ) {
    $router = $this->container->get('router');
    $module = $this->container->get('module');
    $view   = $this->container->get('view');

    // Not installed.
    if (!$module->isInstalled()) {

      $redirectUrl = $router->resolveUrl('install');

      return $response->withRedirect($redirectUrl);
    }

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    $moonwalkFeedPostsCount = $systemApi->countFeedPosts([
      'videoOrigin' => Video::MOONWALK_ORIGIN
    ]);

    $noMoonwalkFeedsEnabled =
      !$this->container->get('moonwalk_foreign_movies_feed_enabled') &&
      !$this->container->get('moonwalk_russian_movies_feed_enabled') &&
      !$this->container->get('moonwalk_camrip_movies_feed_enabled')  &&
      !$this->container->get('moonwalk_foreign_series_feed_enabled') &&
      !$this->container->get('moonwalk_russian_series_feed_enabled') &&
      !$this->container->get('moonwalk_anime_movies_feed_enabled')   &&
      !$this->container->get('moonwalk_anime_series_feed_enabled');

    $parameters = [
      'noMoonwalkFeedsEnabled'           => $noMoonwalkFeedsEnabled,
      'moonwalkForeignMoviesFeedEnabled' => $this->container->get('moonwalk_foreign_movies_feed_enabled'),
      'moonwalkRussianMoviesFeedEnabled' => $this->container->get('moonwalk_russian_movies_feed_enabled'),
      'moonwalkCamripMoviesFeedEnabled'  => $this->container->get('moonwalk_camrip_movies_feed_enabled'),
      'moonwalkForeignSeriesFeedEnabled' => $this->container->get('moonwalk_foreign_series_feed_enabled'),
      'moonwalkRussianSeriesFeedEnabled' => $this->container->get('moonwalk_russian_series_feed_enabled'),
      'moonwalkAnimeMoviesFeedEnabled'   => $this->container->get('moonwalk_anime_movies_feed_enabled'),
      'moonwalkAnimeSeriesFeedEnabled'   => $this->container->get('moonwalk_anime_series_feed_enabled'),
      'feedLoaderPostsLimit'             => $this->container->get('feed_loader_posts_limit'),
      'moonwalkFeedPostsCount'           => $moonwalkFeedPostsCount,
      'categoriesFromVideoType'          => $this->container->get('categories_from_video_type'),
      'categoriesFromVideoGenres'        => $this->container->get('categories_from_video_genres')
    ];

    return $view->render(
      $response,
      'feed-loader/index.html.twig',
      $parameters
    );
  }
}
