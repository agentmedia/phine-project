locksToggler = function ()
{
    this._superCheckBoxes = $('legend input[type="checkbox"]');
};


locksToggler.prototype.init = function ()
{
    var me = this;
    $(this._superCheckBoxes).each(function (idx, superCheckBox)
    {
        me._initCheckBoxSet(superCheckBox);
    });
};

locksToggler.prototype._initCheckBoxSet = function (superCheckBox)
{
    var subs = this._findSubCheckBoxes(superCheckBox);
    this._setSuperCheckBoxState(superCheckBox, subs);
    var me = this;
   
    $(superCheckBox).click(function () {
        me._onSuperChange(superCheckBox, subs);
    });
    $(subs).each(function (idx, sub) {
        $(sub).change(function () {
            me._setSuperCheckBoxState(superCheckBox, subs);
        });
    });
};

locksToggler.prototype._onSuperChange = function (superCheckBox, subs)
{
    
    if ($(subs).filter(':checked').length > 0)
    {
        $(subs).prop('checked', false);
    }
    else
    {
        $(subs).prop('checked', true);
    }
    this._setSuperCheckBoxState(superCheckBox, subs);
};


locksToggler.prototype._setSuperCheckBoxState = function (superCheckBox, subs)
{
    var checkedSubs = $(subs).filter(':checked');
    if ($(checkedSubs).length === 0)
    {
        $(superCheckBox).prop('checked', false);
        $(superCheckBox).prop('indeterminate', false);
    }
    else if ($(checkedSubs).length < $(subs).length)
    {
        $(superCheckBox).prop('checked', false);
        $(superCheckBox).prop('indeterminate', true);
    }
    else
    {
        $(superCheckBox).prop('checked', true);
        $(superCheckBox).prop('indeterminate', false);
    }
};

locksToggler.prototype._findSubCheckBoxes = function (superCheckBox)
{
    var fieldset = $(superCheckBox).parents('legend').parent('fieldset');
    var superId = $(superCheckBox).attr('id');
    return $(fieldset).find('input[type="checkbox"][id!="' + superId + '"]');
};




