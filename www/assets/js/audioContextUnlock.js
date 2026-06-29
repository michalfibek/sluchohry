/**
 * iOS Safari mutes WebAudio entirely while the hardware ring/silent switch
 * is on, even after AudioContext.resume(). The documented fix (see the
 * howler.js maintainer's confirmation at
 * https://github.com/goldfire/howler.js/issues/753 and the technique at
 * https://stackoverflow.com/a/46839941) is to also play a silent HTML5
 * <audio> tag during the same user gesture that resumes/plays a WebAudio
 * buffer. The override only holds while that tag is actually playing, so it
 * must keep looping for the lifetime of the page - a one-shot blip reverts
 * the page to the muted "ambient" session after it ends. Doing both
 * together also covers the separately-created Howler and MIDI.js
 * AudioContexts.
 */
(function() {
    'use strict';

    // 1/10s of silence, MIT/CC-BY-SA: https://stackoverflow.com/a/46839941
    var SILENT_MP3_DATA_URL = 'data:audio/mp3;base64,//MkxAAHiAICWABElBeKPL/RANb2w+yiT1g/gTok//lP/W/l3h8QO/OCdCqCW2Cw//MkxAQHkAIWUAhEmAQXWUOFW2dxPu//9mr60ElY5sseQ+xxesmHKtZr7bsqqX2L//MkxAgFwAYiQAhEAC2hq22d3///9FTV6tA36JdgBJoOGgc+7qvqej5Zu7/7uI9l//MkxBQHAAYi8AhEAO193vt9KGOq+6qcT7hhfN5FTInmwk8RkqKImTM55pRQHQSq//MkxBsGkgoIAABHhTACIJLf99nVI///yuW1uBqWfEu7CgNPWGpUadBmZ////4sL//MkxCMHMAH9iABEmAsKioqKigsLCwtVTEFNRTMuOTkuNVVVVVVVVVVVVVVVVVVV//MkxCkECAUYCAAAAFVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVVV';

    var silentLoopTag = null;

    function playSilentHtml5Audio() {
        if (silentLoopTag) {
            // Already created - just make sure it's still actually playing
            // (iOS can pause it on a phone call, AirPlay switch, etc.)
            if (silentLoopTag.paused) {
                silentLoopTag.play();
            }
            return;
        }

        var tag = document.createElement('audio');
        tag.controls = false;
        tag.preload = 'auto';
        tag.loop = true;
        tag.src = SILENT_MP3_DATA_URL;
        tag.addEventListener('pause', function() {
            // Browsers refuse .play() calls made outside a user gesture, so
            // only the next touch/click handler below can restart this.
            silentLoopTag = null;
        });

        var p = tag.play();
        if (p && typeof p.catch === 'function') {
            p.catch(function() {});
        }

        silentLoopTag = tag;
    }

    function unlockWebAudioContext(ctx) {
        if (!ctx) return;

        if (ctx.state === 'suspended') {
            ctx.resume();
        }

        var source = ctx.createBufferSource();
        source.buffer = ctx.createBuffer(1, 1, 22050);
        source.connect(ctx.destination);
        if (typeof source.start === 'undefined') {
            source.noteOn(0);
        } else {
            source.start(0);
        }
    }

    function unlockAudio() {
        playSilentHtml5Audio();

        if (window.Howler && Howler.ctx) {
            unlockWebAudioContext(Howler.ctx);
        }

        if (window.MIDI && MIDI.WebAudio && typeof MIDI.WebAudio.getContext === 'function') {
            unlockWebAudioContext(MIDI.WebAudio.getContext());
        }
    }

    document.addEventListener('touchend', unlockAudio, false);
    document.addEventListener('mousedown', unlockAudio, false);
})();
