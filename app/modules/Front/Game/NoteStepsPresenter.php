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
        DIFFICULTY_1_RATIO = 1,
        DIFFICULTY_2_RATIO = 2,
        DIFFICULTY_3_RATIO = 3,
        MAX_RAND_MULTIPLY = 2, // multiply difficulty ratio with rand max to this
        FIRST_NOTE_ORD = 97, // a
        LAST_NOTE_ORD = 103; // g

    private $stepRatio;
    private $noteBtoH;
    private $noteCount;

    public function startup()
    {
        parent::startup();
        $this->noteBtoH = true;
        $this->noteCount = 7;
    }

    protected function setAssetsByDifficulty()
    {
        switch ($this->difficulty)
        {
            case 1:
                $this->stepRatio = self::DIFFICULTY_1_RATIO;
                break;
            case 2:
                $this->stepRatio = self::DIFFICULTY_2_RATIO;
                break;
            case 3:
                $this->stepRatio = self::DIFFICULTY_3_RATIO;
                break;
        }
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
        for ($i = 0; $i <= $this->noteCount; $i++) {
            $sign = $signList[rand(0,1)];
            $shiftSigns[] = $sign . rand(1,self::MAX_RAND_MULTIPLY)*$this->stepRatio;
        }

        return array(
            'firstLetter' => $firstLetter,
            'shiftSigns' => $shiftSigns
        );

    }

    public function actionDefault($id = null, $difficulty = 2, $nextRound = null)
    {
        $this->difficulty = (int)$difficulty;

        $this->setAssetsByDifficulty();

        $this->gameAssets = $this->getAssetsRandom();
    }

    public function renderDefault()
    {
        $this->template->difficulty = $this->difficulty;
        $this->template->firstLetter = $this->gameAssets['firstLetter'];
        $this->template->shiftSigns = $this->gameAssets['shiftSigns'];
        $this->template->noteBtoH = $this->noteBtoH;
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
