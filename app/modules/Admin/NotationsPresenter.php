<?php

namespace App\Module\Admin\Presenters;

use App\Module\Base\Presenters\BasePresenter;
use Nette,
    App\Model,
    Nette\Application\UI\Form,
    Grido,
    Grido\Grid,
    Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class NotationsPresenter extends BasePresenter
{
    const
        DEFAULT_TEMPO_ID = 1,
        DEFAULT_OCTAVE_ID = 5,
        DEFAULT_GAME_ID = 4;

    private $notationRecord;
    private $gameAssoc;

    /** @inject @var Model\Notation */
    public $notation;

    /** @inject @var Model\Game */
    public $game;

    /** @inject @var Model\Genre */
    public $genre;

    /** @inject @var Model\Octave */
    public $octave;

    /** @inject @var Model\Tempo */
    public $tempo;


    /**
     * @return Form
     */
    protected function createComponentNotationEditForm()
    {
        $form = new Form;
        $form->addText('artist');
        $form->addText('title')
            ->setRequired();
        $form->addSelect('genre_id')
            ->setItems($this->genre->getAll()->fetchPairs('id', 'name'));
        $form->addSelect('octave_id')
            ->setItems($this->getOctavesTranslated());
        $form->addSelect('tempo_id')
            ->setItems($this->tempo->getAll()->order('order')->fetchPairs('id', 'name'));
        $form->addCheckboxList('game', 'Games:', $this->game->getAll()->where('uses_notation', TRUE)->order('name ASC')->fetchPairs('id', 'name'));
        $form->addTextArea('sheet');
        $form->addHidden('notation_id');

        $form->addSubmit('update'); // default
        $form->addSubmit('delete')
            ->onClick[] = \callback($this, 'notationDeleteClicked');

        $form->onSuccess[] = array($this, 'notationEditFormSucceed');

        return $form;
    }

    /**
     * @param $form
     * @param $values
     */
    public function notationEditFormSucceed($form, $values)
    {
        $data = array(
            'artist' => $values->artist,
            'title' => $values->title,
            'genre_id' => $values->genre_id,
            'sheet' => $values->sheet,
            'octave_id' => $values->octave_id,
            'tempo_id' => $values->tempo_id,
            'update_user_id' => $this->user->getId()
        );

        $gameIdArray = (!empty($values->game)) ? $values->game : null;

        if (strlen($values->notation_id) > 0) {
            if ($update = $this->notation->updateById($values->notation_id, $data)) {
                $this->game->updateNotationAssoc($values->notation_id, $gameIdArray);
                $this->flashMessage("Notation been successfully updated.", 'success');
            } else {
                $this->flashMessage("Error while updating notation.", 'success');
            }

            $this->redirect('default');

        } else {
            if ($insert = $this->notation->insert($data)) {
                $this->game->updateNotationAssoc($insert->id, $gameIdArray);
                $this->flashMessage("Notation has been successfully added.", 'success');
            } else {
                $this->flashMessage("Error while adding notation.", 'success');
            }

            $this->redirect('default');
        }

    }

    private function getOctavesTranslated()
    {
        $octaves = $this->octave->getAll()->order('order');

        foreach ($octaves as $oct) {
            $output[$oct->id] = $oct->symbol . ' (' . $this->translator->translate('admin.notations.octave.'.$oct->name) . ')';
        }

        return $output;
    }

    public function notationDeleteClicked()
    {
        $this->handleDelete($this->getParameter('id'));
    }

    public function actionDefault()
    {

    }

    /**
     * @param $id
     * @throws \Exception
     */
    public function handleDelete($id)
    {
        if (!$this->user->isAllowed($this->name, 'delete')) {
            $this->flashMessage($this->translator->translate('front.auth.flash.actionForbidden'), 'error');
        }

        if (!$this->notation->deleteById($id)) {
            $this->flashMessage("Notation not found.", 'error');
        } else {
            $this->flashMessage("Notation has been deleted.", 'success');
        }
        $this->redirect('default');
    }

    public function	renderDefault()
    {
//        $this->template->notationList = $this->notationList;
    }

    public function actionAdd()
    {
    }

    public function actionEdit($id)
    {
        if ($this->notationRecord = $this->notation->getById($id)) {
            $this->gameAssoc = $this->game->getByNotation($id)->fetchPairs(NULL, 'game_id');

        } else {
            $this->flashMessage('Sorry, this notation was not found.', 'error');
            $this->redirect('default');
        }
    }

    public function renderAdd()
    {
        $this->template->tempoList = $this->tempo->getAll()->fetchPairs('id', 'value');
        $this->template->octaveList = $this->octave->getAll()->fetchPairs('id', 'shift');
        $this['notationEditForm']['octave_id']->setDefaultValue(self::DEFAULT_OCTAVE_ID);
        $this['notationEditForm']['tempo_id']->setDefaultValue(self::DEFAULT_TEMPO_ID);
        $this['notationEditForm']['game']->setDefaultValue(self::DEFAULT_GAME_ID);
    }

    public function	renderEdit()
    {
        $this->template->tempoList = $this->tempo->getAll()->fetchPairs('id', 'value');
        $this->template->octaveList = $this->octave->getAll()->fetchPairs('id', 'shift');
        $this->template->notation = $this->notationRecord;
        $this['notationEditForm']->setDefaults($this->notationRecord->toArray());
        $this['notationEditForm']['notation_id']->setValue($this->getParameter('id'));
        $this['notationEditForm']['game']->setDefaultValue($this->gameAssoc);
    }

    protected function createComponentGrid($name)
    {
        $grid = new Grid($this, $name);
        $grid->setModel($this->notation->getAll());

        $grid->addColumnNumber('id', 'id')
            ->setSortable();
//			->setFilterText();

        $grid->addColumnText('artist', 'Artist')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('title', 'Title')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('genre_id', 'Genre')
            ->setSortable()
            ->setCustomRender(function($item) {
                return $this->genre->getById($item->genre_id)->name;
            });

        $grid->addColumnText('length', 'Length')
            ->setSortable()
            ->setFilterText();

        $genres[''] = '';
        foreach ($this->genre->getAll() as $genre)
            $genres[$genre->id] = $genre->name;

        $grid->addFilterSelect('genre_id', 'Genre', $genres);

        $grid->addColumnText('games', 'Games')
            ->setSortable()
            ->setCustomRender(function($item) {
                $games = $this->game->getByNotation($item->id)->fetchPairs(NULL, 'game_id');
                $render = '';
                foreach ($games as $g) {
                    $gameName = $this->game->getById($g)->name;
                    $render .= '<span class=\'grid-cell-subitem\'>'.$gameName.'</span>';

                    if ($gameName == 'faders')
                        $render = '<a href=\''.$this->link(':Front:Game:Faders:', $item->id).'\'>'.$gameName.'</a>';
                }
                return $render;
            })
            ->setFilterText();

        $grid->addColumnDate('create_time', 'Created')
            ->setDateFormat('d.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();

        $grid->addColumnDate('update_time', 'Updated')
            ->setDateFormat('d.m.Y H:i:s')
            ->setSortable()
            ->setFilterDateRange();

        $grid->addActionHref('edit', 'Edit')
            ->setIcon('fa fa-pencil')
            ->setDisable(function ($item) {
                return (!$this->user->isAllowed($this->name, 'edit'));
            });

        $grid->addActionHref('delete', 'Delete', 'delete!')
            ->setIcon('fa fa-remove')
            ->setConfirm('Do you really want to delete this group?')
            ->setDisable(function ($item) {
                return (!$this->user->isAllowed($this->name, 'delete'));
            });

        $grid->setDefaultSort(array(
            'artist' => 'ASC',
            'title' => 'ASC'
        ));

        return $grid;
    }


}
