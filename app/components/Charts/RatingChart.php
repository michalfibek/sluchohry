<?php

namespace App\Components\Charts;

use Nette,
    Nette\Application\UI,
    Nette\Security\IAuthorizator,
    Nette\Security\User,
    App\Model,
    Grido\Grid,
    Grido\Components\Filters,
    Tracy\Debugger;

class RatingChart extends UI\Control
{
    /** @var int */
    private $gameId;

    /** @var int */
    private $difficultyId;

    /**
     * @var User
     */
    private $user;

    /**
     * @var Model\Score
     */
    private $score;

    /**
     * @var Model\Game
     */
    private $game;

    /**
     * @var Model\User
     */
    private $userModel;

    public function __construct(User $user, Model\Score $score, Model\Game $game, Model\User $userModel)
    {
        $this->user = $user;
        $this->score = $score;
        $this->game = $game;
        $this->userModel = $userModel;
    }

    /**
     * @param int $gameId
     */
    public function setGameId($gameId)
    {
        $this->gameId = $gameId;
    }

    /**
     * @param int $difficultyId
     */
    public function setDifficultyId($difficultyId)
    {
        $this->difficultyId = $difficultyId;
    }

    protected function createComponentRatingChart($name)
    {
        $grid = new Grid($this, $name);
        $grid->setModel($this->score->getListByGame($this->gameId, $this->difficultyId));

//        $grid->setFilterRenderType(Filter::RENDER_INNER);
        $grid->setTemplateFile(__DIR__.'/simpleGrid.latte');

        $grid->addColumnText('user', 'Name')
            ->setSortable()
            ->setCustomRender(function($item) {
                return $this->userModel->getById($item->id)->realname;
            });

        $grid->addColumnNumber('value', 'Score')
            ->setSortable();

        $grid->addColumnDate('update_time', 'Last update')
            ->setDateFormat('d.m.Y H:i')
            ->setSortable();

//        $grid->addActionHref('delete', 'Delete', 'delete!')
//            ->setIcon('fa fa-remove')
//            ->setConfirm('Do you really want to delete this user\'s score record?')
//            ->setDisable(function ($item) {
//                return !$this->user->isAllowed($this->presenter->getName(), 'delete');
//            });

//        $grid->setRowCallback(function($row, Html $tr) {
//
//        }); // TODO color row for current user score

        $grid->setDefaultSort(array(
            'value' => 'DESC'
        ));

        return $grid;
    }

    public function render()
    {
        $this->template->difficulty = $this->difficultyId;
        $this->template->setFile(__DIR__ . '/ratingChart.latte');
        $this->template->render();
    }

}

interface IRatingChartFactory
{
    /** @return RatingChart */
    function create();
}