<?php

namespace App\Module\Admin\Presenters;

use Nette,
    App\Model,
    App\Model\Event,
    Nette\Application\UI\Form,
    Tracy\Debugger,
    Contributte\Datagrid\Datagrid;


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
     * @return Datagrid
     */

    protected function createComponentGridAdvanced($name)
    {
        $grid = new Datagrid();
        $this->addComponent($grid, $name);
        $grid->setDataSource($this->event->getAllView());

        $grid->setTranslator($this->translator);

        $grid->addColumnDateTime('event_time', 'admin.events.advanced.title')
            ->setRenderer(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->event_time . '</a>';
            })
            ->setTemplateEscaping(false)
            ->setSortable();
        $grid->addFilterDateRange('event_time', 'admin.events.advanced.title');

        $grid->addColumnText('username', 'admin.events.advanced.username')
            ->setRenderer(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->username . '</a>';
            })
            ->setTemplateEscaping(false)
            ->setSortable();
        $grid->addFilterText('username', 'admin.events.advanced.username');

        $grid->addColumnText('event_name', 'admin.events.advanced.evtClass')
            ->setRenderer(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->event_name . '</a>';
            })
            ->setTemplateEscaping(false)
            ->setSortable();

        $eventClassNames[''] = '';
        foreach ($this->event->getAllEventClass() as $evtClass)
            $eventClassNames[$evtClass->name] = $evtClass->name;

        $grid->addFilterSelect('event_name', 'admin.events.advanced.evtClass', $eventClassNames);

        $grid->addColumnText('event_data', 'admin.events.advanced.evtData')
            ->setRenderer(function($item) {
                $url = $this->link('View', $item->id);
                return '<a href="'. $url . '">' . $item->event_data . '</a>';
            })
            ->setTemplateEscaping(false)
            ->setSortable();
        $grid->addFilterText('event_data', 'admin.events.advanced.evtData');

        $grid->setDefaultSort(array(
            'event_time' => 'DESC'
        ));

        return $grid;
    }

}