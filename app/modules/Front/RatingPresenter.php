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

    /** @inject @var Components\Charts\IRatingChartFactory */
    public $ratingChart;

    private $gameId;


    public function actionDefault($id)
    {
        $this->gameId = $id;
    }

    public function	renderDefault()
    {

    }

    protected function createComponentRatingChartEasy()
    {
        $chart = $this->ratingChart->create();
        $chart->setDifficultyId(1);
        $chart->setGameId($this->gameId);

        return $chart;
    }

    protected function createComponentRatingChartMedium()
    {
        $chart = $this->ratingChart->create();
        $chart->setDifficultyId(2);
        $chart->setGameId($this->gameId);

        return $chart;
    }

    protected function createComponentRatingChartHard()
    {
        $chart = $this->ratingChart->create();
        $chart->setDifficultyId(3);
        $chart->setGameId($this->gameId);

        return $chart;
    }


//    protected function createComponentChartContainer()
//    {
//        $service = $this->ratingChart;
//        return new Multiplier(function ($id) use ($service) {
//            $control = new RatingChart();
//            $chart->setDifficultyId(1);
//            $chart->setGameId($this->gameId);
//            return $control;
//        });
//    }

    public function handleDelete($id)
    {

    }

}
