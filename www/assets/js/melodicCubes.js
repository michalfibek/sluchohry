/**
 * Song control class
 */
var Song = $class({

    constructor: function(songUrl, markers, spriteDef) {
        scope = this;

        this.songUrl = songUrl;
        this.markers = markers;
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

    constructor: function(song, shuffledOrder, colors, chainDef) {
        scope = this;
        this.colors = colors;
        this.song = song;
        this.chainDef = chainDef;
        this.songChain = [];

        scope.initButtons();
        scope.initChain();

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
    },

    clearHighlights: function() {
        $('.single-cube').each( function() {
            $(this).removeClass('cube-highlight').transition();
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

        var cubeBtns = $('.cube-play');
        cubeBtns.on('click', function() {
            var partId = $(this).data('part');
            scope.switchStopBtn();
            scope.switchPlayBtn();
            scope.song.stop();
            scope.song.playPartOnly(partId);
            scope.addHighlight(partId);
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
                scope.song.stop();
                scope.switchStopBtn();
            }
        });
    },

    initChain: function () {
        var chainCount = scope.chainDef.length;
        for(var i = 0; i < chainCount; i++) {
            this.songChain.push(scope.chainFunctionGenerator(i, scope.chainDef[i], scope.song.songCtrl, scope.getCubeBank));
        }
    },

    chainFunctionGenerator: function(markerIndex, markerValue, songCtrl, cubeBankFn) {
        return function(fn) {
            var cubeBank = cubeBankFn();
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

    evalGame: function() {
        scope.song.stop();
        scope.switchStopBtn();
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
        //$('#modal-correct').modal('show');
        if (okay == true) {
            $('.modal-correct').modal('show');
        } else {
            $('.modal-wrong').modal('show');
        }
    }

})