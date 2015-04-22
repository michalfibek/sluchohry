/**
 * Universal notation player
 */
var NotationPlayer = $class({

    constructor: function(sheet, tempo, defaultOctave) {
        scope = this;

        this.sheet = sheet;
        this.tempo = parseInt(tempo);
        this.defaultOctave = parseInt(defaultOctave);

        this.soundfontUrl = '/assets/vendor/midi-soundfonts/FluidR3_GM/';
        this.instrument = 'acoustic_grand_piano';
        this.instrumentId = 0;
        this.velocity = 127;
        this.baseNoteLength = 4;

        this.noteRegex = new RegExp('^(cis|dis|eis|fis|gis|ais|his|ces|des|es|fes|ges|as|hes|c#|d#|e#|f#|g#|a#|h#|cb|db|eb|fb|gb|ab|hb|[bcdefgah])([,]{0,7}|[\']{0,7})?(16|8|4|2|1)?([.t])?$');

        this.noteToKey = { // very permissive! :)
            'c': 0,
            'c#': 1, 'db': 1, 'cis': 1, 'des': 1,
            'd': 2,
            'd#': 3, 'eb': 3, 'dis': 3, 'es': 3,
            'e': 4,
            'f': 5,
            'f#': 6, 'gb': 6, 'fis': 6, 'ges': 6,
            'g': 7,
            'g#': 8, 'ab': 8, 'gis': 8, 'as': 8,
            'a': 9,
            'a#': 10, 'bb': 10, 'ais': 10, 'b': 10,
            'h': 11
        }

        this.octaveUpChar = '\'';
        this.octaveDownChar = ',';
        this.trioleChar = 't';
        this.dotChar = '.';

        this.defaultNoteLength = 4;

        this.playTimer = null;

        scope.keys = scope.sheetToKeys(this.sheet);

        this.initPlayer(function() {}, function() {});

    },

    initPlayer: function(callbackProgress, callbackSuccess) {
        MIDI.loadPlugin({
            soundfontUrl: scope.soundfontUrl,
            instrument: scope.instrument,
            onprogress: function(state, progress) {
                callbackProgress(state, progress)
            },
            onsuccess: function() {
                MIDI.setVolume(0, 127);
                MIDI.programChange(0, scope.instrumentId);
                scope.playOriginal();
                callbackSuccess();
            }
        });
    },

    playOriginal: function() {

        scope.playChain(scope.keys, 0);

    },

    playChain: function(keys, i) {

        console.log(keys[i]);
        console.log(MIDI.noteToKey[keys[i]['key']]);
        //console.log(i);

        if (i != 0) MIDI.noteOff(0, keys[i-1]['key'], 0.1);
        if (i != keys.length) MIDI.noteOn(0, keys[i][['key']], scope.velocity, 0);

        if (i < keys.length) {
            var delay =  (1000 / (scope.tempo / 60)) * (scope.baseNoteLength / keys[i]['length']);
            console.log(delay);
            scope.playTimer = setTimeout(function() {
                    scope.playChain(keys, i + 1)
            }, delay);
        }

    },

    sheetToKeys: function(sheet) {
        var sheet = sheet.toLowerCase(); // normalize
        var sheetArray = sheet.split(' ');
        var sheetLength = sheetArray.length;
        var keys = [];
        for (var i = 0; i < sheetLength; i++) {
            var result = scope.noteRegex.exec(sheetArray[i]); // regexp match
            if (typeof(result) == 'undefined' || result == null) { continue; } // no match? skip to next pattern

            var noteOctave = scope.defaultOctave;

            if (typeof(result[2] != 'undefined')) { // , or ' -> shift octave
                if (result[2] == scope.octaveUpChar)
                    noteOctave = noteOctave+result[2].length;

                if (result[2] == scope.octaveDownChar)
                    noteOctave = noteOctave-result[2].length;
            }

            var noteLength = scope.defaultNoteLength;

            if (typeof(result[3] != 'undefined')) { // 1, 2, 4, 8, 16 - denominator of the fraction 1/x -> note length
                noteLength = parseInt(result[3]);
            }

            if (typeof(result[4] != 'undefined')) { // . or t -> change length
                if (result[4] == scope.trioleChar)
                    noteLength = noteLength / 3;

                if (result[4] == scope.dotChar)
                    noteLength = noteLength + noteLength / 2; // note length plus its half
            }

            var noteKey = ((1+scope.noteToKey[result[1]])+noteOctave*12)-1; // set with the right octave

            keys[i] = {
                key: noteKey,
                length: noteLength
            }

            //console.table(result);
            //console.table(keys[i]);
        }

        return keys;
    },

    stop: function() {
        //clearTimeout(scope.chainTimeout);
        //stopPlay = true;
    }

})
