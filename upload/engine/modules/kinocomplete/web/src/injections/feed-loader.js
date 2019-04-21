
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

  const moonwalkView = new Kinocomplete.FeedLoader.MoonwalkView({
    configuration,
    layout,
  });

  new Kinocomplete.FeedLoader.MoonwalkComponent({
    view: moonwalkView,
    configuration,
    api,
  });

})();
