/**
 * Universal notation player
 */
var NotationPlayer = $class({

    constructor: function(sheet, tempo, defaultOctave, channelId) {
        this.sheet = sheet;
        this.tempo = parseInt(tempo);
        this.defaultOctave = parseInt(defaultOctave);
        this.channelId = channelId;

        //console.log(sheet);
        //console.log(tempo);
        //console.log(defaultOctave);

        this.soundfontUrl = '/assets/vendor/midi-soundfonts/FluidR3_GM/';
        this.instrument = 'acoustic_grand_piano';
        this.instrumentId = 0;
        this.velocity = 127;
        this.baseNoteLength = 4;

        this.maxKey = 96;
        this.minKey = 0;

        this.noteRegex = new RegExp('^([cdefgah]is|[cdfg]es|es|as|[cdefgah]#|[cdefga]b|[bcdefgah]|_)([,]{0,7}|[\']{0,7})?(16|8|4|2|1)?([.t])?$');

        this.noteToKey = { // very permissive! :)
            'c': 0, 'h#': 0, 'his': 0,
            'c#': 1, 'db': 1, 'cis': 1, 'des': 1,
            'd': 2,
            'd#': 3, 'eb': 3, 'dis': 3, 'es': 3,
            'e': 4, 'fb': 4, 'fes': 4,
            'f': 5, 'e#': 5, 'eis': 5,
            'f#': 6, 'gb': 6, 'fis': 6, 'ges': 6,
            'g': 7,
            'g#': 8, 'ab': 8, 'gis': 8, 'as': 8,
            'a': 9,
            'a#': 10, 'bb': 10, 'ais': 10, 'b': 10,
            'h': 11, 'cb': 11, 'ces': 11
        }
        this.keyToNote = [];
        this.restSymbol = '_';

        this.octaveUpChar = '\'';
        this.octaveDownChar = ',';
        this.trioleChar = 't';
        this.dotChar = '.';

        this.defaultNoteLength = 4;
        this.lastNoteLength = this.defaultNoteLength;

        this.playTimer; // SetTimeout instance

        this.keys;
        this.setSheet(sheet);
        this.setKeyToNote();
    },

    onSongPlay: function() {},
    onSongEnd: function() {},
    onNotePlay: function(noteId) {},
    onNoteStop: function(noteId) {},
    onWrongNote: function(noteName) {},
    onCorrectNotes: function() {},

    initPlayer: function(callbackProgress, callbackSuccess) {
        var that = this;
        MIDI.loadPlugin({
            soundfontUrl: that.soundfontUrl,
            instrument: that.instrument,
            onprogress: function(state, progress) {
                callbackProgress(state, progress)
            },
            onsuccess: function() {
                MIDI.setVolume(that.channelId, 127);
                MIDI.programChange(that.channelId, that.instrumentId);
                callbackSuccess();
            }
        });
    },

    setKeyToNote: function() {
        var keyToNote = [];
        for (var i = 0; i <= this.maxKey; i++) {
            var modulus = i % 12;
            //console.log(i, modulus);
            if (modulus === 0) { keyToNote[i] = 'c'; continue; }
            if (modulus === 1) { keyToNote[i] = 'c#'; continue; }
            if (modulus === 2) { keyToNote[i] = 'd'; continue; }
            if (modulus === 3) { keyToNote[i] = 'd#'; continue; }
            if (modulus === 4) { keyToNote[i] = 'e'; continue; }
            if (modulus === 5) { keyToNote[i] = 'f'; continue; }
            if (modulus === 6) { keyToNote[i] = 'f#'; continue; }
            if (modulus === 7) { keyToNote[i] = 'g'; continue; }
            if (modulus === 8) { keyToNote[i] = 'g#'; continue; }
            if (modulus === 9) { keyToNote[i] = 'a'; continue; }
            if (modulus === 10) { keyToNote[i] = 'a#'; continue; }
            if (modulus === 11) { keyToNote[i] = 'h'; continue; }
        }

        this.keyToNote = keyToNote;
    },

    play: function() {
        this.onSongPlay();
        this.playChain(this.keys, 0);
    },

    playSingle: function(keyId) {
        var that = this;

        MIDI.noteOn(0, that.keys[keyId][['key']], that.velocity, 0);
        that.onNotePlay(keyId);

        that.playTimer = setTimeout(function() {
            MIDI.noteOff(0, that.keys[keyId]['key'], 0.1);
            that.onNoteStop(keyId);
        }, that.getDelay(that.keys[keyId]['length']));


    },

    playChain: function(keys, i) {
        var that = this;

        //console.log(keys[i]);
        //console.log(MIDI.noteToKey[keys[i]['key']]);
        //console.log(i);

        if (i != 0) {
            MIDI.noteOff(0, keys[i - 1]['key'], 0.1);
            that.onNoteStop(i-1);
        }

        if (i != keys.length && keys[i]['key'] != that.restSymbol) {
            MIDI.noteOn(0, keys[i][['key']], that.velocity, 0);
            that.onNotePlay(i);
        }

        if (i < keys.length) {
            //console.log(delay);
            that.playTimer = setTimeout(function() {
                that.playChain(keys, i + 1)
            }, that.getDelay(keys[i]['length']));
        } else {
            that.onSongEnd();
        }

    },

    getDelay: function(noteLength) {
        var that = this;
        return Math.round((1000 / (that.tempo / 60)) * (that.baseNoteLength / noteLength));
    },

    setSheet: function(sheet) {
        this.keys = this.sheetToKeys(sheet)
    },

    setTempo: function(tempo) {
        this.tempo = tempo;
        this.setSheet(this.sheet);
    },

    setOctave: function(octave) {
        this.defaultOctave = octave;
        this.setSheet(this.sheet);
    },

    sheetToKeys: function(sheet) {
        var that = this;
        var wrongCount = 0;
        var sheet = sheet.toLowerCase(); // normalize
        var sheetArray = sheet.split(' ');
        var sheetLength = sheetArray.length;
        that.lastNoteLength = that.defaultNoteLength; // reset note length to default
        var keys = [];
        for (var i = 0; i < sheetLength; i++) {

            if (sheetArray[i].length == 0) continue; // skip space

            var result = that.noteRegex.exec(sheetArray[i]); // regexp match
            if (typeof(result) == 'undefined' || result == null) {
                wrongCount++;
                that.onWrongNote(sheetArray[i]);
                continue;
            } // no match? skip to next pattern

            var noteOctave = that.defaultOctave;

            if (typeof(result[2]) != 'undefined') { // , or ' -> shift octave
                if (result[2][0] == that.octaveUpChar) {
                    noteOctave = noteOctave + result[2].length;
                }

                if (result[2][0] == that.octaveDownChar) {
                    noteOctave = noteOctave - result[2].length;
                }
            }

            var noteLength = that.lastNoteLength;

            if (typeof(result[3]) != 'undefined') { // 1, 2, 4, 8, 16 - denominator of the fraction 1/x -> note length
                noteLength = parseInt(result[3]);
                that.lastNoteLength = noteLength; // change default note length based on this note
            }

            if (typeof(result[4] != 'undefined')) { // . or t -> change length
                if (result[4] == that.trioleChar)
                    noteLength = noteLength / 3;

                if (result[4] == that.dotChar)
                    noteLength = noteLength + noteLength / 2; // note length plus its half
            }

            if (result[1] == that.restSymbol)
                var noteKey = that.restSymbol; // space symbol
            else
                var noteKey = ((1+that.noteToKey[result[1]])+noteOctave*12)-1; // set with the right octave

            keys[i] = {
                key: noteKey,
                length: noteLength
            }

            //console.table(result);
            //console.table(keys[i]);
        }

        if (wrongCount == 0) {
            that.onCorrectNotes();
        }

        return keys;
    },

    stop: function() {
        clearTimeout(this.playTimer);
        this.onSongEnd();

        try { // ugly workaround to swallow error in plugin.webaudio.js
            MIDI.stopAllNotes();
        } catch(err) {

        }
    }

})
