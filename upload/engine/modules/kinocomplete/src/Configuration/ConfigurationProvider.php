<?php

namespace Kinocomplete\Configuration;

use Kinocomplete\Container\ContainerFactory;
use Webmozart\Assert\Assert;

class ConfigurationProvider
{
  /**
   * Get configuration defaults.
   *
   * @param  string $workingDir
   * @param  array $overrides
   * @return array
   */
  static public function getDefaults(
    $workingDir,
    $overrides = []
  ) {
    Assert::directory(
      $workingDir,
      'Директория конфигурационных файлов не найдена.'
    );

    $workingDir = realpath($workingDir);

    $moduleJson = ContainerFactory::fromFile(
      $workingDir .'/module.json'
    );

    $configurationJson = ContainerFactory::fromFile(
      $workingDir .'/configuration.json'
    );

    /**
     * Do not merge [$moduleJson] and [$configurationJson]
     * to [$configuration], use declarative approach instead.
     * It's verbose and safe to access to DIC members.
     */
    $configuration = [
      'module_version'                                => $moduleJson['module_version'],
      'module_label'                                  => $moduleJson['module_label'],
      'module_name'                                   => $moduleJson['module_name'],
      'module_description'                            => $moduleJson['module_description'],
      'module_icon'                                   => $moduleJson['module_icon'],
      'module_view_dir'                               => realpath($workingDir .'/view'),
      'module_sql_dir'                                => realpath($workingDir .'/sql'),
      'module_data_dir'                               => realpath($workingDir .'/data'),
      'system_version_min'                            => $moduleJson['system_version_min'],
      'system_version_max'                            => $moduleJson['system_version_max'],
      'system_root_dir'                               => realpath(ROOT_DIR),
      'system_cache_dir'                              => realpath(ROOT_DIR .'/engine/cache'),
      'system_upload_dir'                             => realpath(ROOT_DIR .'/uploads'),
      'database_host'                                 => DBHOST,
      'database_name'                                 => DBNAME,
      'database_user'                                 => DBUSER,
      'database_pass'                                 => DBPASS,
      'database_prefix'                               => PREFIX .'_',
      'database_configuration_table'                  => $moduleJson['database_configuration_table'],
      'database_feed_posts_table'                     => $moduleJson['database_feed_posts_table'],
      'moonwalk_enabled'                              => $configurationJson['moonwalk_enabled'],
      'moonwalk_secure'                               => $configurationJson['moonwalk_secure'],
      'moonwalk_host'                                 => $configurationJson['moonwalk_host'],
      'moonwalk_token'                                => $configurationJson['moonwalk_token'],
      'moonwalk_base_path'                            => $configurationJson['moonwalk_base_path'],
      'moonwalk_player_pattern'                       => $configurationJson['moonwalk_player_pattern'],
      'moonwalk_poster_pattern'                       => $configurationJson['moonwalk_poster_pattern'],
      'moonwalk_thumbnail_pattern'                    => $configurationJson['moonwalk_thumbnail_pattern'],
      'moonwalk_screenshots_enabled'                  => $configurationJson['moonwalk_screenshots_enabled'],
      'moonwalk_foreign_movies_feed_enabled'          => $configurationJson['moonwalk_foreign_movies_feed_enabled'],
      'moonwalk_russian_movies_feed_enabled'          => $configurationJson['moonwalk_russian_movies_feed_enabled'],
      'moonwalk_camrip_movies_feed_enabled'           => $configurationJson['moonwalk_camrip_movies_feed_enabled'],
      'moonwalk_foreign_series_feed_enabled'          => $configurationJson['moonwalk_foreign_series_feed_enabled'],
      'moonwalk_russian_series_feed_enabled'          => $configurationJson['moonwalk_russian_series_feed_enabled'],
      'moonwalk_anime_movies_feed_enabled'            => $configurationJson['moonwalk_anime_movies_feed_enabled'],
      'moonwalk_anime_series_feed_enabled'            => $configurationJson['moonwalk_anime_series_feed_enabled'],
      'tmdb_enabled'                                  => $configurationJson['tmdb_enabled'],
      'tmdb_secure'                                   => $configurationJson['tmdb_secure'],
      'tmdb_host'                                     => $configurationJson['tmdb_host'],
      'tmdb_token'                                    => $configurationJson['tmdb_token'],
      'tmdb_base_path'                                => $configurationJson['tmdb_base_path'],
      'tmdb_language'                                 => $configurationJson['tmdb_language'],
      'kodik_enabled'                                 => $configurationJson['kodik_enabled'],
      'kodik_secure'                                  => $configurationJson['kodik_secure'],
      'kodik_host'                                    => $configurationJson['kodik_host'],
      'kodik_feeds_host'                              => $configurationJson['kodik_feeds_host'],
      'kodik_token'                                   => $configurationJson['kodik_token'],
      'kodik_base_path'                               => $configurationJson['kodik_base_path'],
      'kodik_player_pattern'                          => $configurationJson['kodik_player_pattern'],
      'kodik_poster_pattern'                          => $configurationJson['kodik_poster_pattern'],
      'kodik_thumbnail_pattern'                       => $configurationJson['kodik_thumbnail_pattern'],
      'kodik_movies_feed_enabled'                     => $configurationJson['kodik_movies_feed_enabled'],
      'kodik_series_feed_enabled'                     => $configurationJson['kodik_series_feed_enabled'],
      'kodik_adult_feed_enabled'                      => $configurationJson['kodik_adult_feed_enabled'],
      'kodik_foreign_movies_feed_enabled'             => $configurationJson['kodik_foreign_movies_feed_enabled'],
      'kodik_russian_movies_feed_enabled'             => $configurationJson['kodik_russian_movies_feed_enabled'],
      'kodik_foreign_cartoon_movies_feed_enabled'     => $configurationJson['kodik_foreign_cartoon_movies_feed_enabled'],
      'kodik_russian_cartoon_movies_feed_enabled'     => $configurationJson['kodik_russian_cartoon_movies_feed_enabled'],
      'kodik_soviet_cartoon_movies_feed_enabled'      => $configurationJson['kodik_soviet_cartoon_movies_feed_enabled'],
      'kodik_anime_movies_feed_enabled'               => $configurationJson['kodik_anime_movies_feed_enabled'],
      'kodik_foreign_series_feed_enabled'             => $configurationJson['kodik_foreign_series_feed_enabled'],
      'kodik_russian_series_feed_enabled'             => $configurationJson['kodik_russian_series_feed_enabled'],
      'kodik_foreign_cartoon_series_feed_enabled'     => $configurationJson['kodik_foreign_cartoon_series_feed_enabled'],
      'kodik_russian_cartoon_series_feed_enabled'     => $configurationJson['kodik_russian_cartoon_series_feed_enabled'],
      'kodik_foreign_documentary_series_feed_enabled' => $configurationJson['kodik_foreign_documentary_series_feed_enabled'],
      'kodik_russian_documentary_series_feed_enabled' => $configurationJson['kodik_russian_documentary_series_feed_enabled'],
      'kodik_multipart_movies_feed_enabled'           => $configurationJson['kodik_multipart_movies_feed_enabled'],
      'kodik_anime_series_feed_enabled'               => $configurationJson['kodik_anime_series_feed_enabled'],
      'hdvb_enabled'                                  => $configurationJson['hdvb_enabled'],
      'hdvb_secure'                                   => $configurationJson['hdvb_secure'],
      'hdvb_host'                                     => $configurationJson['hdvb_host'],
      'hdvb_token'                                    => $configurationJson['hdvb_token'],
      'hdvb_base_path'                                => $configurationJson['hdvb_base_path'],
      'hdvb_player_pattern'                           => $configurationJson['hdvb_player_pattern'],
      'hdvb_poster_pattern'                           => $configurationJson['hdvb_poster_pattern'],
      'hdvb_thumbnail_pattern'                        => $configurationJson['hdvb_thumbnail_pattern'],
      'video_cdn_enabled'                             => $configurationJson['video_cdn_enabled'],
      'video_cdn_secure'                              => $configurationJson['video_cdn_secure'],
      'video_cdn_host'                                => $configurationJson['video_cdn_host'],
      'video_cdn_token'                               => $configurationJson['video_cdn_token'],
      'video_cdn_base_path'                           => $configurationJson['video_cdn_base_path'],
      'video_cdn_player_pattern'                      => $configurationJson['video_cdn_player_pattern'],
      'video_cdn_poster_pattern'                      => $configurationJson['video_cdn_poster_pattern'],
      'video_cdn_thumbnail_pattern'                   => $configurationJson['video_cdn_thumbnail_pattern'],
      'rutor_enabled'                                 => $configurationJson['rutor_enabled'],
      'rutor_secure'                                  => $configurationJson['rutor_secure'],
      'rutor_host'                                    => $configurationJson['rutor_host'],
      'proxy_enabled'                                 => $configurationJson['proxy_enabled'],
      'proxy_secure'                                  => $configurationJson['proxy_secure'],
      'proxy_address'                                 => $configurationJson['proxy_address'],
      'proxy_login'                                   => $configurationJson['proxy_login'],
      'proxy_password'                                => $configurationJson['proxy_password'],
      'autocomplete_add_post_enabled'                 => $configurationJson['autocomplete_add_post_enabled'],
      'autocomplete_edit_post_enabled'                => $configurationJson['autocomplete_edit_post_enabled'],
      'video_field_id'                                => $configurationJson['video_field_id'],
      'video_field_origin'                            => $configurationJson['video_field_origin'],
      'video_field_type'                              => $configurationJson['video_field_type'],
      'video_field_title'                             => $configurationJson['video_field_title'],
      'video_field_world_title'                       => $configurationJson['video_field_world_title'],
      'video_field_tagline'                           => $configurationJson['video_field_tagline'],
      'video_field_description'                       => $configurationJson['video_field_description'],
      'video_field_duration'                          => $configurationJson['video_field_duration'],
      'video_field_actors'                            => $configurationJson['video_field_actors'],
      'video_field_directors'                         => $configurationJson['video_field_directors'],
      'video_field_studios'                           => $configurationJson['video_field_studios'],
      'video_field_countries'                         => $configurationJson['video_field_countries'],
      'video_field_genres'                            => $configurationJson['video_field_genres'],
      'video_field_age_group'                         => $configurationJson['video_field_age_group'],
      'video_field_poster'                            => $configurationJson['video_field_poster'],
      'video_field_thumbnail'                         => $configurationJson['video_field_thumbnail'],
      'video_field_year'                              => $configurationJson['video_field_year'],
      'video_field_translator'                        => $configurationJson['video_field_translator'],
      'video_field_kinopoisk_id'                      => $configurationJson['video_field_kinopoisk_id'],
      'video_field_tmdb_id'                           => $configurationJson['video_field_tmdb_id'],
      'video_field_world_art_id'                      => $configurationJson['video_field_world_art_id'],
      'video_field_porno_lab_id'                      => $configurationJson['video_field_porno_lab_id'],
      'video_field_imdb_id'                           => $configurationJson['video_field_imdb_id'],
      'video_field_added_at'                          => $configurationJson['video_field_added_at'],
      'video_field_updated_at'                        => $configurationJson['video_field_updated_at'],
      'video_field_kinopoisk_rating'                  => $configurationJson['video_field_kinopoisk_rating'],
      'video_field_kinopoisk_votes'                   => $configurationJson['video_field_kinopoisk_votes'],
      'video_field_tmdb_rating'                       => $configurationJson['video_field_tmdb_rating'],
      'video_field_tmdb_votes'                        => $configurationJson['video_field_tmdb_votes'],
      'video_field_imdb_rating'                       => $configurationJson['video_field_imdb_rating'],
      'video_field_imdb_votes'                        => $configurationJson['video_field_imdb_votes'],
      'video_field_mpaa_rating'                       => $configurationJson['video_field_mpaa_rating'],
      'video_field_mpaa_votes'                        => $configurationJson['video_field_mpaa_votes'],
      'video_field_player'                            => $configurationJson['video_field_player'],
      'video_field_quality'                           => $configurationJson['video_field_quality'],
      'video_field_trailer'                           => $configurationJson['video_field_trailer'],
      'video_field_magnet_link'                       => $configurationJson['video_field_magnet_link'],
      'video_field_torrent_file'                      => $configurationJson['video_field_torrent_file'],
      'video_field_torrent_size'                      => $configurationJson['video_field_torrent_size'],
      'video_field_torrent_seeds'                     => $configurationJson['video_field_torrent_seeds'],
      'video_field_torrent_leeches'                   => $configurationJson['video_field_torrent_leeches'],
      'video_pattern_duration'                        => $configurationJson['video_pattern_duration'],
      'post_pattern_title'                            => $configurationJson['post_pattern_title'],
      'post_pattern_short_story'                      => $configurationJson['post_pattern_short_story'],
      'post_pattern_full_story'                       => $configurationJson['post_pattern_full_story'],
      'post_pattern_meta_title'                       => $configurationJson['post_pattern_meta_title'],
      'post_pattern_meta_description'                 => $configurationJson['post_pattern_meta_description'],
      'post_pattern_meta_keywords'                    => $configurationJson['post_pattern_meta_keywords'],
      'post_accessory_video_fields'                   => $configurationJson['post_accessory_video_fields'],
      'post_updater_video_fields'                     => $configurationJson['post_updater_video_fields'],
      'post_updater_new_date'                         => $configurationJson['post_updater_new_date'],
      'feed_loader_posts_limit'                       => $configurationJson['feed_loader_posts_limit'],
      'categories_case'                               => $configurationJson['categories_case'],
      'categories_from_video_type'                    => $configurationJson['categories_from_video_type'],
      'categories_from_video_genres'                  => $configurationJson['categories_from_video_genres'],
      'images_auto_download'                          => $configurationJson['images_auto_download'],
      'images_overwrite_download'                     => $configurationJson['images_overwrite_download'],
      'images_download_path'                          => $configurationJson['images_download_path'],
      'torrents_auto_download'                        => $configurationJson['torrents_auto_download'],
      'torrents_overwrite_download'                   => $configurationJson['torrents_overwrite_download'],
      'torrents_download_path'                        => $configurationJson['torrents_download_path'],
      'action_user_name'                              => $configurationJson['action_user_name']
    ];

    return array_merge(
      $configuration,
      $overrides
    );
  }
}
