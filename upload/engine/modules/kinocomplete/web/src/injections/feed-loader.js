import Kinocomplete from '../index';
import axios from 'axios';

(async () => {

  const configuration = new Kinocomplete.Configuration({
    client: axios,
  });

  await configuration.load();

  const layout = new Kinocomplete.Layout({
    configuration,
  });

  const api = new Kinocomplete.Api({
    client: axios,
  });

  // Moonwalk.

  const moonwalkView = new Kinocomplete.FeedLoader.SourceView({
    origin: 'moonwalk',
    layout,
  });

  new Kinocomplete.FeedLoader.SourceComponent({
    origin: 'moonwalk',
    view: moonwalkView,
    configuration,
    api,
  });

  // Kodik.

  const kodikView = new Kinocomplete.FeedLoader.SourceView({
    origin: 'kodik',
    layout,
  });

  new Kinocomplete.FeedLoader.SourceComponent({
    origin: 'kodik',
    view: kodikView,
    configuration,
    api,
  });

})();
