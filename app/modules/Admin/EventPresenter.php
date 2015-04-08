<?php

namespace App\Module\Admin\Presenters;

use Nette,
    App\Model,
    Nette\Application\UI\Form,
    Mesour\DataGrid\Grid,
    Mesour\DataGrid\NetteDbDataSource,
    Mesour\DataGrid\Components\Link;
use Tracy\Debugger;


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
    protected function createComponentEventDataGrid($name)
    {
        $source = new NetteDbDataSource($this->event->getAllView());
        $grid = new Grid($this, $name);
        $table_id = 'id';
        $grid->setPrimaryKey($table_id);
        $grid->setDataSource($source);

//        $grid->enableFilter();

        $grid->addNumber('id');
        $grid->addText('username', 'Username');
        $grid->addText('event_name', 'Event name');
        $grid->addText('event_data', 'Event data');
        $grid->addDate('event_time', 'Create time')
            ->setFormat('j.n.Y H:i:s');
        $grid->addText('user_ip', 'User IP address');

        $grid->setDefaultOrder('event_time', 'DESC');

        return $grid;
    }

}