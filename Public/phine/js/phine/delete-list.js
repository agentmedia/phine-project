/**
 * Initializes delete list
 * @returns {deleteList}
 */
function deleteList()
{
    this._anchors = $('.delete-link');
    if (this._anchors.length > 0)
    {
        this._initCancel();
        this._initAnchors();
    }
}

/**
 * Inits the cancel button in the delete modal window
 * @returns {void}
 */
deleteList.prototype._initCancel = function ()
{
    $('#modal-delete-close').click(function (e)
    {
        e.preventDefault();
        $('#delete-modal').foundation('reveal', 'close');
    });
};
/**
 * Adds handlers to all delete links
 * @returns {void}
 */
deleteList.prototype._initAnchors = function ()
{
    var me = this;
    $(this._anchors).each(function (idx, anchor) {
        $(anchor).click(function (e) {
            me._onDeleteClick(e, anchor);
        });
    });
};

/**
 * Handles the delete click by filling and opening the modal
 * @param {event} e The click event
 * @param {$} anchor The anchor
 * @returns {void}
 */
deleteList.prototype._onDeleteClick = function (e, anchor)
{
    e.preventDefault();
    $('#modal-delete-field').val($(anchor).attr('data-id'));
    $('#delete-modal p').text($(anchor).attr('data-description'));
    $('#delete-modal').foundation('reveal', 'open');
};