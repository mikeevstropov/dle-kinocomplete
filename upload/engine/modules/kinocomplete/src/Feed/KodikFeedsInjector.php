<?php

namespace Kinocomplete\Feed;

use Kinocomplete\Video\Video;

class KodikFeedsInjector implements FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * @param  callable $inject
   * @return void
   */
  static public function inject(callable $inject) {

    // Feed "movies".
    $feed = new Feed();
    $feed->setName('movies');
    $feed->setLabel('Все фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films.json');
    $feed->setJsonPointer('/');
    $feed->setSize(22210342);
    $inject($feed);

    // Feed "series".
    $feed = new Feed();
    $feed->setName('series');
    $feed->setLabel('Все сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials.json');
    $feed->setJsonPointer('/');
    $feed->setSize(11315895);
    $inject($feed);

    // Feed "adult".
    $feed = new Feed();
    $feed->setName('adult');
    $feed->setLabel('Adult-ролики');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('episodes.json');
    $feed->setJsonPointer('/');
    $feed->setSize(3228085);
    $inject($feed);

    // Feed "foreign-movies".
    $feed = new Feed();
    $feed->setName('foreign-movies');
    $feed->setLabel('Зарубежные фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/foreign-movie.json');
    $feed->setJsonPointer('/');
    $feed->setSize(18075688);
    $inject($feed);

    // Feed "russian-movies".
    $feed = new Feed();
    $feed->setName('russian-movies');
    $feed->setLabel('Русские фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/russian-movie.json');
    $feed->setJsonPointer('/');
    $feed->setSize(3126536);
    $inject($feed);

    // Feed "foreign-cartoon-movies".
    $feed = new Feed();
    $feed->setName('foreign-cartoon-movies');
    $feed->setLabel('Зарубежные мультфильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/foreign-cartoon.json');
    $feed->setJsonPointer('/');
    $feed->setSize(474159);
    $inject($feed);

    // Feed "russian-cartoon-movies".
    $feed = new Feed();
    $feed->setName('russian-cartoon-movies');
    $feed->setLabel('Русские мультфильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/russian-cartoon.json');
    $feed->setJsonPointer('/');
    $feed->setSize(69578);
    $inject($feed);

    // Feed "soviet-cartoon-movies".
    $feed = new Feed();
    $feed->setName('soviet-cartoon-movies');
    $feed->setLabel('Советские мультфильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/soviet-cartoon.json');
    $feed->setJsonPointer('/');
    $feed->setSize(62436);
    $inject($feed);

    // Feed "anime-movies".
    $feed = new Feed();
    $feed->setName('anime-movies');
    $feed->setLabel('Аниме фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/anime.json');
    $feed->setJsonPointer('/');
    $feed->setSize(403842);
    $inject($feed);

    // Feed "foreign-series".
    $feed = new Feed();
    $feed->setName('foreign-series');
    $feed->setLabel('Зарубежные сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/foreign-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(5974878);
    $inject($feed);

    // Feed "russian-series".
    $feed = new Feed();
    $feed->setName('russian-series');
    $feed->setLabel('Русские сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/russian-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(1880465);
    $inject($feed);

    // Feed "foreign-cartoon-series".
    $feed = new Feed();
    $feed->setName('foreign-cartoon-series');
    $feed->setLabel('Зарубежные мультсериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/cartoon-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(420853);
    $inject($feed);

    // Feed "russian-cartoon-series".
    $feed = new Feed();
    $feed->setName('russian-cartoon-series');
    $feed->setLabel('Русские мультсериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/russian-cartoon-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(50923);
    $inject($feed);

    // Feed "foreign-documentary-series".
    $feed = new Feed();
    $feed->setName('foreign-documentary-series');
    $feed->setLabel('Зарубежные документальные сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/documentary-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(356707);
    $inject($feed);

    // Feed "russian-documentary-series".
    $feed = new Feed();
    $feed->setName('russian-documentary-series');
    $feed->setLabel('Русские документальные сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/russian-documentary-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(203241);
    $inject($feed);

    // Feed "multipart-movies".
    $feed = new Feed();
    $feed->setName('multipart-movies');
    $feed->setLabel('Многосерийные фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/multi-part-film.json');
    $feed->setJsonPointer('/');
    $feed->setSize(63952);
    $inject($feed);

    // Feed "anime-series".
    $feed = new Feed();
    $feed->setName('anime-series');
    $feed->setLabel('Аниме сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/anime-serial.json');
    $feed->setJsonPointer('/');
    $feed->setSize(2365702);
    $inject($feed);
  }
}
