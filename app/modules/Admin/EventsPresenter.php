<?php

namespace App\Module\Admin\Presenters;

use Nette,
    App\Model,
    Nette\Application\UI\Form,
    Tracy\Debugger,
    Grido,
    Grido\Grid;


/**
 * Sign in/out presenters.
 */
class EventsPresenter extends \App\Module\Base\Presenters\BasePresenter
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
        $grid = new Grid($this, $name);
        $grid->setModel($this->event->getAllView());

//        $grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_INNER);

//        $grid->addColumnNumber('id','id')
//            ->setSortable();

        $grid->addColumnDate('event_time', 'Event time')
            ->setDateFormat('d.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();

        $grid->addColumnText('username', 'Username')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('event_name', 'Event class')
            ->setSortable();

        $eventClassNames[''] = '';
        foreach ($this->event->getAllEventClass() as $evtClass)
            $eventClassNames[$evtClass->name] = $evtClass->name;

        $grid->addFilterSelect('event_name', 'Event class', $eventClassNames);

        $grid->addColumnText('event_data', 'Event data')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('user_ip', 'User IP address')
            ->setSortable()
            ->setFilterText();

        $grid->setDefaultSort(array(
           'event_time' => 'DESC'
        ));

        return $grid;
    }

}