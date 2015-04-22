<?php
namespace App\Module\Front\Game\Presenters;

use App\Module\Base\Presenters\BaseGamePresenter;
use Nette,
    App\Model;
use Tracy\Debugger;

/**
 * Homepage presenter.
 */
class FadersPresenter extends BaseGamePresenter
{
    /** @inject @var Model\Notation */
    public $notation;

    public function startup()
    {
        parent::startup();
        $this->gameId = 4;
    }

    protected function getAssetsById($id)
    {
        if ($notation = $this->notation->getById($id))
        {
            $assets['notation'] = $notation;
            return $assets;
        } else {
            return null;
        }
    }

    protected function getAssetsRandom()
    {
        if ($notation = $this->notation->getRandom())
        {
            $assets['notation'] = $notation;
            return $assets;
        } else {
            return null;
        }
    }

    public function actionDefault($id = null, $difficulty = 2, $nextRound = null)
    {
        $this->difficulty = (int)$difficulty;
//        $this->stepRatio = $this->getVariationByDifficulty($this->difficulty);
        $this->gameAssets = (isset($id)) ? $this->getAssetsById($id) : $this->getAssetsRandom();
    }

    public function renderDefault()
    {
        $this->template->difficulty = $this->difficulty;
        $this->template->notation = $this->gameAssets['notation'];
        $this->template->noteArray = explode(' ', $this->gameAssets['notation']->sheet);
    }

}
