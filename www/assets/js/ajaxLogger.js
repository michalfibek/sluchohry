$(function () {
    $.nette.init();
});

var AjaxLogger = $class({
    constructor: function() {
        scope = this;
    },

    sendResult: function(handlerLink, result) {
        $.nette.ajax({
            type: 'GET',
            url: handlerLink,
            data: {
                'result': result
            }
        });
    }
});