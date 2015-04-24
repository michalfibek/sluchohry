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
        $this->gameId = self::GAME_FADERS;
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
        if ($notation = $this->notation->getRandom($this->historyGetAll(), self::GAME_FADERS))
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

        if (!$nextRound) {
            $this->historyClear();
        }

        $this->gameAssets = (isset($id)) ? $this->getAssetsById($id) : $this->getAssetsRandom();
        if (!$this->gameAssets) {

            $msg = Nette\Utils\Html::el('span', $this->translator->translate('front.faders.flash.playedAll'));
            $msg->add( Nette\Utils\Html::el('a', $this->translator->translate('front.faders.flash.playAgain'))->href($this->link('default')) );
            $status = 'success';

            $this->flashMessage($msg, $status);
            $this->redirect(':Front:Default:');
        }

        $this->historyAdd($this->gameAssets['notation']['id']);
    }

    public function renderDefault()
    {
        $this->template->difficulty = $this->difficulty;
        $this->template->notation = $this->gameAssets['notation'];
        $this->template->noteArray = explode(' ', $this->gameAssets['notation']->sheet);
    }

}
