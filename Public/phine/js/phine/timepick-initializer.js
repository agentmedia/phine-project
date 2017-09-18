timepickInitializer = function(locale)
{
    this._locale = locale;
    this._hourPickers = $('input[data-type="hour"]');
    this._minutePickers = $('input[data-type="minute"]');
    this._secondPickers = $('input[data-type="second"]');
};

timepickInitializer.prototype.init = function(){
   var me = this;
   $(this._hourPickers).each(function(idx, field){
       me._initHourPicker(field);
   });
   
   $(this._minutePickers).each(function(idx, field){
       me._initMinutePicker(field);
   });
   
   
   $(this._secondPickers).each(function(idx, field){
       me._initSecondPicker(field);
   });
};

timepickInitializer.prototype._initHourPicker = function(field)
{
    var relatedDateID = $(field).attr('id').replace('Hour', 'Date');
    this._initTimePicker(field, $('#' + relatedDateID), 23);
};

timepickInitializer.prototype._initMinutePicker = function(field)
{
    var relatedDateID = $(field).attr('id').replace('Minute', 'Date');
    this._initTimePicker(field, $('#' + relatedDateID), 59);
};

timepickInitializer.prototype._initSecondPicker = function(field)
{
    var relatedDateID = $(field).attr('id').replace('Second', 'Date');
    this._initTimePicker(field, $('#' + relatedDateID), 59);
};


timepickInitializer.prototype._initTimePicker = function(field, dateField, max){
    if (!$(field).val()) {
        $(field).val('00');
    }
    $(field).spinner({numberFormat: "d2"});
    this._restrictRotate(field, 0, max);
    this._addDateFieldHandler(field, dateField);
};

timepickInitializer.prototype._addDateFieldHandler = function(field, dateField)
{
    if ($(dateField).length === 0) {
        return;
    }
    var me = this;
    this._toggleActive(field, dateField);
    $(dateField).change(function(){me._toggleActive(field, dateField);});
};

timepickInitializer.prototype._toggleActive = function(field, dateField)
{
    if ($(dateField).val() !== ''){
        $(field).spinner('enable');
    } else {
        $(field).spinner('disable');
   }
};


timepickInitializer.prototype._restrictRotate = function (field, min, max) {
    $(field).on("spin", function(event, ui){
      if ( ui.value > max ) {
          $(field).spinner("value", min);
          return false;
        } else if ( ui.value < min ) {
          $(field).spinner("value",  max );
          return false;
        }  
    });
      
};



