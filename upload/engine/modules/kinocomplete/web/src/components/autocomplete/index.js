import SuggestionDialogView from './suggestion-dialog-view';
import ErrorsDialogView from './errors-dialog-view';
import BaseComponent from '../../base/component';
import {requiredArgument} from '../../utils';
import WatchJS from 'melanke-watchjs';
import Cookies from 'js-cookie';
import Layout from '../layout';
import View from './view';
import Api from '../api';

export default class Autocomplete extends BaseComponent {

  /**
   * Api instance.
   *
   * @type {Api}
   * @protected
   */
  api = null;

  /**
   * View instance.
   *
   * @type {View}
   * @protected
   */
  view = null;

  /**
   * ErrorsDialogView instance.
   *
   * @type {ErrorsDialogView}
   * @protected
   */
  errorsDialogView = null;

  /**
   * SuggestionDialogView instance.
   *
   * @type {SuggestionDialogView}
   * @protected
   */
  suggestionDialogView = null;

  /**
   * Overlay.
   *
   * @type {boolean}
   */
  overlay = false;

  /**
   * Autocomplete suggestion.
   *
   * @type {Array}
   * @protected
   */
  suggestion = [];

  /**
   * Suggestion loading state.
   *
   * @type {boolean}
   * @protected
   */
  suggestionLoading = false;

  /**
   * Suggestion loading index.
   *
   * @type {number}
   * @protected
   */
  suggestionLoadingIndex = 0;

  /**
   * Suggestion loading errors.
   *
   * @type {Array}
   * @protected
   */
  suggestionLoadingErrors = [];

  /**
   * Selected video.
   *
   * @type {null}
   * @protected
   */
  selectedVideo = null;

  /**
   * Video loading state.
   *
   * @type {boolean}
   * @protected
   */
  videoLoading = false;

  /**
   * Video loading index.
   *
   * @type {number}
   * @protected
   */
  videoLoadingIndex = 0;

  /**
   * Video loading errors.
   *
   * @type {Array}
   * @protected
   */
  videoLoadingErrors = [];

  /**
   * Autocomplete constructor.
   *
   * @param {Configuration} configuration
   * @param {Layout} layout
   * @param {Api} api
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
    layout = requiredArgument('layout'),
    api = requiredArgument('api'),
  } = {}) {

    super({configuration});

    if (!(layout instanceof Layout))
      throw new Error(
        'Argument "layout" must be an instance of Layout class.'
      );

    if (!(api instanceof Api))
      throw new Error(
        'Argument "api" must be an instance of Api class.'
      );

    this.api = api;
    this.view = new View({configuration, layout});
    this.errorsDialogView = new ErrorsDialogView({layout});
    this.suggestionDialogView = new SuggestionDialogView({configuration, layout});
    this.overlay = Cookies.get('kc-autocomplete-overlay') === '1';

    this.watchProperties();
    this.init();

    console.log('Autocomplete initialized.');
  }

  /**
   * Watch properties.
   *
   * @return {void}
   * @protected
   */
  watchProperties () {

    WatchJS.watch(this, 'overlay', () => {

      Cookies.set(
        'kc-autocomplete-overlay',
        this.overlay ? 1 : 0,
        {expires: 12}
      );
    });

    WatchJS.watch(this, 'suggestion', () => {

      this.suggestionDialogView.updateContent({
        videos: this.suggestion,
        onClick: this.selectVideo.bind(this),
      });
    });

    WatchJS.watch(this, 'suggestionLoading', () => {

      this.suggestionDialogView.setSearchFieldLoading(
        this.suggestionLoading
      );
    });

    WatchJS.watch(this, 'suggestionLoadingErrors', () => {

      this.suggestionDialogView.updateErrorsContainer(
        this.suggestionLoadingErrors
      );
    });

    WatchJS.watch(this, 'selectedVideo', () => {

      this.view.updateFields({
        video: this.selectedVideo,
        clear: !this.overlay,
      });

      this.suggestionDialogView.toggleDialog();
    });

    WatchJS.watch(this, 'videoLoading', () => {

      this.suggestionDialogView.setDialogLoading(
        this.videoLoading
      );
    });

    WatchJS.watch(this, 'videoLoadingErrors', () => {

      this.suggestionDialogView.updateErrorsContainer(
        this.videoLoadingErrors
      );
    });
  }

  /**
   * Init interface.
   *
   * @return {Promise<void>}
   * @protected
   */
  async init () {

    await this.view.appendStyle();

    const errors = this.configuration.get('module_errors');

    if (errors.length) {

      await this.errorsDialogView.appendDialog();

      this.errorsDialogView.updateContent(errors);

      await this.view.appendTrigger({
        error: true,
        onClick: this.errorsDialogView.toggleDialog.bind(
          this.errorsDialogView
        ),
      });

    } else {

      await this.suggestionDialogView.appendDialog({
        onClear: this.view.clearFields.bind(this.view),
        onSearch: this.loadSuggestion.bind(this),
      });

      await this.suggestionDialogView.appendOverlayCheckbox({
        overlay: this.overlay,
        onChange: this.overlayHandler.bind(this),
      });

      this.suggestionDialogView.updateContent();

      await this.view.appendTrigger({
        onClick: this.suggestionDialogView.toggleDialog.bind(
          this.suggestionDialogView
        ),
      });
    }
  }

  /**
   * Overlay handler.
   *
   * @param event
   */
  overlayHandler (event) {

    this.overlay = event.target.checked;
  }

  /**
   * Load suggestion.
   *
   * @param  {string} query
   * @return {void}
   * @protected
   */
  loadSuggestion (query) {

    if (query.length < 3) {

      this.suggestion = [];
      return;
    }

    this.suggestionLoading = true;
    const index = ++this.suggestionLoadingIndex;

    this.api.getVideos({
      title: query.trim(),
    }).then(items => {

      if (index !== this.suggestionLoadingIndex)
        return;

      this.suggestion = items;
      this.suggestionLoading = false;
      this.suggestionLoadingErrors = [];

    }).catch(error => {

      if (index !== this.suggestionLoadingIndex)
        return;

      const message = (error.response &&
        error.response.data &&
        error.response.data.message) ||
        'Неизвестная ошибка';

      this.suggestion = [];
      this.suggestionLoading = false;
      this.suggestionLoadingErrors.push(message);
    });
  }

  /**
   * Select video.
   *
   * @param  {string} id
   * @param  {string} origin
   * @return {void}
   * @protected
   */
  selectVideo ({
    id = requiredArgument('id'),
    origin = requiredArgument('origin'),
  } = {}) {

    if (!id)
      throw new Error(
        'Argument "id" is required.'
      );

    if (!origin)
      throw new Error(
        'Argument "origin" is required.'
      );

    this.videoLoading = true;
    const index = ++this.videoLoadingIndex;

    this.api.getAutocompleteVideo({
      id,
      origin,
    }).then(item => {

      if (index !== this.videoLoadingIndex)
        return;

      this.selectedVideo = item;
      this.videoLoading = false;
      this.videoLoadingErrors = [];

    }).catch(error => {

      if (index !== this.videoLoadingIndex)
        return;

      const message = (error.response &&
        error.response.data &&
        error.response.data.message) ||
        'Неизвестная ошибка';

      this.selectedVideo = null;
      this.videoLoading = false;
      this.videoLoadingErrors.push(message);
    });
  }
}
