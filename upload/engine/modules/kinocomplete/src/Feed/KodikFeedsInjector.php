<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Video\Video;

class KodikFeedsInjector implements FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * @param  callable $add
   * @return void
   */
  static public function inject(callable $add) {

    // Feed "movies".
    $add(
      'movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films.json');
        $feed->setJsonPointer('/');
        $feed->setSize(22210342);

        return $feed;
      }
    );

    // Feed "series".
    $add(
      'series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials.json');
        $feed->setJsonPointer('/');
        $feed->setSize(11315895);

        return $feed;
      }
    );

    // Feed "adult".
    $add(
      'adult',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('adult');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('episodes.json');
        $feed->setJsonPointer('/');
        $feed->setSize(3228085);

        return $feed;
      }
    );

    // Feed "foreign-movies".
    $add(
      'foreign-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films/foreign-movie.json');
        $feed->setJsonPointer('/');
        $feed->setSize(18075688);

        return $feed;
      }
    );

    // Feed "russian-movies".
    $add(
      'russian-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films/russian-movie.json');
        $feed->setJsonPointer('/');
        $feed->setSize(3126536);

        return $feed;
      }
    );

    // Feed "foreign-cartoon-movies".
    $add(
      'foreign-cartoon-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-cartoon-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films/foreign-cartoon.json');
        $feed->setJsonPointer('/');
        $feed->setSize(474159);

        return $feed;
      }
    );

    // Feed "russian-cartoon-movies".
    $add(
      'russian-cartoon-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-cartoon-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films/russian-cartoon.json');
        $feed->setJsonPointer('/');
        $feed->setSize(69578);

        return $feed;
      }
    );

    // Feed "soviet-cartoon-movies".
    $add(
      'soviet-cartoon-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('soviet-cartoon-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films/soviet-cartoon.json');
        $feed->setJsonPointer('/');
        $feed->setSize(62436);

        return $feed;
      }
    );

    // Feed "anime-movies".
    $add(
      'anime-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('anime-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('films/anime.json');
        $feed->setJsonPointer('/');
        $feed->setSize(403842);

        return $feed;
      }
    );

    // Feed "foreign-series".
    $add(
      'foreign-series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/foreign-serial.json');
        $feed->setJsonPointer('/');
        $feed->setSize(5974878);

        return $feed;
      }
    );

    // Feed "russian-series".
    $add(
      'russian-series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/russian-serial.json');
        $feed->setJsonPointer('/');
        $feed->setSize(1880465);

        return $feed;
      }
    );

    // Feed "foreign-cartoon-series".
    $add(
      'foreign-cartoon-series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-cartoon-series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/cartoon-serial.json');
        $feed->setJsonPointer('/');
        $feed->setSize(420853);

        return $feed;
      }
    );

    // Feed "russian-cartoon-series".
    $add(
      'russian-cartoon-series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-cartoon-series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/russian-cartoon-serial.json');
        $feed->setJsonPointer('/');
        $feed->setSize(50923);

        return $feed;
      }
    );

    // Feed "foreign-documentary-series".
    $add(
      'foreign-documentary-series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('foreign-documentary-series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/documentary-serial.json');
        $feed->setJsonPointer('/');
        $feed->setSize(356707);

        return $feed;
      }
    );

    // Feed "russian-documentary-series".
    $add(
      'russian-documentary-series',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('russian-documentary-series');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/russian-documentary-serial.json');
        $feed->setJsonPointer('/');
        $feed->setSize(203241);

        return $feed;
      }
    );

    // Feed "multipart-movies".
    $add(
      'multipart-movies',
      Video::KODIK_ORIGIN,
      function () {

        $feed = new Feed();
        $feed->setName('multipart-movies');
        $feed->setVideoOrigin(Video::KODIK_ORIGIN);
        $feed->setRequestPath('serials/multi-part-film.json');
        $feed->setJsonPointer('/');
        $feed->setSize(63952);

        return $feed;
      }
    );
  }
}
