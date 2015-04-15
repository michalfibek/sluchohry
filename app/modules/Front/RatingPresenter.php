<?php

namespace App\Module\Front\Presenters;

use Nette,
    Nette\Application\UI\Multiplier,
    App\Model,
    App\Components,
    Grido\Grid,
    Grido\Components\Filters\Filter;
use Tracy\Debugger;


/**
 * User profile
 */
class RatingPresenter extends \App\Module\Base\Presenters\BasePresenter
{
    /** @inject @var Model\Score */
    public $score;

    /** @inject @var Model\Game */
    public $game;

    /** @inject @var Model\User */
    public $userModel;

    private $gameId;

    private $topThree;


    public function actionDefault($id)
    {
        $this->gameId = $id;
        $this->topThree = $this->score->getTopThree($this->gameId);
    }

    public function	renderDefault()
    {
        $this->template->gameName = $this->game->getById($this->gameId)->name;
        $this->template->topThree = $this->topThree;
        $this->template->podiumImage = file_get_contents('nette.safe://'.WWW_DIR.'/assets/images/podium.svg');

    }

    public function createComponentRatingCharts()
    {
        return new Multiplier(function() {
            $ratingChart = new Components\Charts\RatingChart($this->user, $this->score, $this->game, $this->userModel, $this->translator);
            $ratingChart->setGameId($this->gameId);

            return $ratingChart;
        });

    }

    public function handleDelete($id)
    {

    }

}
