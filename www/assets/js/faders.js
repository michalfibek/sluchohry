/**
* Gaming system
*/
var Game = $class({

    constructor: function (timer, logger, notationId, difficulty, notationPlayerOriginal, notationPlayerUser, sheet) {
        scope = this;
        this.timer = timer;
        this.notationId = notationId;
        this.difficulty = difficulty;
        this.logger = logger;
        this.playerOriginal = notationPlayerOriginal;
        this.playerUser = notationPlayerUser;
        this.sheet = sheet;
        this.sliderMoveCount = 0;
        this.userPlayCount = 0;
        this.originalPlayCount = 0;
        this.evalAttempt = 0;
        this.sliderCount = $('.note-slider').length;
        this.gameName = 'faders';
        this.gameStartHandler = '?do=gameStart';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;

        scope.initNotePlayer();
        scope.initButtons();
        scope.initSliders();
        scope.setUserPlayerKeys();
        scope.initTimer();

        scope.sendOnLoadRecord();
        scope.initOnWindowClose();

    },

    showGame: function() {
        $(".spinner").transition({ opacity: 0 }, 300, function() {
            this.hide();
            $(".game-main").removeClass('hidden').transition({ opacity: 1 }, 300, function() {
                this.show();
            });
        });
    },

    setUserPlayerKeys: function() {
        var keysCount = scope.playerUser.keys.length;

        for (var i = 0; i < keysCount; i++) {
            scope.playerUser.keys[i]['key'] = $('#note-slider-' + i).val();
        }
    },

    setUserPlayerKeySingle: function(keyId, value) {
        if (scope.playerUser.keys[keyId]['key'] == value)
            return false; // no change

        scope.playerUser.keys[keyId]['key'] = value;
        return true;
    },

    setActiveSlider: function(sliderId) {
        $('#note-slider-' + sliderId).addClass('active');
    },

    setInactiveSlider: function(sliderId) {
        $('#note-slider-' + sliderId).removeClass('active');
    },

    setInactiveAllSliders: function() {
        $('.note-slider').removeClass('active');
    },

    switchPlayBtn: function() {
        $('#btn-stop').show();
        $('#btn-play').hide();
        $('#btn-play-original').hide();
    },

    switchStopBtn: function() {
        $('#btn-stop').hide();
        $('#btn-play').show();
        $('#btn-play-original').show();
    },

    initNotePlayer: function() {
        var callbackProgress = function() {};
        var callbackSuccess = function() {
            scope.showGame();
        };
        scope.playerOriginal.initPlayer(callbackProgress, callbackSuccess);

        // callbacks original player
        scope.playerOriginal.onSongEnd = function() {
            scope.switchStopBtn();
            scope.setInactiveAllSliders();
        }
        scope.playerOriginal.onSongPlay = function() {
            scope.switchPlayBtn();
        }
        scope.playerOriginal.onNotePlay = function(noteId) {
            scope.setActiveSlider(noteId);
        }
        scope.playerOriginal.onNoteStop = function(noteId) {
            scope.setInactiveSlider(noteId);
        }

        // callbacks user player
        scope.playerUser.onSongEnd = function() {
            scope.switchStopBtn();
            scope.setInactiveAllSliders();
        }
        scope.playerUser.onSongPlay = function() {
            scope.switchPlayBtn();
        }
        scope.playerUser.onNotePlay = function(noteId) {
            scope.setActiveSlider(noteId);
        }
        scope.playerUser.onNoteStop = function(noteId) {
            scope.setInactiveSlider(noteId);
        }
    },

    getResult: function () {
        var result = {
            gameName: this.gameName,
            notationId: scope.notationId,
            difficulty: scope.difficulty,
            sliderCount: scope.sliderCount,
            steps: scope.sliderMoveCount,
            time: scope.timer.getTime(),
            userPlayCount: scope.userPlayCount,
            originalPlayCount: scope.originalPlayCount,
            evalAttempt: scope.evalAttempt
        };
        return result;
    },

    getMinMaxKeys: function() {
        var keysCount = scope.playerOriginal.keys.length;
        var min = scope.playerOriginal.maxKey;
        var max = scope.playerOriginal.minKey;

        for (var i = 0; i < keysCount; i++) {
            if (scope.playerOriginal.keys[i]['key'] < min)
                min = scope.playerOriginal.keys[i]['key'];

            if (scope.playerOriginal.keys[i]['key'] > max)
                max = scope.playerOriginal.keys[i]['key'];
        }

        return {'min': min, 'max': max}
    },

    initSliders: function() {

        var minMax = scope.getMinMaxKeys();
        var min = minMax['min'];
        var max = minMax['max'];
        var average = Math.round((min+max)/2);
        //console.log(min, max, average);

        $('.note-slider').each(function() {
            var sliderId = $(this).data('id');
            var keyRecord = scope.playerOriginal.keys[sliderId];
            $(this).parent().addClass('length-' + keyRecord['length']);
            $(this).noUiSlider({
                orientation: "vertical",
                direction: 'rtl',
                start: [average],
                step: 1,
                range: {
                    'min': min, // absolute min - 0
                    'max': max // absolute max - 128
                },
                format: {
                    to: function ( value ) {
                        return value;
                    },
                    from: function ( value ) {
                        return value;
                    }
                }
            })
            $(this).on({
                change: function(evt, val) {
                    var keyId = $(evt.target).data('id');
                    var changed = scope.setUserPlayerKeySingle(keyId, val);
                    scope.playerUser.playSingle(keyId);
                    if (changed == true)
                        scope.sliderMoveCount++
                },
                slide: function(evt, val) {
                    scope.setHandlerColor($(evt.target).data('id'), val);
                }
            })

            $(this).Link('lower').to('-inline-', function ( value ) {

                // The tooltip HTML is 'this', so additional
                // markup can be inserted here.
                $(this).html(
                    '<span>' + scope.playerUser.keyToNote[Math.round(value)] + '</span>'
                );
            });
            scope.setHandlerColor(sliderId, average);
            //$(this).find('noUi-handle')
        })
    },

    setHandlerColor: function(sliderId, keyValue) {

        $('#note-slider-' + sliderId).find('.noUi-handle').removeClass(function (index, css) {
            return (css.match (/(^|\s)color-\S+/g) || []).join(' ');
        }); // remove old class

        $('#note-slider-' + sliderId).find('.noUi-handle').addClass('color-' + keyValue % 11);
    },

    initTimer: function() {
        scope.timer.start()
    },

    initOnWindowClose: function() {
        var that = this;
        $(window).on("beforeunload", function() {
            if (!scope.gameSolved)
                that.logger.sendResult(that.gameForceEndHandler, that.getResult());
        });
    },

    sendOnLoadRecord: function() {

        var record = {
            gameName: this.gameName,
            notationId: scope.notationId,
            difficulty: scope.difficulty,
            sliderCount: scope.sliderCount
        }

        this.logger.sendResult(this.gameStartHandler, record);
    },

    initButtons: function() {
        scope.switchStopBtn();

        $('#btn-play').on('click', function(){
            scope.playerUser.play();
            scope.userPlayCount++;
        });

        $('#btn-play-original').on('click', function(){
            scope.playerOriginal.play();
            scope.originalPlayCount++;
        });

        $('#btn-stop').on('click', function(){
            scope.playerOriginal.stop();
            scope.playerUser.stop();
        });

        $('#btn-eval').on('click', function(){
            scope.evalGame();
            scope.evalAttempt++;
        });

        $('.btn-return-game').on('click', function() {
            $('.modal-wrong').modal('hide');
            scope.timer.start(); // re-run timeout on return to game
        })
    },

    evalGame: function() {
        scope.playerOriginal.stop();
        scope.playerUser.stop();
        scope.timer.stop();
        var okay = true;
        $('.note-slider').each(function() {

            var keyId = $(this).data('id');
            if ( $(this).val() !== scope.playerOriginal.keys[keyId]['key'] ) {
                okay = false;
                return false;
            }
        });
        if (okay == true) {
            scope.gameSolved = true;
            $('.result-steps').find('span').empty().append(scope.sliderMoveCount);
            $('.result-time').find('span').empty().append(scope.timer.getTime('sec'));
            $('.modal-correct').modal('show');
            //$('#modal-correct').modal('show');
            this.logger.sendResult(this.gameEndHandler, this.getResult(), function(payload) {

                if (payload['score'] > 0) {

                    //$('.result-score').find('span').empty().append(payload['score']);
                    $('.result-score').find('span').animateNumber(
                        {
                            number: parseInt(payload['score'])
                        },
                        800,
                        function() { // call after number animation ends

                            if (payload['personalRecord'] == true && payload['gameRecord'] == false) {
                                $('.result-record').find('.empty').hide();
                                $('.personal-record').removeClass('hidden').transition({opacity: 1}, 400, function () {
                                    this.show();
                                });
                            }

                            if (payload['gameRecord'] == true) {
                                $('.result-record').find('.empty').hide();
                                $('.game-record').removeClass('hidden').transition({opacity: 1}, 400, function () {
                                    this.show();
                                });
                            }

                        }
                    )
                }
            });
        } else {
            $('.modal-wrong').modal('show');

            // DEBUG ONLY, possible attempt record
            //var result = {gameName: this.gameName, steps: scope.getSteps(), time: scope.timer.getTime()};
            //this.logger.sendResult(this.gameEndHandler, result);
        }
    }
});