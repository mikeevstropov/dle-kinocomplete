<?php

namespace Kinocomplete\Module;

use Kinocomplete\Container\ContainerFactory;
use Kinocomplete\Service\DefaultService;
use Kinocomplete\Templating\Templating;
use Kinocomplete\Api\SystemApi;
use Webmozart\Assert\Assert;
use Webmozart\PathUtil\Path;
use Medoo\Medoo;

class Module extends DefaultService
{
  protected function configure()
  {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $filePath = Path::join(
      $this->container->get('module_sql_dir'),
      'configure.sql'
    );

    Assert::fileExists(
      $filePath,
      'Файл конфигурации модуля не найден.'
    );

    /** @var SystemApi $systemApi */
    $systemApi = $this->container->get('system_api');

    $context = ContainerFactory::toArray($this->container);
    $context['database_table_engine'] = $systemApi->getTableEngine('post');

    $configureSql = file_get_contents($filePath);

    $configureSql = Templating::renderString(
      $configureSql,
      $context
    );

    $database->query($configureSql);

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При конфигурации модуля произошла ошибка: '. $error[2]
      );

    return true;
  }

  protected function deconfigure()
  {
    /** @var Medoo $database */
    $database = $this->container->get('database');

    $filePath = Path::join(
      $this->container->get('module_sql_dir'),
      'deconfigure.sql'
    );

    Assert::fileExists(
      $filePath,
      'Файл де-конфигурации модуля не найден.'
    );

    $configureSql = file_get_contents($filePath);

    $deconfigureSql = Templating::renderString(
      $configureSql,
      $this->container
    );

    $database->query($deconfigureSql);

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При деконфигурации модуля произошла ошибка: '. $error[2]
      );

    return true;
  }

  public function isInstalled()
  {
    $database           = $this->container->get('database');
    $name               = $this->container->get('module_name');
    $prefix             = $this->container->get('database_prefix');
    $configurationTable = $this->container->get('database_configuration_table');
    $feedPostsTable     = $this->container->get('database_feed_posts_table');

    $modules = $database->select(
      'admin_sections',
      ['name'],
      ['name' => $name]
    );

    $configurationTablePrefixed = $prefix . $configurationTable;
    $feedPostsTablePrefixed     = $prefix . $feedPostsTable;

    $configurationTableExists = !!$database
      ->query("SHOW TABLES LIKE '$configurationTablePrefixed'")
      ->rowCount();

    $feedPostsTableExists = !!$database
      ->query("SHOW TABLES LIKE '$feedPostsTablePrefixed'")
      ->rowCount();

    return count($modules) === 1
      && $configurationTableExists
      && $feedPostsTableExists;
  }

  public function install()
  {
    $database    = $this->container->get('database');
    $name        = $this->container->get('module_name');
    $label       = $this->container->get('module_label');
    $description = $this->container->get('module_description');
    $icon        = $this->container->get('module_icon');

    if ($this->isInstalled())
      throw new \Exception(
        'Ошибка установки уже установленного модуля.'
      );

    $database->insert('admin_sections', [
      'name'  => $name,
      'title' => $label,
      'descr' => $description,
      'icon'  => $icon
    ]);

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При установке модуля произошла ошибка: '. $error[2]
      );

    $this->configure();

    return true;
  }

  public function uninstall()
  {
    $database = $this->container->get('database');
    $name     = $this->container->get('module_name');

    if (!$this->isInstalled())
      throw new \Exception(
        'Ошибка удаления несуществующего модуля.'
      );

    $database->delete('admin_sections', [
      'AND' => ['name' => $name]
    ]);

    $error = $database->error();

    if ($error[1])
      throw new \Exception(
        'При удалении модуля произошла ошибка: '. $error[2]
      );

    $this->deconfigure();

    return true;
  }
}
