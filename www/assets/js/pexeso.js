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

    waitForLoad: function(onSingleLoadCallback) {
        var songCount = this.songCtrl.length;

        for(var i = 0; i < songCount; i++) {
            this.songCtrl[i].on('load', function () {
                onSingleLoadCallback();
            });
        }
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

    constructor: function(song, timer, logger, pairCount, songList, difficulty) {
        scope = this;
        this.song = song;
        this.timer = timer;
        this.songList = songList;
        this.difficulty = difficulty;
        this.logger = logger;
        this.pairCount = pairCount;
        this.cubeClickCount = 0;
        this.songsLoaded = 0;
        this.gameName = 'pexeso';
        this.gameStartHandler = '?do=gameStart';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;
        this.lastCubeId = null;

        scope.initButtons();
        scope.initTimer();
        scope.shuffleCards();

        scope.showOnLoad();
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
        if (cubeIdFirst == null || cubeIdSecond == null || cubeIdFirst == cubeIdSecond)
            return false;
        var first = cubeIdFirst.slice(0, -1);
        var second = cubeIdSecond.slice(0, -1);
        if (first == second && cubeIdFirst.slice(-1) == 'B' && cubeIdSecond.slice(-1) == 'A') return true; else return false;
    },

    showOnLoad: function() {
        scope.song.waitForLoad(function() {
            scope.songsLoaded++;
            if (scope.songsLoaded == scope.pairCount) {
                scope.showGame();
                scope.sendOnLoadRecord();
            }
        })
    },

    initButtons: function() {
        scope.clearHighlights();

        var cubeBtns = $('.cube-play');
        cubeBtns.on('click', function() {
            var cubeId = $(this).attr('id');
            if (!$('#'+cubeId).parent().hasClass('cube-found'))
            {
                var partId = $(this).data('part');
                var songId = $(this).data('song');
                if (scope.isHighlight(cubeId)) // if user clicks on currently playing cube
                {
                    scope.song.stop(songId);
                    scope.clearHighlights();
                } else {

                    scope.song.stopAll();
                    scope.clearHighlights();

                    if (scope.isPair(cubeId, scope.lastCubeId))
                    {
                        $('#'+cubeId).parent().addClass('cube-found');
                        $('#'+scope.lastCubeId).parent().addClass('cube-found');
                        scope.evalGame();
                    } else {
                        scope.cubeClickCount++;
                        scope.song.playPart(function() {
                            scope.clearHighlights(); // callback - clear after songs stops
                        }, songId, partId);
                        scope.addHighlight(cubeId);
                    }
                    scope.lastCubeId = cubeId;
                }
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
        var that = this;
        $(window).on("beforeunload", function() {
            if (!scope.gameSolved)
                that.logger.sendResult(that.gameForceEndHandler, that.getResult());
        });
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
            steps: scope.cubeClickCount,
            time: scope.timer.getTime(),
            songList: scope.songList,
            difficulty: scope.difficulty,
            songId: scope.songId
        };
        return result;
    },

    evalGame: function() {
        var okay = true;

        $('.single-cube').each( function() {
            if (!$(this).hasClass('cube-found'))
                okay = false;
        });

        if (okay == true) {
            scope.timer.stop();
            scope.gameSolved = true;
            $('.modal-correct').modal('show');
            $('.result-steps').find('span').empty().append(scope.cubeClickCount);
            $('.result-time').find('span').empty().append(scope.timer.getTime('sec'));
            //$('#modal-correct').modal('show');
            this.logger.sendResult(this.gameEndHandler, this.getResult(), function(payload) {
                //alert(JSON.stringify(payload, null, 4));

                if (payload['personalRecord'] == true && payload['gameRecord'] == false)
                    $('.result-record').find('personal-record').removeClass('hidden');

                if (payload['gameRecord'] == true && payload['personalRecord'] == false)
                    $('.result-record').find('personal-record').removeClass('hidden');

                if (payload['score'] == true)
                    $('.result-score').find('span').empty().append(payload['score']);
            });
        } else {
            //console.log('not solved');

            // DEBUG ONLY, possible attempt record
            //var result = {gameName: this.gameName, steps: scope.cubeMoveCount, time: scope.timer.getTime()};
            //this.logger.sendResult(this.gameEndHandler, result);
        }
    }

})