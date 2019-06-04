<?php

namespace Kinocomplete\Controller;

use Kinocomplete\Api\SystemApi;
use Kinocomplete\Video\Video;
use Kinocomplete\Feed\Feeds;
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

    // Count FeedPosts of Moonwalk
    // which is related to Posts.
    $moonwalkFeedPostsCount = $systemApi->countFeedPosts(
      ['videoOrigin' => Video::MOONWALK_ORIGIN],
      ['[><]post' => ['postId' => 'id']]
    );

    // Count FeedPosts of Kodik
    // which is related to Posts.
    $kodikFeedPostsCount = $systemApi->countFeedPosts(
      ['videoOrigin' => Video::KODIK_ORIGIN],
      ['[><]post' => ['postId' => 'id']]
    );

    $parameters = [
      'moonwalkFeeds'             => Feeds::getEnabled($this->container, Video::MOONWALK_ORIGIN),
      'kodikFeeds'                => Feeds::getEnabled($this->container, Video::KODIK_ORIGIN),
      'moonwalkFeedPostsCount'    => $moonwalkFeedPostsCount,
      'kodikFeedPostsCount'       => $kodikFeedPostsCount,
      'feedLoaderPostsLimit'      => $this->container->get('feed_loader_posts_limit'),
      'categoriesFromVideoType'   => $this->container->get('categories_from_video_type'),
      'categoriesFromVideoGenres' => $this->container->get('categories_from_video_genres')
    ];

    return $view->render(
      $response,
      'feed-loader/index.html.twig',
      $parameters
    );
  }
}
