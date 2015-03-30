/**
 * Song control class
 */
var Song = $class({

    constructor: function(songUrl, spriteDef) {
        scope = this;

        this.songUrl = songUrl;
        this.spriteDef = spriteDef;

        this.songCtrl; // reference on howler instance
        this.chainTimeout; // reference on timeout in playback

        this.initPlayer();

    },

    initPlayer: function() {
        this.songCtrl = new Howl({
            src: scope.songUrl,
            sprite: scope.spriteDef,
            onload: function() { // after sound file loads, do these...
                g.showGame();
            }
        });
    },

    playPart: function(partId) {
        this.songCtrl.play(partId);
    },

    playPartOnly: function(partId) {
        this.songCtrl.once('end', function() {
            g.clearHighlights();
            g.switchStopBtn();
        }).play(partId);
    },

    stop: function() {
        this.songCtrl.stop();
        clearTimeout(scope.chainTimeout);
        //stopPlay = true;
    }

})

/**
 * Gaming system
 */
var Game = $class({

    constructor: function(song, songId, difficulty, timer, logger, shuffledOrder, colors, chainDef) {
        scope = this;
        this.song = song;
        this.songId = songId;
        this.difficulty = difficulty;
        this.timer = timer;
        this.logger = logger;
        this.colors = colors;
        this.chainDef = chainDef;
        this.songChain = [];
        this.cubeMoveCount = 0;
        this.gameName = 'melodicCubes';
        this.gameEndHandler = '?do=gameEnd';
        this.gameForceEndHandler = '?do=gameForceEnd';
        this.gameSolved = false;

        scope.initButtons();
        scope.initChain();
        scope.initTimer();
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

    getCubeBank: function() {
        var cubeBank = [];
        $('.single-cube').each( function(index, element) {
            var thisId = $(this).find('.cube-play').data('part');
            cubeBank.push(thisId);
        });
        return cubeBank;
    },

    getRandomColor: function() {
        return this.colors.splice([Math.floor(Math.random() * scope.colors.length)],1);
    },

    show: function() {
        $('#btn-stop').hide(); // hide stop button by default
        $(".spinner").transition({ opacity: 0 }, 300, function() {
            this.hide();
            $(".game-main").removeClass('hidden').transition({ opacity: 1 }, 300, function() {
                this.show();
            });
        });
    },

    addHighlight: function(cubeIndex) {
        $('#song-'+cubeIndex).parent().addClass('cube-highlight');
        $('#song-'+cubeIndex).find('.cube-spinner').show();
    },

    isHighlight: function(cubeIndex) {
        return $('#song-'+cubeIndex).parent().hasClass('cube-highlight');
    },

    clearHighlights: function() {
        $('.single-cube').each( function() {
            $(this).removeClass('cube-highlight').transition();
            $(this).find('.cube-spinner').hide();
        });
    },

    switchPlayBtn: function() {
        $('#btn-stop').show();
        $('#btn-play').hide();
    },

    switchStopBtn: function() {
        scope.clearHighlights();
        $('#btn-stop').hide();
        $('#btn-play').show();
    },

    initButtons: function() {

        scope.switchStopBtn();
        scope.clearHighlights();

        var cubeBtns = $('.cube-play');
        cubeBtns.on('click', function() {
            var partId = $(this).data('part');
            if (scope.isHighlight(partId)) // if user clicks on currently playing cube
            {
                scope.song.stop();
                scope.switchStopBtn();
            } else {
                scope.switchStopBtn();
                scope.switchPlayBtn();
                scope.song.stop();
                scope.song.playPartOnly(partId);
                scope.addHighlight(partId);
            }

        });
        cubeBtns.each( function() {
            $(this).css("background-color", scope.getRandomColor());
        })

        $('#btn-play').on('click', function(){
            scope.song.stop();
            scope.switchPlayBtn();
            scope.songChain[0](scope.playChain(1));
            //songCubesChain[0](playChain(1));
        });

        $('#btn-stop').on('click', function(){
            scope.song.stop();
            scope.switchStopBtn();
        });

        $('#btn-eval').on('mouseup', function(){
            scope.evalGame();
        });

        $("#play-cubes").sortable({
            animation: 150,
            onEnd: function(evt) {
                evt.oldIndex;
                evt.newIndex;
                scope.cubeMoveCount++;
                scope.song.stop();
                scope.switchStopBtn();
            }
        });

        $('.btn-return-game').on('click', function() {
            $('.modal-wrong').modal('hide');
            scope.timer.start(); // re-run timeout on return to game
        })
    },

    initChain: function () {
        var chainCount = scope.chainDef.length;
        for(var i = 0; i < chainCount; i++) {
            this.songChain.push(scope.chainFunctionGenerator(i, scope.chainDef[i], scope.song.songCtrl, scope.getCubeBank));
        }
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

    chainFunctionGenerator: function(markerIndex, markerValue, songCtrl, cubeBankFn) {
        return function(fn) {
            var cubeBank = cubeBankFn();
            if (markerIndex !== 0) scope.clearHighlights();
            scope.addHighlight(cubeBank[markerIndex]);

            scope.song.playPart(cubeBank[markerIndex]);
            scope.chainTimeout = setTimeout(function() {
                fn();
                if (markerIndex == cubeBank.length-1)
                {
                    scope.switchStopBtn();
                }
            }, $('#song-'+cubeBank[markerIndex]).data('duration'));
        };
    },

    playChain: function(i) {
        return function () {
            if (scope.songChain[i]) {
                scope.songChain[i](scope.playChain(++i));
            };
        };
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
        scope.switchStopBtn();
        scope.timer.stop();
        var okay = true;
        var cubeBank = scope.getCubeBank();
        var bankLength = cubeBank.length;
        btnEval = $('#btn-eval');
        for(var i = 0; i < bankLength; i++) {
            if (cubeBank[i] !== 'part'+ i) {
                okay = false;
                break;
            }
        }
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