<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Video\Video;

class MoonwalkFeedsInjector implements FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * @param  callable $inject
   * @return void
   */
  static public function inject(callable $inject) {

    // Feed "foreign-movies".
    $feed = new Feed();
    $feed->setName('foreign-movies');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('movies_foreign.json?api_token={token}');
    $feed->setJsonPointer('/report/movies');
    $feed->setSize(49719268);
    $inject($feed);

    // Feed "russian-movies".
    $feed = new Feed();
    $feed->setName('russian-movies');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('movies_russian.json?api_token={token}');
    $feed->setJsonPointer('/report/movies');
    $feed->setSize(5085601);
    $inject($feed);

    // Feed "camrip-movies".
    $feed = new Feed();
    $feed->setName('camrip-movies');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('movies_camrip.json?api_token={token}');
    $feed->setJsonPointer('/report/movies');
    $feed->setSize(1415144);
    $inject($feed);

    // Feed "foreign-series".
    $feed = new Feed();
    $feed->setName('foreign-series');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('serials_foreign.json?api_token={token}');
    $feed->setJsonPointer('/report/serials');
    $feed->setSize(16970833);
    $inject($feed);

    // Feed "russian-series".
    $feed = new Feed();
    $feed->setName('russian-series');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('serials_russian.json?api_token={token}');
    $feed->setJsonPointer('/report/serials');
    $feed->setSize(5535230);
    $inject($feed);

    // Feed "anime-movies".
    $feed = new Feed();
    $feed->setName('anime-movies');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('movies_anime.json?api_token={token}');
    $feed->setJsonPointer('/report/movies');
    $feed->setSize(1189131);
    $inject($feed);

    // Feed "anime-series".
    $feed = new Feed();
    $feed->setName('anime-series');
    $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
    $feed->setRequestPath('serials_anime.json?api_token={token}');
    $feed->setJsonPointer('/report/serials');
    $feed->setSize(6542717);
    $inject($feed);
  }
}
