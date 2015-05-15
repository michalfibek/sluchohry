<?php
namespace App\Module\Front\Game\Presenters;

use Nette,
    App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class NoteStepsPresenter extends \App\Module\Base\Presenters\BaseGamePresenter
{
    // define cube splits count by difficulty
    const
        MAX_RAND_MULTIPLY = 2, // multiply difficulty ratio with rand max to this
        FIRST_NOTE_ORD = 97, // a
        LAST_NOTE_ORD = 103; // g

    private $stepRatio;
    private $noteBtoH;
    private $noteCount;

    public function startup()
    {
        parent::startup();
        $this->gameId = self::GAME_NOTE_STEPS;
        $this->noteBtoH = true;
        $this->noteCount = 7; // default 7
    }

    protected function getAssetsById($id)
    {

    }

    protected function getAssetsRandom()
    {
        $firstLetter = chr(rand(self::FIRST_NOTE_ORD, self::LAST_NOTE_ORD));
        if ($this->noteBtoH && $firstLetter == 'b')
            $firstLetter = 'h';

        $signList = '+-';
        for ($i = 0; $i < $this->noteCount; $i++) {
            $sign = $signList[rand(0,1)];

            // generate non-reversing arrows only on higher difficulty
            if ($i != 0 && $this->difficulty != 1) {
                $shiftValueSingle = NULL;
                $previousSymbol = substr($shiftSigns[$i-1], 0, 1);
                $previousValue =  substr($shiftSigns[$i-1], 1);

                while (($shiftValueSingle == NULL) || (($shiftValueSingle == $previousValue) && ($previousSymbol != $sign))) {
                    $shiftValueSingle = rand(1, self::MAX_RAND_MULTIPLY) * $this->stepRatio;
                    $shiftSignSingle = $sign . $shiftValueSingle;
                }
            } else {
                $shiftSignSingle = $sign . rand(1, self::MAX_RAND_MULTIPLY) * $this->stepRatio;
            }

            $shiftSigns[$i] = $shiftSignSingle;
        }

        return array(
            'firstLetter' => $firstLetter,
            'shiftSigns' => $shiftSigns
        );

    }

    public function actionDefault($id = null, $difficulty = 2, $nextRound = null)
    {
        if (!$nextRound) {
            $this->historyClear();
        }

        $this->difficulty = (int)$difficulty;

        $this->stepRatio = $this->getVariationByDifficulty($this->difficulty);

        $this->gameAssets = $this->getAssetsRandom();

        $this->historyAdd();
    }

    public function renderDefault()
    {
        $this->template->difficulty = $this->difficulty;
        $this->template->firstLetter = $this->gameAssets['firstLetter'];
        $this->template->shiftSigns = $this->gameAssets['shiftSigns'];
        $this->template->noteBtoH = $this->noteBtoH;
//        Debugger::barDump($this->gameAssets);
//        $this->template->firstLetter = 'h';
//        $this->template->shiftSigns = ['+1','-1','+2'];
    }

    protected function createTemplate($class = NULL)
    {
        $template = parent::createTemplate($class);
        $template->addFilter('shiftArrows', function ($input) {
            return $this->getNoteShiftArrows($input);
        });

        return $template;
    }

    /**
     * Converts eg. +2 to two up arrows. Accepts only +NUMBER and -NUMBER formats.
     *
     * @param $inputShift
     * @return string
     */
    protected function getNoteShiftArrows($inputShift)
    {
        if ($inputShift[0] == '+')
            $symbol = '<span class="symbol symbol-up"></span>';
        elseif ($inputShift[0] == '-')
            $symbol = '<span class="symbol symbol-down"></span>';

        $inputShift = substr($inputShift, 1); // remove first letter, the sign

        $returnString = '';

        for ($i = 0; $i<$inputShift; $i++) {
            $returnString .= $symbol;
        }

        return $returnString;
    }
}
