<?php

namespace Kinocomplete\Feed;

interface FeedsInjectorInterface {

  /**
   * Inject feeds.
   *
   * @param  callable $add
   * @return void
   */
  public static function inject(callable $add);
}
