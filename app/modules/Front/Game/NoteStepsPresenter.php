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
        DIFFICULTY_1_RATIO = 3,
        DIFFICULTY_2_RATIO = 4,
        DIFFICULTY_3_RATIO = 8;

    protected $stepRatio;


    public function startup()
    {
        parent::startup();
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

    }

    public function actionDefault($id = null, $difficulty = 2, $nextRound = null)
    {
        $this->difficulty = (int)$difficulty;

        $this->setAssetsByDifficulty();

        $this->gameAssets = (isset($id)) ? $this->getAssetsById($id) : $this->getAssetsRandom();
    }

    public function renderDefault()
    {
        $this->template->difficulty = $this->difficulty;
        $this->template->firstLetter = 'h';
        $this->template->shiftSigns = ['+1','-1','+2'];
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
