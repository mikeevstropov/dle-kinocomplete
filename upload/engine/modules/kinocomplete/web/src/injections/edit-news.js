
import Kinocomplete from '../index';
import axios from 'axios';

(async () => {

  const configuration = new Kinocomplete.Configuration({
    client: axios,
  });

  await configuration.load();

  if (configuration.get('autocomplete_edit_post_enabled')) {

    const layout = new Kinocomplete.Layout({
      configuration,
    });

    const api = new Kinocomplete.Api({
      client: axios,
    });

    new Kinocomplete.Autocomplete({
      configuration,
      layout,
      api,
    });
  }

})();
