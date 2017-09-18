/**
 * The rights toggler for backend user groups
 * @returns {membergroupToggler}
 */
function membergroupToggler()
{
    this._container = $('#MemberGroup');
    this._cbGuestsOnly = $('#GuestsOnly');
}

/**
 * Initializes the toggler
 * @returns {void}
 */
membergroupToggler.prototype.init = function()
{
    var me = this;
    this._toggle();
    $(this._cbGuestsOnly).change(function(){me._toggle();});
};

/**
 * Toggles container visibility by checkbox value
 * @returns {undefined}
 */
membergroupToggler.prototype._toggle = function()
{
    if ($(this._cbGuestsOnly).prop('checked')) {
        $(this._container).hide();
    } else {
        $(this._container).show();
    }
};


