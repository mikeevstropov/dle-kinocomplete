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
    $feed->setSize(84620157);
    $inject($feed);

    // Feed "series".
    $feed = new Feed();
    $feed->setName('series');
    $feed->setLabel('Все сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials.json');
    $feed->setSize(39028456);
    $inject($feed);

    // Feed "foreign-movies".
    $feed = new Feed();
    $feed->setName('foreign-movies');
    $feed->setLabel('Зарубежные фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/foreign-movie.json');
    $feed->setSize(68080193);
    $inject($feed);

    // Feed "russian-movies".
    $feed = new Feed();
    $feed->setName('russian-movies');
    $feed->setLabel('Русские фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/russian-movie.json');
    $feed->setSize(12988627);
    $inject($feed);

    // Feed "foreign-cartoon-movies".
    $feed = new Feed();
    $feed->setName('foreign-cartoon-movies');
    $feed->setLabel('Зарубежные мультфильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/foreign-cartoon.json');
    $feed->setSize(1810666);
    $inject($feed);

    // Feed "russian-cartoon-movies".
    $feed = new Feed();
    $feed->setName('russian-cartoon-movies');
    $feed->setLabel('Русские мультфильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/russian-cartoon.json');
    $feed->setSize(243875);
    $inject($feed);

    // Feed "soviet-cartoon-movies".
    $feed = new Feed();
    $feed->setName('soviet-cartoon-movies');
    $feed->setLabel('Советские мультфильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/soviet-cartoon.json');
    $feed->setSize(191401);
    $inject($feed);

    // Feed "anime-movies".
    $feed = new Feed();
    $feed->setName('anime-movies');
    $feed->setLabel('Аниме фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('films/anime.json');
    $feed->setSize(1305405);
    $inject($feed);

    // Feed "foreign-series".
    $feed = new Feed();
    $feed->setName('foreign-series');
    $feed->setLabel('Зарубежные сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/foreign-serial.json');
    $feed->setSize(21892085);
    $inject($feed);

    // Feed "russian-series".
    $feed = new Feed();
    $feed->setName('russian-series');
    $feed->setLabel('Русские сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/russian-serial.json');
    $feed->setSize(7138915);
    $inject($feed);

    // Feed "foreign-cartoon-series".
    $feed = new Feed();
    $feed->setName('foreign-cartoon-series');
    $feed->setLabel('Зарубежные мультсериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/cartoon-serial.json');
    $feed->setSize(1412546);
    $inject($feed);

    // Feed "russian-cartoon-series".
    $feed = new Feed();
    $feed->setName('russian-cartoon-series');
    $feed->setLabel('Русские мультсериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/russian-cartoon-serial.json');
    $feed->setSize(145205);
    $inject($feed);

    // Feed "foreign-documentary-series".
    $feed = new Feed();
    $feed->setName('foreign-documentary-series');
    $feed->setLabel('Зарубежные документальные сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/documentary-serial.json');
    $feed->setSize(840320);
    $inject($feed);

    // Feed "russian-documentary-series".
    $feed = new Feed();
    $feed->setName('russian-documentary-series');
    $feed->setLabel('Русские документальные сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/russian-documentary-serial.json');
    $feed->setSize(333230);
    $inject($feed);

    // Feed "multipart-movies".
    $feed = new Feed();
    $feed->setName('multipart-movies');
    $feed->setLabel('Многосерийные фильмы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/multi-part-film.json');
    $feed->setSize(232725);
    $inject($feed);

    // Feed "anime-series".
    $feed = new Feed();
    $feed->setName('anime-series');
    $feed->setLabel('Аниме сериалы');
    $feed->setVideoOrigin(Video::KODIK_ORIGIN);
    $feed->setRequestPath('serials/anime-serial.json');
    $feed->setSize(7033444);
    $inject($feed);
  }
}
