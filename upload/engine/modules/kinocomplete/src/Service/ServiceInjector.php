<?php

namespace Kinocomplete\Service;

use Psr\Container\ContainerInterface;

class ServiceInjector
{
  protected $container;

  public function __construct(ContainerInterface $container)
  {
    $this->container = $container;
  }

  public function inject()
  {
    $this->container['system']              = ServiceFactory::getSystem();
    $this->container['extra_fields']        = ServiceFactory::getExtraFields();
    $this->container['database']            = ServiceFactory::getDatabase();
    $this->container['module']              = ServiceFactory::getModule();
    $this->container['module_cache']        = ServiceFactory::getModuleCache();
    $this->container['view']                = ServiceFactory::getView();
    $this->container['diagnostics']         = ServiceFactory::getDiagnostics();
    $this->container['client']              = ServiceFactory::getClient();
    $this->container['system_api']          = ServiceFactory::getSystemApi();
    $this->container['users']               = ServiceFactory::getUsers();
    $this->container['action_user']         = ServiceFactory::getActionUser();
    $this->container['categories']          = ServiceFactory::getCategories();
    $this->container['user_factory']        = ServiceFactory::getUserFactory();
    $this->container['video_factory']       = ServiceFactory::getVideoFactory();
    $this->container['post_factory']        = ServiceFactory::getPostFactory();
    $this->container['category_factory']    = ServiceFactory::getCategoryFactory();
    $this->container['feed_post_factory']   = ServiceFactory::getFeedPostFactory();
    $this->container['extra_field_factory'] = ServiceFactory::getExtraFieldFactory();
    $this->container['file_downloader']     = ServiceFactory::getFileDownloader();
    $this->container['moonwalk_api']        = ServiceFactory::getMoonwalkApi();
    $this->container['moonwalk_source']     = ServiceFactory::getMoonwalkSource();
    $this->container['tmdb_api']            = ServiceFactory::getTmdbApi();
    $this->container['tmdb_source']         = ServiceFactory::getTmdbSource();
    $this->container['kodik_api']           = ServiceFactory::getKodikApi();
    $this->container['kodik_source']        = ServiceFactory::getKodikSource();
    $this->container['hdvb_api']            = ServiceFactory::getHdvbApi();
    $this->container['hdvb_source']         = ServiceFactory::getHdvbSource();
    $this->container['video_cdn_api']       = ServiceFactory::getVideoCdnApi();
    $this->container['video_cdn_source']    = ServiceFactory::getVideoCdnSource();
    $this->container['rutor_api']           = ServiceFactory::getRutorApi();
    $this->container['rutor_source']        = ServiceFactory::getRutorSource();
    $this->container['rutor_parser']        = ServiceFactory::getRutorParser();
    $this->container['errorHandler']        = ServiceFactory::getErrorHandler();
  }
}
