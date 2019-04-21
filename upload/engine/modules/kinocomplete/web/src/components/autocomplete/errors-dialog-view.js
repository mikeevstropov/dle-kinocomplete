import BaseView from '../../base/view';

export default class ErrorsDialogView extends BaseView {

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
   * Append dialog.
   *
   * @return {Promise<void>}
   * @public
   */
  async appendDialog () {

    if (this.dialog)
      throw new Error(
        'Errors dialog already appended.'
      );

    const dialog = await this.layout.render({
      fileName: 'autocomplete/errors-dialog.twig',
    });

    this.content = dialog.querySelector(
      '.kc__autocomplete__errors-dialog__errors'
    );

    document
      .querySelector('body')
      .appendChild(dialog);

    const buttons = {
      'Закрыть': function () {
        $(this).dialog('close');
      },
      'Настройки': function () {
        location.href = location.pathname
          +'?mod=kinocomplete&action=settings';
      },
    };

    $('.kc__autocomplete__errors-dialog').dialog({
      autoOpen: false,
      width: 500,
      minHeight: 160,
      resizable: false,
      buttons,
    });

    this.dialog = dialog;
  }

  /**
   * Toggle dialog.
   *
   * @return {void}
   * @public
   */
  toggleDialog () {

    if (!this.dialog)
      throw new Error(
        'Errors dialog has not found.'
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
   * @return {void}
   * @public
   */
  updateContent (errors) {

    if (!this.content)
      throw new Error(
        'Content of errors dialog has not found.'
      );

    this.content.innerHTML = '';

    errors.forEach(message => {

      const element = document.createElement('div');
      element.className = 'kc__autocomplete__errors-dialog__error';
      element.innerText = message;

      this.content.appendChild(element);
    });
  }
}
