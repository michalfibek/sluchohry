/**
 * Gaming system
 */
var EditNotation = $class({

    constructor: function (notationPlayer, tempoList, octaveList) {
        scope = this;
        this.nPlayer = notationPlayer;
        this.tempoList = tempoList;
        this.octaveList = octaveList;

        scope.initNotePlayer();
        scope.initButtons();
        scope.initInputChangeListeners();
        //scope.shuffleCards();

        //scope.sendOnLoadRecord();
        //scope.initOnWindowClose();

    },

    switchPlayBtn: function() {
        $('#btn-stop').show();
        $('#btn-play').hide();
    },

    switchStopBtn: function() {
        $('#btn-stop').hide();
        $('#btn-play').show();
    },

    initInputChangeListeners: function() {

        $('#octave').change(function() {
            scope.nPlayer.setOctave(scope.octaveList[$(this).val()]);
            scope.nPlayer.stop();
        })
        $('#tempo').change(function() {
            scope.nPlayer.setTempo(scope.tempoList[$(this).val()]);
            scope.nPlayer.stop();
        })
        $('#sheet').change(function() {
            scope.clearWrongNotes();
            scope.nPlayer.setKeys($(this).val());
        })
    },

    initNotePlayer: function() {
        var callbackProgress = function() {};
        var callbackSuccess = function() {};
        scope.nPlayer.initPlayer(callbackProgress, callbackSuccess);

        // callbacks
        scope.nPlayer.onSongEnd = function() {
            scope.switchStopBtn();
        }
        scope.nPlayer.onSongPlay = function() {
            scope.switchPlayBtn();
        }
        scope.nPlayer.onWrongNote = function(wrongNote) {
            scope.writeWrongNote(wrongNote);
        }
        scope.nPlayer.onCorrectNotes = function() {
            scope.clearWrongNotes();
        }
    },

    initTimer: function() {
        scope.timer.start()
    },

    clearWrongNotes: function() {
        $('#wrong-input-list').find('span').empty();
    },

    writeWrongNote: function(wrongNote) {
        $('#wrong-input-list').find('span').append(wrongNote + ' ');
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
