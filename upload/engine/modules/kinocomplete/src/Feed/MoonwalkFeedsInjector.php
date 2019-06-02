<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Video\Video;

class MoonwalkFeedsInjector implements FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * @param  callable $add
   * @return void
   */
  static public function inject(callable $add) {

    // Feed "foreign-movies".
    $add(
      'foreign-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_foreign.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(49719268);

        return $feed;
      }
    );

    // Feed "russian-movies".
    $add(
      'russian-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_russian.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(5085601);

        return $feed;
      }
    );

    // Feed "camrip-movies".
    $add(
      'camrip-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('camrip-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_camrip.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(1415144);

        return $feed;
      }
    );

    // Feed "foreign-series".
    $add(
      'foreign-series',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-series');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('serials_foreign.json?api_token={token}');
        $feed->setJsonPointer('/report/serials');
        $feed->setSize(16970833);

        return $feed;
      }
    );

    // Feed "russian-series".
    $add(
      'russian-series',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-series');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('serials_russian.json?api_token={token}');
        $feed->setJsonPointer('/report/serials');
        $feed->setSize(5535230);

        return $feed;
      }
    );

    // Feed "anime-movies".
    $add(
      'anime-movies',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('anime-movies');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('movies_anime.json?api_token={token}');
        $feed->setJsonPointer('/report/movies');
        $feed->setSize(1189131);

        return $feed;
      }
    );

    // Feed "anime-series".
    $add(
      'anime-series',
      Video::MOONWALK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('anime-series');
        $feed->setVideoOrigin(Video::MOONWALK_ORIGIN);
        $feed->setRequestPath('serials_anime.json?api_token={token}');
        $feed->setJsonPointer('/report/serials');
        $feed->setSize(6542717);

        return $feed;
      }
    );
  }
}
