/**
 * The rights toggler for backend user groups
 * @returns {rightsToggler}
 */
function rightsToggler()
{
    this._container = $('#group-rights');
    this._select = $('#UserGroup');
}

/**
 * Initializes the toggler
 * @returns {void}
 */
rightsToggler.prototype.init = function()
{
    var me = this;
    this._toggle();
    $(this._select).change(function(){me._toggle();});
};

/**
 * Toggles container visibility by select value
 * @returns {undefined}
 */
rightsToggler.prototype._toggle = function()
{
    if ($(this._select).val() === '')
    {
        $(this._container).hide();
    }
    else
    {
        $(this._container).show();
    }
};