<?php

namespace App\Module\Admin\Presenters;

use Nette,
    App\Model,
    Nette\Application\UI\Form,
    Tracy\Debugger,
    Grido;


/**
 * Sign in/out presenters.
 */
class EventPresenter extends \App\Module\Base\Presenters\BasePresenter
{
    /** @inject @var Model\Event */
    public $event;

    public function actionDefault()
    {
        
    }
    
    public function renderDefault()
    {

    }

    /**
     * @param $name
     * @return Grid
     */

    protected function createComponentGrid($name)
    {
        $grid = new Grido\Grid($this, $name);
        $grid->setModel($this->event->getAllView());

//        $grid->addColumnNumber('id','id')
//            ->setSortable();

        $grid->addColumnDate('event_time', 'Event time')
            ->setDateFormat('d.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();

        $grid->addColumnText('username', 'Username')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('event_name', 'Event name')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('event_data', 'Event data')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('user_ip', 'User IP address')
            ->setSortable()
            ->setFilterText();

        $grid->setDefaultSort(array(
           'event_time' => 'DESC'
        ));
    }

}