ajaxModalForm = function()
{
    this._modal = $('#ajax-modal');
};

ajaxModalForm.prototype.init = function()
{
    var form = $(this._modal).find('form');
    var submit = $(form).find('input[type="submit"]');
    var me = this;
    
    $(submit).click(function(e)
    {
        e.preventDefault();
        var data = new Object();
        var arrData = $(form).serializeArray();
        $.each(arrData, function(key, val){
            data[val.name] = val.value;
        });
        data[$(submit).attr('name')] = $(submit).attr('value');
        $(me._modal).load($(form).attr('action'), data);
    });
};




