
var Song = $class({

    constructor: function(songId, artist, title, duration, filename, songMarkers) {
        scope = this;

        this.songId = String(songId);
        this.artist = artist;
        this.title = title;
        this.duration = duration;
        this.filename = filename;
        this.songData;
        $(document).keypress(function(e) {
            var tag = e.target.tagName.toLowerCase();
            if ( (e.which === 77 || e.which === 109) && tag != 'input' && tag != 'textarea')
                scope.setTimeMarker();
        });
        if (songMarkers.length > 0) scope.initTimeMarkers(songMarkers);
    },

    show: function() {
//                        $('.song-editor').removeClass('hidden');
        $(".spinner").transition({ opacity: 0 }, 300, function() {
            this.hide();
            $(".song-editor").removeClass('hidden').transition({ opacity: 1 }, 300, function() {
                this.show();
            });
        });
    },

    populateForm: function() {
        $('#songId').val(this.songId);
        $('#artist').val(this.artist);
        $('#title').val(this.title);
        $('#duration').append(this.duration);
    },

    downloadSongData: function() {
        var xhr = new XMLHttpRequest();
        var url = defaultUri + this.filename + '.mp3';
        xhr.open('GET', url, true);
        xhr.responseType = 'blob';
        xhr.onload = function() {
            scope.songData = xhr.response;
            scope._initPlayer();
            scope.generateWaveform();
        }
        xhr.send();
    },

    _initPlayer: function() {
        // encode source song to base 64 for howler
        var songBase64 = new window.FileReader();
        songBase64.readAsDataURL(scope.songData);
        songBase64.onloadend = function() {
            var base64data = songBase64.result;
            songControl = new Howl({
                src: base64data,
                onplay: function() {
                    scope.showPlayMarker();
                },
                onpause: function() {
                    scope.drawWaveform();
                    scope.updateLineMarker();
                }
            });
        };

        $('#ex1-play-original').on('click', function(){
            songControl.stop().play();
            $('#ex1-play-original').blur;
            $('#ex1-mark').removeClass('disabled');
        });
        $('#ex1-pause').on('click', function(){
            songControl.pause();
        });
        $('#ex1-stop').on('click', function(){
            songControl.stop();
            scope.drawWaveform();
            scope.updateLineMarker();
            $('#ex1-mark').addClass('disabled');
        });
        $('#ex1-mark').on('click', function(){
            scope.setTimeMarker();
        });

    },

    generateWaveform: function() {
        SoundCloudWaveform.generate(scope.songData, {
            canvas_width: canvas_width,
            canvas_height: canvas_height,
            bar_width: 1,
            bar_gap: 0.4,
//                                wave_color: '#ff0000',
            onComplete: function(png, pixels) {
                scope.waveformImage = pixels;
                scope.drawWaveform();
                scope.updateLineMarker();
                scope.show();
                return true;
            }
        });
    },

    drawWaveform: function () {
        ctx.putImageData(scope.waveformImage, 0, 0);
    },

    updateLineMarker: function() {
        for (var i = 0; i < markerList.length; i++) {
            var x = markerList[i]*markerOffset;
            scope.lineDraw(x, 0, x, canvas.height, '#00ff00');
        }
    },

    updateLineSongPos: function(markerPos) {
        var x = markerPos;
        scope.drawWaveform();
        scope.updateLineMarker();
        scope.lineDraw(x, 0, x, canvas.height);
    },

    updateLineCursor: function(e) {
        // fix mouse position relative to canvas
        var r = canvas.getBoundingClientRect(),
            x = e.clientX - r.left,
            y = e.clientY - r.top;
        scope.drawWaveform();
        scope.lineDraw(x, 0, x, canvas.height, '#ff5555');
    },

    lineDraw: function(x1, y1, x2, y2, color) {
        ctx.beginPath();
//                        ctx.moveTo(x1, y1);
//                        ctx.lineTo(x2, y2);
        ctx.rect(x1, y1, 2, y2);
        ctx.fillStyle = color;
        ctx.fill();
        ctx.strokeStyle = color;
        ctx.stroke();
    },
    showPlayMarker: function() {
        window.setInterval(function() {
            if ( (songControl.seek() != 0) && (songControl.playing(0)) ) {
                var currentTime = songControl.seek() * 1000;
                scope.updateLineSongPos(currentTime*markerOffset);
//                                console.log('POS:'+currentTime*markerOffset);
            }
        }, 50);
//                        canvas.onmousemove = scope.updateLineCursor; // track mouse movement
    },

    setTimeMarker: function() {
        var curTime = songControl.seek();
        curTime = curTime.toFixed(3)*1000;
        markerList.push(curTime);
        $('#markers').val( function ( index, val) {
            if (val.length > 0) val += ',';
            return val + curTime; // in milliseconds
        });
        $('#time-markers').append('<a href="#" id="mark-' + curTime + '" title="delete marker" class="marker">' + curTime/1000 + '</a>');
    },

    delTimeMarker: function(timecode) {
        for (var i = 0; i < markerList.length; i++) {
            if (markerList[i] == timecode) {
                markerList.splice(i,1);
            }
        }
        var timeMarkers = $('#markers').val( function ( index, val) {
            val = val.replace(timecode + ',', '');
            val = val.replace(',' + timecode, '');
            val = val.replace(timecode, '');
            return val;
        });
        $('#mark-' + timecode).remove();
        scope.drawWaveform();
        scope.updateLineMarker();

    },

    initTimeMarkers: function(songMarkers) {
        markerList = songMarkers.split(',');
        for (var i = 0; i < markerList.length; i++) {
            $('#time-markers').append('<a href="#" id="mark-' + markerList[i] + '" title="delete marker" class="marker">' + markerList[i]/1000 + '</a>');
            $("#mark-" + markerList[i]).click({thisMarker: markerList[i]}, function(event) {
                scope.delTimeMarker(event.data.thisMarker);
            });

        }
    }


});

if (typeof songId != 'undefined' && songId != null) {
    var s = new Song(songId, songArtist, songTitle, songDuration, songFilename, songMarkers);
    s.downloadSongData();
} else {
    $(".spinner").hide();
    $("#file-uploader").fineUploader({
        debug: true,
        multiple: false,
        element: $('#file-uploader'),
        request: {
            endpoint: 'admin?do=uploadFile'
        },
        validation: {
            allowedExtensions: ['mp3', 'wav', 'ogg'],
            sizeLimit: 10485760 // 10 MB - size is in bytes
        },
        template: 'qq-template-bootstrap',
        classes: {
            success: 'alert alert-success',
            fail: 'alert alert-error'
        }
    }).on('complete', function (event, id, name, responseJSON) {
        var s = new Song(responseJSON["songId"], responseJSON["artist"], responseJSON["title"], responseJSON["duration"], responseJSON["filename"]);
        s.downloadSongData();
        s.populateForm();
        songEditors.push(this.s);
    });
}