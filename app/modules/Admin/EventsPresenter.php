<?php

namespace App\Module\Admin\Presenters;

use Nette,
    App\Model,
    App\Model\Event,
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
        $this->template->lastPlaysSolved = $this->event->getLastEvents(Event::CLASS_GAME_SOLVED, 15);
        $this->template->lastPlaysClosed = $this->event->getLastEvents(Event::CLASS_GAME_CLOSED, 15);
        $this->template->lastLogins = $this->event->getLastEvents(Event::CLASS_AUTH, 20);
    }

    public function actionAdvanced()
    {

    }

    public function renderAdvanced()
    {

    }

    public function renderView($id)
    {
        $this->template->event = $this->event->getByIdView($id);
    }

    /**
     * @param $name
     * @return Grid
     */

    protected function createComponentGridAdvanced($name)
    {
        $grid = new Grid($this, $name);
        $grid->setModel($this->event->getAllView());

        $grid->setTranslator($this->translator);

//        $grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_INNER);

//        $grid->addColumnNumber('id','id')
//            ->setSortable();

        $grid->addColumnDate('event_time', 'admin.events.advanced.title')
            ->setCustomRender(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->event_time . '</a>';
            })
            ->setDateFormat('d.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();

        $grid->addColumnText('username', 'admin.events.advanced.username')
            ->setCustomRender(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->username . '</a>';
            })
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('event_name', 'admin.events.advanced.evtClass')
            ->setCustomRender(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->event_name . '</a>';
            })
            ->setSortable();

        $eventClassNames[''] = '';
        foreach ($this->event->getAllEventClass() as $evtClass)
            $eventClassNames[$evtClass->name] = $evtClass->name;

        $grid->addFilterSelect('event_name', 'admin.events.advanced.evtClass', $eventClassNames);

        $grid->addColumnText('event_data', 'admin.events.advanced.evtData')
            ->setCustomRender(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->event_data . '</a>';
            })
            ->setSortable()
            ->setFilterText();

        $grid->setDefaultSort(array(
            'event_time' => 'DESC'
        ));

        return $grid;
    }

}