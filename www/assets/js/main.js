/**
 * Base class definition.
 * @param definition
 * @returns {*}
 */
var $class = function(definition) {
    var constructor = definition.constructor;
    var parent = definition.Extends;
    if (parent) {
        var F = function() { };
        constructor._superClass = F.prototype = parent.prototype;
        constructor.prototype = new F();
    }
    for (var key in definition) {
        constructor.prototype[key] = definition[key];
    }
    constructor.prototype.constructor = constructor;
    return constructor;
};

/**
 * Put Bootstrap modal to center
 */
function centerModals(){
    $('.modal').each(function(i){
        var $clone = $(this).clone().css('display', 'block').appendTo('body');
        var top = Math.round(($clone.height() - $clone.find('.modal-content').height()) / 2);
        top = top > 0 ? top : 0;
        $clone.remove();
        $(this).find('.modal-content').css("margin-top", top);
    });
}
$('.modal').on('show.bs.modal', centerModals);
$(window).on('resize', centerModals);

/**
 * Shuffle Jquery element
 */
(function($){

    $.fn.shuffle = function() {

        var allElems = this.get(),
            getRandom = function(max) {
                return Math.floor(Math.random() * max);
            },
            shuffled = $.map(allElems, function(){
                var random = getRandom(allElems.length),
                    randEl = $(allElems[random]).clone(true)[0];
                allElems.splice(random, 1);
                return randEl;
            });

        this.each(function(i){
            $(this).replaceWith($(shuffled[i]));
        });

        return $(shuffled);
    };

})(jQuery);