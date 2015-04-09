/**
 * Gaming system
 */
var Game = $class({

    constructor: function(timer, logger, shiftSigns, difficulty) {
        scope = this;
        this.timer = timer;
        this.shiftSigns = shiftSigns;
        this.difficulty = difficulty;
        this.logger = logger;
        this.cubeClickCount = 0;
        this.songsLoaded = 0;
        this.gameName = 'noteSteps';
        this.gameStartHandler = '?do=gameStart';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;

        scope.initButtons();
        scope.initTimer();
        //scope.shuffleCards();

        //scope.sendOnLoadRecord();
        //scope.initOnWindowClose();

    },

    isCorrectNote: function(noteName, inputPos) {


        console.log(noteName);
        console.log(inputPos);

        return true;
    },

    initButtons: function() {

        $('.step-input').on('input',function() {
                if (scope.isCorrectNote($(this).val(), $(this).data('pos')))
                    $(this).addClass('correct')
                else
                    $(this).removeClass('correct')
            });
    },

    initTimer: function() {
        scope.timer.start();
    },

    initOnWindowClose: function() {
        if (!scope.gameSolved)
        {
            var that = this;
            $(window).on("beforeunload", function() {
                that.logger.sendResult(that.gameForceEndHandler, that.getResult());
            })
        }
    },

    sendOnLoadRecord: function() {

        var record = {
            gameName: this.gameName,
            difficulty: scope.difficulty,
            shiftSigns: scope.shiftSigns.join()
        }
        this.logger.sendResult(this.gameStartHandler, record);
    },


    getResult: function () {
        var result = {
            gameName: this.gameName,
            steps: scope.cubeClickCount,
            time: scope.timer.getTime(),
            shiftSigns: scope.shiftSigns.join(),
            difficulty: scope.difficulty
        };
        return result;
    },

    evalGame: function() {
        var okay = true;

        if (okay == true) {
            scope.timer.stop();
            scope.gameSolved = true;
            $('.modal-correct').modal('show');
            $('.attempt-count').find('span').empty().append(scope.cubeClickCount);
            //$('#modal-correct').modal('show');
            this.logger.sendResult(this.gameEndHandler, this.getResult());
        } else {
            //console.log('not solved');

            // DEBUG ONLY, possible attempt record
            //var result = {gameName: this.gameName, steps: scope.cubeMoveCount, time: scope.timer.getTime()};
            //this.logger.sendResult(this.gameEndHandler, result);
        }
    }

})