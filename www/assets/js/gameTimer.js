/**
 * Timewatch for game
 */
var GameTimer = $class({

    constructor: function() {
        this.timeCounter = 0; // value in msec*100
        this.t;
    },

    _timer: function() {
        var that = this;
        that.t = setTimeout(function(){that._addTime()}, 100);
    },

    _addTime: function() {
        this.timeCounter++;
        this._timer();
    },

    start: function() {
        this._timer();
    },

    stop: function () {
        clearTimeout(this.t);
    },

    getTime: function(precision) {
        precision = precision || 'msec'; // default precision

        if (precision == 'sec')
            return Math.ceil(this.timeCounter / 10);
        if (precision == 'msec')
            return this.timeCounter * 10;
    }
})
