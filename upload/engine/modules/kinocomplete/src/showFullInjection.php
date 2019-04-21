<?php

require __DIR__ . '/../vendor/autoload.php';

use Kinocomplete\Configuration\ConfigurationInjector;
use Kinocomplete\Configuration\ConfigurationProvider;
use Kinocomplete\Templating\SystemTemplating;
use Kinocomplete\Service\ServiceInjector;
use Kinocomplete\Container\Container;

/**
 * Private scope.
 */
call_user_func(function () use (&$tpl, &$row) {

  $configuration = ConfigurationProvider::getDefaults(__DIR__ .'/../');
  $container = new Container($configuration);

  $serviceInjector = new ServiceInjector($container);
  $configurationInjector = new ConfigurationInjector($container);

  $serviceInjector->inject();
  $configurationInjector->inject();

  $postFactory = $container->get('post_factory');
  $post = $postFactory->fromDatabaseArray($row);

  // Resolve [kc_xflist_field index="n"]
  $tpl->copy_template = SystemTemplating::resolveXFList(
    $tpl->copy_template,
    $post->extraFields
  );

  // Resolve [kc_xflist_has_field index="1"][/kc_xflist_has_field]
  $tpl->copy_template = SystemTemplating::resolveXFListHas(
    $tpl->copy_template,
    $post->extraFields
  );

  // Resolve [kc_xflist_not_field index="1"][/kc_xflist_not_field]
  $tpl->copy_template = SystemTemplating::resolveXFListNot(
    $tpl->copy_template,
    $post->extraFields
  );
});
