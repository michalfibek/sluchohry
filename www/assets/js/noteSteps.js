/**
 * Gaming system
 */
var Game = $class({

    constructor: function(timer, logger, firstLetter, shiftSigns, difficulty) {
        scope = this;
        this.timer = timer;
        this.shiftSigns = shiftSigns;
        this.shiftSignsCount = shiftSigns.length;
        this.firstLetter = firstLetter;
        this.difficulty = difficulty;
        this.logger = logger;
        this.correctNotes = [];
        this.badAttemptCount = 0;
        this.gameName = 'noteSteps';
        this.gameStartHandler = '?do=gameStart';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;
        this.noteBtoH = true;

        /** char wanna-be-constants */
        this.FIRST_NOTE_ORD = 97; // a
        this.LAST_NOTE_ORD = 103; // g

        scope.initCorrectNotes();
        scope.initButtons();
        scope.initTimer();
        //scope.shuffleCards();

        scope.sendOnLoadRecord();
        scope.initOnWindowClose();

    },

    isCorrectNote: function(noteInput, inputPos) {

        if (scope.noteBtoH)
        {
            if (noteInput == 'h')
                noteInput = 'b';
            else if (noteInput == 'b')
                return false;
        }

        if (scope.correctNotes[inputPos-1] == noteInput)
            return true;
        else
            return false;
    },

    initCorrectNotes: function() {
        for(var i = 0; i < scope.shiftSignsCount; i++) {

            if (i == 0)
                var baseNote = scope.firstLetter.toLowerCase();
            else
                var baseNote = scope.correctNotes[i-1].toLowerCase();

            if (baseNote == 'h')
                baseNote = 'b';

            if (scope.shiftSigns[i][0] == '+')
                var shiftedNote = baseNote.charCodeAt(0) + parseInt(scope.shiftSigns[i].slice(1));
            if (scope.shiftSigns[i][0] == '-')
                var shiftedNote = baseNote.charCodeAt(0) - parseInt(scope.shiftSigns[i].slice(1));

            // correct the musical scale overflow and underflow
            while (shiftedNote < scope.FIRST_NOTE_ORD)
                shiftedNote = scope.LAST_NOTE_ORD - (scope.FIRST_NOTE_ORD - shiftedNote) + 1;
            while (shiftedNote > scope.LAST_NOTE_ORD)
                shiftedNote = scope.FIRST_NOTE_ORD + (shiftedNote - scope.LAST_NOTE_ORD) - 1;

            scope.correctNotes[i] = String.fromCharCode(shiftedNote);
        }
    },

    initButtons: function() {

        $('.step-input').on('input',function() {
            var input = $(this).val();
            var position = $(this).data('pos');

            if (input.length > 1) { // only one character per input
                $(this).val(input.slice(-1));
                input = $(this).val();
            }

            if (scope.isCorrectNote(input, position)) {
                $(this).removeClass('wrong');
                $(this).addClass('correct');
                scope.evalGame();
                if (position != scope.shiftSignsCount) {  // if not last input
                    var nextId = parseInt(position)+1;
                    $('#step-'+nextId).focus();
                } else {
                    $(this).blur();
                }
            }
            else {
                if (input.length > 0) {
                    scope.badAttemptCount++;
                    $(this).removeClass('correct');
                    $(this).addClass('wrong');
                }
                else {
                    $(this).removeClass('correct');
                    $(this).removeClass('wrong');
                }
            }
        });

        $('#step-1').focus();
    },

    initTimer: function() {
        scope.timer.start();
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
            firstLetter: scope.firstLetter,
            shiftSigns: scope.shiftSigns.join(),
            difficulty: scope.difficulty
        }
        this.logger.sendResult(this.gameStartHandler, record);
    },


    getResult: function () {
        var result = {
            gameName: this.gameName,
            steps: scope.badAttemptCount,
            time: scope.timer.getTime(),
            firstLetter: scope.firstLetter,
            shiftSigns: scope.shiftSigns.join(),
            difficulty: scope.difficulty
        };
        return result;
    },

    evalGame: function() {
        var okay = true;
        $('.step-input').each(function() {
            var input = $(this).val();
            var position = $(this).data('pos');
            if (!scope.isCorrectNote(input, position))
            {
                okay = false;
                return false;
            }
        })
        if (okay == true) {
            scope.timer.stop();
            scope.gameSolved = true;
            $('.modal-correct').modal('show');
            $('.attempt-count').find('span').empty().append(scope.badAttemptCount);
            this.logger.sendResult(this.gameEndHandler, this.getResult());
        }
    }

})