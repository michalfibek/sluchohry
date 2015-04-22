/**
* Gaming system
*/
var Game = $class({

    constructor: function (timer, logger, difficulty, notationPlayer, sheet) {
        scope = this;
        this.timer = timer;
        this.difficulty = difficulty;
        this.logger = logger;
        this.nPlayer = notationPlayer;
        this.sheet = sheet;
        this.badAttemptCount = 0;
        this.gameName = 'faders';
        this.gameStartHandler = '?do=gameStart';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;

        scope.initNotePlayer();
        scope.initButtons();
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

    switchPlayBtn: function() {
        $('#btn-stop').show();
        $('#btn-play').hide();
    },

    switchStopBtn: function() {
        $('#btn-stop').hide();
        $('#btn-play').show();
    },

    initNotePlayer: function() {
        var callbackProgress = function() {};
        var callbackSuccess = function() {
            scope.showGame();
        };
        scope.nPlayer.initPlayer(callbackProgress, callbackSuccess);

        // callbacks
        scope.nPlayer.onSongEnd = function() {
            scope.switchStopBtn();
        }
        scope.nPlayer.onSongPlay = function() {
            scope.switchPlayBtn();
        }
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
            scope.nPlayer.playOriginal();
        });

        $('#btn-stop').on('click', function(){
            scope.nPlayer.stop();
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
