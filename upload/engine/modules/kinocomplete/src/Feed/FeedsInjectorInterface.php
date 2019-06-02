<?php

namespace Kinocomplete\Feed;

interface FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * Method $add should have three arguments:
   * - Name of the feed.
   * - Video origin from Video constants.
   * - Factory method which will return Feed instance.
   *
   * @param  callable $add
   * @return void
   */
  public static function inject(callable $add);
}
