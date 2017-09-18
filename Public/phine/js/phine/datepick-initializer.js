datepickInitializer = function(locale)
{
    this._locale = locale;
    this._pickers = $('input[data-type="date"]');
};

datepickInitializer.prototype.init = function(){
    $.datepicker.setDefaults( $.datepicker.regional[ this._locale] );
    var me = this;
    $(this._pickers).each(function(idx, field){
        me._initPicker(field);
    });
};

datepickInitializer.prototype._initPicker = function(field)
{
    /*
    var name = $(field).attr('name');
    $(field).attr('name', name + 'Display');
    var altField = $('<input />');
    altField.attr('type', 'hidden');
    altField.attr('name', name);
    altField.insertAfter(field);
     $(field).datepicker({
      altField: altField,
      altFormat: "yy-mm-dd"
    });
    */
     $(field).datepicker();
};
