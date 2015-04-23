/**
* Gaming system
*/
var Game = $class({

    constructor: function (timer, logger, difficulty, notationPlayerOriginal, notationPlayerUser, sheet) {
        scope = this;
        this.timer = timer;
        this.difficulty = difficulty;
        this.logger = logger;
        this.playerOriginal = notationPlayerOriginal;
        this.playerUser = notationPlayerUser;
        this.sheet = sheet;
        this.badAttemptCount = 0;
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
        //scope.shuffleCards();

        //scope.sendOnLoadRecord();
        //scope.initOnWindowClose();

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
            console.log($('.note-slider').val());
        }
    },

    setUserPlayerKeySingle: function(keyId, value) {
        scope.playerUser.keys[keyId]['key'] = value;
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
            var keyRecord = scope.playerOriginal.keys[$(this).data('id')];
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
                    scope.setUserPlayerKeySingle(keyId, val);
                    scope.playerUser.playSingle(keyId);
                    //scope.setUserPlayerKeys();
                }
            })

            $(this).Link('lower').to('-inline-', function ( value ) {

                // The tooltip HTML is 'this', so additional
                // markup can be inserted here.
                $(this).html(
                    '<span>' + value + '</span>'
                );
            });
            //$(this).find('noUi-handle')
        })
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
            difficulty: scope.difficulty
        }
        this.logger.sendResult(this.gameStartHandler, record);
    },

    initButtons: function() {
        scope.switchStopBtn();

        $('#btn-play').on('click', function(){
            scope.playerUser.play();
        });

        $('#btn-play-original').on('click', function(){
            scope.playerOriginal.play();
        });

        $('#btn-stop').on('click', function(){
            scope.playerOriginal.stop();
            scope.playerUser.stop();
        });

        $('#btn-eval').on('mouseup', function(){
            scope.evalGame();
        });
    }
});



//    window.onload = function () {
//    MIDI.loadPlugin({
//        soundfontUrl: "/assets/vendor/midi-soundfonts/FluidR3_GM/",
//        instruments: ["acoustic_grand_piano", "acoustic_guitar_nylon", "percussive_organ"],
//        onprogress: function(state, progress) {
//            console.log(state, progress);
//        },
//        onsuccess: function() {
//            var delay = 0;
//            var tempo = 120;
//            var velocity = 127;
//            var baseNoteLength = 4;
//            // play the note
//            MIDI.setVolume(0, 127);
//            MIDI.setVolume(1, 127);
//            MIDI.programChange(0, 17); // midi number - 1
//            MIDI.programChange(1, 0); // midi number - 1
//
//            var notes = [
//                'D4',
//                'E4',
//                'Gb4',
//                'G4',
//                'A4',
//                'Gb4',
//                'E4',
//                'E4',
//                'E4',
//                'D4',
//                'D4'
//            ];
//
//            var lengths = [
//                8,
//                8,
//                8,
//                8,
//                8,
//                6,
//                16,
//                4,
//                6,
//                16,
//                4,
//            ];
//
//
//            var i = 0;
//
//            var f = function() {
//
//                MIDI.noteOff(0, MIDI.keyToNote[notes[i-1]], 0.1);
//                MIDI.noteOn(0, MIDI.keyToNote[notes[i]], velocity, 0);
//
//                i++;
//
//                if (i <= notes.length)
//                    var timerId = setTimeout(f, (1000 / (tempo / 60)) * (baseNoteLength / lengths[i-1]) );
//            }
//
//            f();
//
//        }
//    });
//};
