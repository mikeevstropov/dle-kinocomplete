<?php

namespace Kinocomplete\Service;

use Kinocomplete\ExtraField\ExtraFieldFactory;
use Kinocomplete\FileSystem\FileDownloader;
use Kinocomplete\FeedPost\FeedPostFactory;
use Kinocomplete\Category\CategoryFactory;
use Kinocomplete\Diagnostics\Diagnostics;
use Psr\Container\ContainerInterface;
use Kinocomplete\Container\Container;
use Kinocomplete\Video\VideoFactory;
use Kinocomplete\Module\ModuleCache;
use Kinocomplete\Parser\RutorParser;
use Kinocomplete\User\UserFactory;
use Kinocomplete\Post\PostFactory;
use Kinocomplete\Api\MoonwalkApi;
use Kinocomplete\Api\VideoCdnApi;
use Kinocomplete\Api\SystemApi;
use Kinocomplete\Module\Module;
use Kinocomplete\Source\Source;
use Kinocomplete\Api\RutorApi;
use Kinocomplete\Api\HdvbApi;
use Kinocomplete\Video\Video;
use Kinocomplete\Api\TmdbApi;
use Kinocomplete\Utils\Utils;
use Slim\Views\TwigExtension;
use Slim\Http\Response;
use GuzzleHttp\Client;
use Slim\Http\Request;
use Slim\Views\Twig;
use Campo\UserAgent;
use Medoo\Medoo;


class ServiceFactory
{
  public static function getSystem()
  {
    return function (ContainerInterface $container) {

      $systemRootDir = $container->get('system_root_dir');

      require $systemRootDir .'/engine/data/config.php';

      if (!isset($config))
        throw new \Exception(
          'Некорректное содержимое файла "/engine/data/config.php".'
        );

      if (!isset($config['version_id']))
        throw new \Exception(
          'Параметр "version_id" не был найден при инициализации контейнера "system".'
        );

      $config['version_tag'] = Utils::getVersionTag(
        $config['version_id']
      );

      return new Container($config);
    };
  }

  public static function getExtraFields()
  {
    return function (ContainerInterface $container) {

      $systemRootDir = $container->get('system_root_dir');

      $path = $systemRootDir .'/engine/data/xfields.txt';
      $raw = file_get_contents($path);

      /** @var ExtraFieldFactory $extraFieldFactory */
      $extraFieldFactory = $container->get('extra_field_factory');

      return $extraFieldFactory->fromDefinitions($raw);
    };
  }

  public static function getDatabase()
  {
    return function (ContainerInterface $container) {

      return new Medoo([
        'database_type' => 'mysql',
        'charset'       => 'utf8mb4',
        'collation'     => 'utf8mb4_general_ci',
        'database_name' => $container->get('database_name'),
        'server'        => $container->get('database_host'),
        'username'      => $container->get('database_user'),
        'password'      => $container->get('database_pass'),
        'prefix'        => $container->get('database_prefix')
      ]);
    };
  }

  public static function getModule()
  {
    return function (ContainerInterface $container) {

      return new Module($container);
    };
  }

  public static function getModuleCache()
  {
    return function (ContainerInterface $container) {

      return new ModuleCache($container);
    };
  }

  public static function getView()
  {
    return function (ContainerInterface $container) {

      $versionTag = $container->get('system')->get('version_tag');
      $viewDir    = $container->get('module_view_dir') .'/'. $versionTag;

      $view = new Twig(
        $viewDir,
        ['cache' => false]
      );

      $router    = $container->get('router');
      $extension = new TwigExtension($router, '');

      $view->addExtension($extension);

      $environment = $view->getEnvironment();
      $environment->addGlobal('router', $router);
      $environment->addGlobal('diagnostics', $container->get('diagnostics'));
      $environment->addGlobal('moduleVersion', $container->get('module_version'));

      return $view;
    };
  }

  public static function getDiagnostics()
  {
    return function (ContainerInterface $container) {

      return new Diagnostics($container);
    };
  }

  public static function getClient()
  {
    return function (ContainerInterface $container) {

      $config = [
        'headers' => []
      ];

      $proxyEnabled  = $container->get('proxy_enabled');
      $proxySecure   = $container->get('proxy_secure');
      $proxyAddress  = $container->get('proxy_address');
      $proxyLogin    = $container->get('proxy_login');
      $proxyPassword = $container->get('proxy_password');

      if ($proxyEnabled && $proxyAddress) {

        $proxyUri = $proxySecure
          ? 'https://'
          : 'http://';

        $proxyUri .= $proxyLogin && $proxyPassword
          ? $proxyLogin .':'. $proxyPassword .'@'
          : '';

        $proxyUri .= $proxyAddress;

        $config['proxy'] = $proxyUri;
      }

      $config['headers']['user-agent'] = UserAgent::random([
        'device_type' => 'Desktop',
        'os_type' => 'Windows'
      ]);

      return new Client($config);
    };
  }

  public static function getSystemApi()
  {
    return function (ContainerInterface $container) {

      return new SystemApi($container);
    };
  }

  public static function getMoonwalkApi()
  {
    return function (ContainerInterface $container) {

      return new MoonwalkApi($container);
    };
  }

  public static function getTmdbApi()
  {
    return function (ContainerInterface $container) {

      return new TmdbApi($container);
    };
  }

  public static function getHdvbApi()
  {
    return function (ContainerInterface $container) {

      return new HdvbApi($container);
    };
  }

  public static function getVideoCdnApi()
  {
    return function (ContainerInterface $container) {

      return new VideoCdnApi($container);
    };
  }

  public static function getRutorApi()
  {
    return function (ContainerInterface $container) {

      return new RutorApi($container);
    };
  }

  public static function getUsers()
  {
    return function (ContainerInterface $container) {

      /** @var SystemApi $systemApi */
      $systemApi = $container->get('system_api');

      return $systemApi->getUsers();
    };
  }

  public static function getActionUser()
  {
    return function (ContainerInterface $container) {

      $users = $container->get('users');
      $userName = $container->get('action_user_name');

      $selected = $users[0];

      if ($userName) {

        foreach ($users as $user) {

          if ($user->name === $userName) {

            $selected = $user;
            break;
          }
        }
      }

      return $selected;
    };
  }

  public static function getCategories()
  {
    return function (ContainerInterface $container) {

      /** @var SystemApi $systemApi */
      $systemApi = $container->get('system_api');

      return $systemApi->getCategories();
    };
  }

  public static function getUserFactory()
  {
    return function (ContainerInterface $container) {

      return new UserFactory($container);
    };
  }

  public static function getVideoFactory()
  {
    return function (ContainerInterface $container) {

      return new VideoFactory($container);
    };
  }

  public static function getPostFactory()
  {
    return function (ContainerInterface $container) {

      return new PostFactory($container);
    };
  }

  public static function getCategoryFactory()
  {
    return function (ContainerInterface $container) {

      return new CategoryFactory($container);
    };
  }

  public static function getFeedPostFactory()
  {
    return function (ContainerInterface $container) {

      return new FeedPostFactory($container);
    };
  }

  public static function getExtraFieldFactory()
  {
    return function (ContainerInterface $container) {

      return new ExtraFieldFactory($container);
    };
  }

  public static function getFileDownloader()
  {
    return function (ContainerInterface $container) {

      return new FileDownloader($container);
    };
  }

  public static function getMoonwalkSource()
  {
    return function (ContainerInterface $container) {

      /** @var VideoFactory $videoFactory */
      $videoFactory = $container->get('video_factory');

      $source = new Source();
      $source->setApi($container->get('moonwalk_api'));
      $source->setEnabled((bool) $container->get('moonwalk_enabled'));
      $source->setSecure((bool) $container->get('moonwalk_secure'));
      $source->setHost($container->get('moonwalk_host'));
      $source->setBasePath($container->get('moonwalk_base_path'));
      $source->setToken($container->get('moonwalk_token'));
      $source->setVideoOrigin(Video::MOONWALK_ORIGIN);
      $source->setVideoFactory([$videoFactory, 'fromMoonwalk']);

      return $source;
    };
  }

  public static function getTmdbSource()
  {
    return function (ContainerInterface $container) {

      /** @var VideoFactory $videoFactory */
      $videoFactory = $container->get('video_factory');

      $source = new Source();
      $source->setApi($container->get('tmdb_api'));
      $source->setEnabled((bool) $container->get('tmdb_enabled'));
      $source->setSecure((bool) $container->get('tmdb_secure'));
      $source->setHost($container->get('tmdb_host'));
      $source->setBasePath($container->get('tmdb_base_path'));
      $source->setToken($container->get('tmdb_token'));
      $source->setVideoOrigin(Video::TMDB_ORIGIN);
      $source->setVideoFactory([$videoFactory, 'fromTmdb']);
      $source->setLanguage($container->get('tmdb_language'));

      return $source;
    };
  }

  public static function getHdvbSource()
  {
    return function (ContainerInterface $container) {

      /** @var VideoFactory $videoFactory */
      $videoFactory = $container->get('video_factory');

      $source = new Source();
      $source->setApi($container->get('hdvb_api'));
      $source->setEnabled((bool) $container->get('hdvb_enabled'));
      $source->setSecure((bool) $container->get('hdvb_secure'));
      $source->setHost($container->get('hdvb_host'));
      $source->setBasePath($container->get('hdvb_base_path'));
      $source->setToken($container->get('hdvb_token'));
      $source->setVideoOrigin(Video::HDVB_ORIGIN);
      $source->setVideoFactory([$videoFactory, 'fromHdvb']);

      return $source;
    };
  }

  public static function getVideoCdnSource()
  {
    return function (ContainerInterface $container) {

      /** @var VideoFactory $videoFactory */
      $videoFactory = $container->get('video_factory');

      $source = new Source();
      $source->setApi($container->get('video_cdn_api'));
      $source->setEnabled((bool) $container->get('video_cdn_enabled'));
      $source->setSecure((bool) $container->get('video_cdn_secure'));
      $source->setHost($container->get('video_cdn_host'));
      $source->setBasePath($container->get('video_cdn_base_path'));
      $source->setToken($container->get('video_cdn_token'));
      $source->setVideoOrigin(Video::VIDEO_CDN_ORIGIN);
      $source->setVideoFactory([$videoFactory, 'fromVideoCdn']);

      return $source;
    };
  }

  public static function getRutorSource()
  {
    return function (ContainerInterface $container) {

      /** @var VideoFactory $videoFactory */
      $videoFactory = $container->get('video_factory');

      $source = new Source();
      $source->setApi($container->get('rutor_api'));
      $source->setEnabled((bool) $container->get('rutor_enabled'));
      $source->setSecure((bool) $container->get('rutor_secure'));
      $source->setHost($container->get('rutor_host'));
      $source->setVideoOrigin(Video::RUTOR_ORIGIN);
      $source->setVideoFactory([$videoFactory, 'fromRutor']);

      return $source;
    };
  }

  public static function getRutorParser()
  {
    return function (ContainerInterface $container) {

      return new RutorParser($container);
    };
  }

  public static function getErrorHandler()
  {
    return function (ContainerInterface $container) {

      return function (
        Request $request,
        Response $response,
        \Exception $exception
      ) use ($container) {

        $code = $exception->getCode();
        $message = $exception->getMessage();

        try {

          $response = $response->withStatus($code);

        } catch (\InvalidArgumentException $exception) {

          $response = $response->withStatus(500);
          $code = 500;
        }

        return $response->withJson([
          'code' => $code,
          'message' => $message
        ]);
      };
    };
  }
}
