/**
 * Song control class
 */
var Song = $class({

    constructor: function(songDefs) {
        scope = this;
        this.songCtrl = this.initPlayer(songDefs); // array of song controls
    },

    initPlayer: function(songDefs) {
        var songCtrl = [];
        var songCount = songDefs.length;
        for(var i = 0; i < songCount; i++) {
            songCtrl.push(new Howl(songDefs[i]));
        }

        return songCtrl;
    },

    playPart: function(onStopCallback, songId, partId) {
        var onStopCallback = onStopCallback;
        this.songCtrl[songId].once('end', function() {
            onStopCallback();
            }).play(partId);
    },

    stop: function(songId) {
        this.songCtrl[songId].stop();
        //stopPlay = true;
    },

    stopAll: function() {
        var songCount = this.songCtrl.length;
        for(var i = 0; i < songCount; i++) {
            this.songCtrl[i].stop();
        }
    }

})


/**
 * Gaming system
 */
var Game = $class({

    constructor: function(song, timer, logger, songList, difficulty) {
        scope = this;
        this.song = song;
        this.timer = timer;
        this.songList = songList;
        this.difficulty = difficulty;
        this.logger = logger;
        this.gameName = 'pexeso';
        this.gameStartHandler = '?do=gameStart';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;
        this.lastCubeId = null;

        scope.initButtons();
        scope.initTimer();
        scope.shuffleCards();
        //scope.initOnWindowClose(); // add on logger enabled
        scope.sendOnLoadRecord();

    },

    showGame: function() {
        $(".spinner").transition({ opacity: 0 }, 300, function() {
            this.hide();
            $(".game-main").removeClass('hidden').transition({ opacity: 1 }, 300, function() {
                this.show();
            });
        });
    },

    show: function() {
        $(".spinner").transition({ opacity: 0 }, 300, function() {
            this.hide();
            $(".game-main").removeClass('hidden').transition({ opacity: 1 }, 300, function() {
                this.show();
            });
        });
    },

    addHighlight: function(cubeId) {
        $('#'+cubeId).parent().addClass('cube-highlight');
        $('#'+cubeId).find('.cube-spinner').show();
    },

    isHighlight: function(cubeId) {
        return $('#'+cubeId).parent().hasClass('cube-highlight');
    },

    clearHighlights: function() {
        $('.single-cube').each( function() {
            $(this).removeClass('cube-highlight').transition();
            $(this).find('.cube-spinner').hide();
        });
    },

    isPair: function(cubeIdFirst, cubeIdSecond) {
        if (cubeIdFirst == null || cubeIdSecond == null)
            return false;
        var first = cubeIdFirst.slice(0, -1);
        var second = cubeIdSecond.slice(0, -1);
        if (first == second) return true; else return false;
    },

    initButtons: function() {
        scope.clearHighlights();

        var cubeBtns = $('.cube-play');
        cubeBtns.on('click', function() {
            var cubeId = $(this).attr('id');
            var partId = $(this).data('part');
            var songId = $(this).data('song');
            if (scope.isHighlight(cubeId)) // if user clicks on currently playing cube
            {
                scope.song.stop(songId);
                scope.clearHighlights();
            } else {
                if (scope.isPair(cubeId, scope.lastCubeId))
                {
                    $('#'+cubeId).parent().addClass('cube-found');
                    $('#'+scope.lastCubeId).parent().addClass('cube-found');
                }
                scope.song.stopAll();
                scope.clearHighlights();
                scope.song.playPart(function() {
                    scope.clearHighlights(); // callback - clear after songs stops
                }, songId, partId);
                scope.addHighlight(cubeId);
                scope.lastCubeId = cubeId;
            }

        });

        $('#btn-eval').on('mouseup', function(){
            scope.evalGame();
        });

        $('.btn-return-game').on('click', function() {
            $('.modal-wrong').modal('hide');
            scope.timer.start(); // re-run timeout on return to game
        })
    },

    initTimer: function() {
        scope.timer.start()
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
            songList: scope.songList
        }
        this.logger.sendResult(this.gameStartHandler, record);
    },


    shuffleCards: function() {
        $('.single-cube').shuffle();
    },

    getResult: function () {
        var result = {
            gameName: this.gameName,
            steps: scope.cubeMoveCount,
            time: scope.timer.getTime(),
            difficulty: scope.difficulty,
            songId: scope.songId
        };
        return result;
    },

    evalGame: function() {
        scope.song.stop();
        scope.timer.stop();
        //var okay = true;
        if (okay == true) {
            scope.gameSolved = true;
            $('.modal-correct').modal('show');
            $('.attempt-count').find('span').empty().append(scope.cubeMoveCount);
            //$('#modal-correct').modal('show');
            this.logger.sendResult(this.gameEndHandler, this.getResult());
        } else {
            $('.modal-wrong').modal('show');

            // DEBUG ONLY, possible attempt record
            //var result = {gameName: this.gameName, steps: scope.cubeMoveCount, time: scope.timer.getTime()};
            //this.logger.sendResult(this.gameEndHandler, result);
        }
    }

})