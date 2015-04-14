$(function () {
    $.nette.init();
});

var AjaxLogger = $class({
    constructor: function() {
        scope = this;
    },

    sendResult: function(handlerLink, result, successCallback) {
        successCallback = successCallback || false; // default value

        $.nette.ajax({
            type: 'GET',
            url: handlerLink,
            data: {
                'result': result
            },
            success: function(payload) {
                if (successCallback != false) {
                    //console.log(payload);
                    successCallback(payload);
                }
            }
        });
    }
});