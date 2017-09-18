/**
 * The toggler for publishing
 * @returns {publishToggler}
 */
function publishToggler()
{
    this._container = $('#PublishTimes');
    this._cbPublish = $('#Publish');
}

/**
 * Initializes the toggler
 * @returns {void}
 */
publishToggler.prototype.init = function()
{
    var me = this;
    this._toggle();
    $(this._cbPublish).change(function(){me._toggle();});
};

/**
 * Toggles container visibility by checkbox value
 * @returns {undefined}
 */
publishToggler.prototype._toggle = function()
{
    if ($(this._cbPublish).prop('checked')) {
        $(this._container).show();
    } else {
        $(this._container).hide();
    }
};

