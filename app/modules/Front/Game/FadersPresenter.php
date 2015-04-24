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

    /** @var \Nette\Http\SessionSection */
    protected $gameHistory;

    public function startup()
    {
        parent::startup();
        $this->gameId = self::GAME_FADERS;

        $this->gameHistory = $this->getSession('fadersHistory');

        if (!$this->getParameter('nextRound')) {
            $this->gameHistory->remove();
//            $this->session->close();
//            $this->session->start();
            Debugger::barDump($this->gameHistory, 'sess after remove');
        }
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
        $omitNotation = ($this->gameHistory->getIterator()->getArrayCopy()) ? $this->gameHistory->getIterator()->getArrayCopy() : null;
//        Debugger::barDump($omitNotation, 'omit array');
//        $omitNotation = null;

        if ($notation = $this->notation->getRandom($omitNotation, self::GAME_FADERS))
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

//        $this->gameHistory = $this->getSession('fadersHistory');
//
//        if (!$this->getParameter('nextRound')) {
////            $this->gameHistory->remove();
////            $this->session->close();
////            $this->session->start();
//            Debugger::barDump($this->gameHistory, 'sess after remove');
//        }

//        $this->gameHistory = $this->getSession(__CLASS__);

        Debugger::log('living component');

        $this->gameAssets = (isset($id)) ? $this->getAssetsById($id) : $this->getAssetsRandom();
        if (!$this->gameAssets) {

            $msg = Nette\Utils\Html::el('span', $this->translator->translate('front.faders.flash.playedAll'));
            $msg->add( Nette\Utils\Html::el('a', $this->translator->translate('front.faders.flash.playAgain'))->href($this->link('default')) );
            $status = 'success';

//            Debugger::log($this->gameHistory);
            Debugger::log($this->gameHistory->getIterator()->getArrayCopy());

            $this->flashMessage($msg, $status);
            $this->redirect(':Front:Default:');
        }

        Debugger::barDump($this->gameHistory, 'sess1');

//        $this->gameHistory++;
//        if (!$this->getParameter('nextRound')) {
//            $this->gameHistory->offsetSet($this->gameAssets['notation']['id'], $this->gameAssets['notation']['id']);
//        } else {
//            $this->gameHistory = $this->gameAssets['notation']['id'];
//        }

        $this->gameHistory->offsetSet($this->gameAssets['notation']['id'], $this->gameAssets['notation']['id']);
        $this->session->close();
//        $this->session->start();

//        Debugger::barDump($this->getSession('fadersHistory')->getIterator(), 'sess2');
//        Debugger::barDump($this->session->getIterator()->getArrayCopy(), 's name');
    }

    public function renderDefault()
    {
        $this->template->difficulty = $this->difficulty;
        $this->template->notation = $this->gameAssets['notation'];
        $this->template->noteArray = explode(' ', $this->gameAssets['notation']->sheet);
    }

}
