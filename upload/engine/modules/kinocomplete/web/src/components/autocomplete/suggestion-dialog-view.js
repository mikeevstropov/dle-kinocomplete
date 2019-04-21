import {requiredArgument} from '../../utils';
import Configuration from '../configuration';
import BaseView from '../../base/view';
import lodash from 'lodash';

export default class SuggestionDialogView extends BaseView {

  /**
   * Configuration instance.
   *
   * @type {Configuration}
   * @protected
   */
  configuration = null;

  /**
   * Dialog.
   *
   * @type {Element}
   * @public
   */
  dialog = null;

  /**
   * Content.
   *
   * @type {Element}
   * @public
   */
  content = null;

  /**
   * Errors container.
   *
   * @type {Element}
   * @public
   */
  errorsContainer = null;

  /**
   * Search field.
   *
   * @type {Element}
   * @public
   */
  searchField = null;

  /**
   * Overlay checkbox.
   *
   * @type {null}
   * @public
   */
  overlayCheckbox = null;

  /**
   * View constructor.
   *
   * @param {Configuration} configuration
   * @param {Layout} layout
   * @public
   */
  constructor ({
    configuration = requiredArgument('configuration'),
    layout = requiredArgument('layout'),
  } = {}) {

    super({layout});

    if (!(configuration instanceof Configuration))
      throw new Error(
        'Argument "configuration" must be an instance of Configuration class.'
      );

    this.configuration = configuration;
  }

  /**
   * Append dialog.
   *
   * @param  {function} onClear
   * @param  {function} onSearch
   * @param  {number} searchDelay
   * @return {Promise<void>}
   * @public
   */
  async appendDialog ({
    onClear,
    onSearch,
    searchDelay = 500,
  } = {}) {

    if (this.dialog)
      throw new Error(
        'Suggestion dialog already appended.'
      );

    const dialog = await this.layout.render({
      fileName: 'autocomplete/suggestion-dialog.twig',
    });

    const content = dialog.querySelector(
      '.kc__autocomplete__suggestion-dialog__content'
    );

    const errorsContainer = dialog.querySelector(
      '.kc__autocomplete__suggestion-dialog__errors'
    );

    const searchField = dialog.querySelector(
      '[name=searchQuery]'
    );

    document
      .querySelector('body')
      .appendChild(dialog);

    searchField.oninput = lodash.debounce(event => {

      if (typeof onSearch === 'function')
        onSearch(event.target.value);

    }, searchDelay);

    const buttons = {
      'Закрыть': function () {
        $(this).dialog('close');
      },
      'Очистить новость': function () {
        onClear();
        $(this).dialog('close');
      },
    };

    $('.kc__autocomplete__suggestion-dialog').dialog({
      autoOpen: false,
      width: 500,
      minHeight: 160,
      resizable: false,
      buttons,
    });

    this.dialog = dialog;
    this.content = content;
    this.errorsContainer = errorsContainer;
    this.searchField = searchField;
  }

  /**
   * Append overlay checkbox.
   *
   * @param  {function} onChange
   * @param  {boolean} overlay
   * @return {Promise<void>}
   */
  async appendOverlayCheckbox ({
    onChange,
    overlay = false,
  } = {}) {

    const wrapper = this.dialog.parentNode;

    if (!wrapper)
      throw new Error(
        'Suggestion dialog is not initialized.'
      );

    const overlayCheckbox = await this.layout.render({
      fileName: 'autocomplete/overlay-checkbox.twig',
      context: {overlay},
    });

    const buttonsPane = wrapper.querySelector(
      '.ui-dialog-buttonpane'
    );

    buttonsPane.appendChild(overlayCheckbox);

    const input = overlayCheckbox.querySelector('input');

    $(input).on('change', onChange);

    this.overlayCheckbox = overlayCheckbox;
  }

  /**
   * Redraw overlay checkbox.
   *
   * @return {void}
   * @public
   */
  redrawOverlayCheckbox () {

    if (!this.overlayCheckbox)
      throw new Error(
        'Overlay checkbox is not found.'
      );

    const systemVersionId = this.configuration
      .get('system_version_id');

    if (systemVersionId < 12) {

      $(this.overlayCheckbox).iButton({
        labelOn: '<i class="icon-ok"></i>',
        labelOff: '<i class="icon-remove"></i>',
        handleWidth: 30,
      });

    } else {

      const ready = this.overlayCheckbox.querySelector('.switchery');
      const input = this.overlayCheckbox.querySelector('input');

      if (ready)
        return;

      new Switchery(input);
    }
  }

  /**
   * Visibility toggle.
   *
   * @return {void}
   * @public
   */
  toggleDialog () {

    if (!this.dialog)
      throw new Error(
        'Suggestion dialog has not found.'
      );

    const isOpened = $(this.dialog)
      .dialog('isOpen');

    if (isOpened) {

      // Close.
      $(this.dialog)
        .dialog('close');

    } else {

      // Open.
      $(this.dialog)
        .dialog('open');

      this.redrawOverlayCheckbox();

      // Reset position.
      $(this.dialog)
        .parent()
        .position({
          my: 'center',
          at: 'center',
          of: window,
        });
    }
  }

  /**
   * Update content.
   *
   * @param  {Array} videos
   * @param  {function} onClick
   * @return {Promise<void>}
   * @public
   */
  async updateContent ({
    videos = [],
    onClick,
  } = {}) {

    if (!this.content)
      throw new Error(
        'Content of suggestion dialog has not found.'
      );

    this.content.innerHTML = '';

    if (videos.length) {

      const elements = [];

      await Promise.all(
        videos.map((item, index) => new Promise(
          (resolve, reject) => {

            const fileName = item.magnetLink || item.torrentFile
              ? 'autocomplete/torrent-ticket.twig'
              : 'autocomplete/video-ticket.twig';

            this.layout.render({
              fileName,
              context: {item},
            }).then(element => {

              elements[index] = element;

              if (typeof onClick === 'function')
                element.onclick = () => onClick(item);

              resolve();

            }).catch(
              reject
            );
          })
        )
      );

      elements.forEach(element => {
        this.content.appendChild(element);
      });

    } else {

      const dummyTicket = await this.layout.render({
        fileName: 'autocomplete/dummy-ticket.twig',
      });

      this.content.appendChild(
        dummyTicket.cloneNode(true)
      );

      this.content.appendChild(
        dummyTicket.cloneNode(true)
      );
    }

    // Reset a dialog position if
    // the dialog was instantiated.
    if ($(this.dialog).hasClass('ui-dialog-content')) {

      $(this.dialog)
        .parent()
        .position({
          my: 'center',
          at: 'center',
          of: window,
        });
    }
  }

  /**
   * Update errors container.
   *
   * @param  {Array<string>} errors
   * @return {Promise}
   * @public
   */
  updateErrorsContainer (errors) {

    if (!this.errorsContainer)
      throw new Error(
        'Errors container of suggestion dialog has not found.'
      );

    this.errorsContainer.innerHTML = '';

    return Promise.all(errors.map(
      message => new Promise((resolve, reject) => {

        this.layout.render({
          fileName: 'autocomplete/error-alert.twig',
          context: {message},
        }).then(element => {

          this.errorsContainer.appendChild(
            element
          );

          resolve();

        }).catch(
          reject
        );
      })
    ));
  }

  /**
   * Set loading status of search field.
   *
   * @param  {boolean} value
   * @return {void}
   * @public
   */
  setSearchFieldLoading (value) {

    if (!this.dialog)
      throw new Error(
        'Suggestion dialog has not found.'
      );

    const field = this.dialog.querySelector(
      '[name=searchQuery]'
    );

    field.parentNode.className = field
      .parentNode
      .className
      .replace(/\sloading/g, '');

    if (value)
      field.parentNode.className += ' loading';
  }

  /**
   * Set dialog loading.
   *
   * @param  {boolean} value
   * @return {void}
   * @public
   */
  setDialogLoading (value) {

    if (!this.dialog)
      throw new Error(
        'Suggestion dialog has not found.'
      );

    this.dialog.className = this.dialog
      .className
      .replace(/\sloading/g, '');

    if (value)
      this.dialog.className += ' loading';
  }
}
