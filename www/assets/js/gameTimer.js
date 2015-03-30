/**
 * Timewatch for game
 */
var GameTimer = $class({

    constructor: function() {
        this.seconds = 0;
        this.t;
    },

    _timer: function() {
        var that = this;
        that.t = setTimeout(function(){that._addTime()}, 1000);
    },

    _addTime: function() {
        this.seconds++;
        this._timer();
        //console.log(this.seconds);
    },

    start: function() {
        this._timer();
    },

    stop: function () {
        clearTimeout(this.t);
    },

    getTime: function() {
        return this.seconds;
    }
})
