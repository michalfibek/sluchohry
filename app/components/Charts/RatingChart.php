<?php

namespace App\Components\Charts;

use Contributte\Translation\Translator;
use Nette,
    Nette\Application\UI,
    Nette\Security\Authorizator,
    Nette\Security\User,
    App\Model,
    Contributte\Datagrid\Datagrid,
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
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(User $user, Model\Score $score, Model\Game $game, Model\User $userModel, Translator $translator)
    {
        $this->user = $user;
        $this->score = $score;
        $this->game = $game;
        $this->userModel = $userModel;
        $this->translator = $translator;
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
        $grid = new Datagrid();
        $this->addComponent($grid, $name);
        $grid->setPrimaryKey('user_id');
        $grid->setDataSource($this->score->getListByGame($this->gameId, $this->difficultyId, NULL, 15));

        $grid->addColumnText('realname', 'front.ratings.name');
        $grid->addColumnNumber('score', 'front.ratings.score');

        $grid->setPagination(false);

        $grid->setTranslator($this->translator);

        $grid->setRowCallback(function ($item, $tr) {
            if ($item['user_id'] == $this->user->getId())
                $tr->class[] = 'highlight-row';

            return $tr;

        });

        return $grid;
    }

    public function render($difficultyId = NULL)
    {
        if ($difficultyId) $this->difficultyId = $difficultyId;
        $this->template->difficulty = $this->difficultyId;
        $this->template->setFile(__DIR__ . '/RatingChart.latte');
        $this->template->render();
    }

}

interface IRatingChartFactory
{
    function create(): RatingChart;
}