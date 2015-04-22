///**
// * Gaming system
// */
//var Game = $class({
//
//    constructor: function (timer, logger, difficulty) {
//        scope = this;
//        this.timer = timer;
//        this.noteCount = shiftSigns.length;
//        this.difficulty = difficulty;
//        this.logger = logger;
//        this.correctNotes = [];
//        this.badAttemptCount = 0;
//        this.gameName = 'noteSteps';
//        this.gameStartHandler = '?do=gameStart';
//        this.gameEndHandler = '?do=gameEnd';
//        this.gameForceEndHandler = '?do=gameForceEnd';
//        this.gameSolved = false;
//        this.noteBtoH = noteBtoH;
//
//        /** char wanna-be-constants */
//        this.FIRST_NOTE_ORD = 97; // a
//        this.LAST_NOTE_ORD = 103; // g
//
//        scope.initCorrectNotes();
//        scope.initButtons();
//        scope.initTimer();
//        //scope.shuffleCards();
//
//        scope.sendOnLoadRecord();
//        scope.initOnWindowClose();
//
//    },
//}



    window.onload = function () {
    MIDI.loadPlugin({
        soundfontUrl: "/assets/vendor/midi-soundfonts/FluidR3_GM/",
        instruments: ["acoustic_grand_piano", "acoustic_guitar_nylon", "percussive_organ"],
        onprogress: function(state, progress) {
            console.log(state, progress);
        },
        onsuccess: function() {
            var delay = 0;
            var tempo = 120;
            var velocity = 127;
            var baseNoteLength = 4;
            // play the note
            MIDI.setVolume(0, 127);
            MIDI.setVolume(1, 127);
            MIDI.programChange(0, 17); // midi number - 1
            MIDI.programChange(1, 0); // midi number - 1

            var notes = [
                'D4',
                'E4',
                'Gb4',
                'G4',
                'A4',
                'Gb4',
                'E4',
                'E4',
                'E4',
                'D4',
                'D4'
            ];

            var lengths = [
                8,
                8,
                8,
                8,
                8,
                6,
                16,
                4,
                6,
                16,
                4,
            ];


            var i = 0;

            var f = function() {

                MIDI.noteOff(0, MIDI.keyToNote[notes[i-1]], 0.1);
                MIDI.noteOn(0, MIDI.keyToNote[notes[i]], velocity, 0);

                i++;

                if (i <= notes.length)
                    var timerId = setTimeout(f, (1000 / (tempo / 60)) * (baseNoteLength / lengths[i-1]) );
            }

            f();

        }
    });
};
