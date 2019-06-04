<?php

namespace Kinocomplete\Feed;

interface FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * @param  callable $inject
   * @return void
   */
  public static function inject(callable $inject);
}
