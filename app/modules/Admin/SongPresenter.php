<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form,
	Grido,
	Grido\Grid,
	Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class SongPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	private $songRecord;
	private $songMarkers;
	private $gameAssoc;
	private $genreList;
	private $gameList;

	/** @inject @var Model\Song */
	public $song;

	/** @inject @var Model\Game */
	public $game;

	/** @inject @var Model\Genre */
	public $genre;

	/**
	 * @return Form
	 */
	protected function createComponentSongEditForm()
	{
		$form = new Form;
		$form->addText('artist');
		$form->addText('title')
			->setRequired();
		$form->addSelect('genre_id')
			->setItems($this->genreList->fetchPairs('id', 'name'));
		$form->addCheckboxList('game', 'Games:', $this->gameList->fetchPairs('id', 'name'));
		$form->addHidden('songId');
		$form->addHidden('markersUpdated')
			->setValue('false');
		$markers = $form->addHidden('markers');
		if ($this->songMarkers) {
			$markers->setValue(implode(',', $this->songMarkers->fetchPairs('id', 'timecode')));
		}
		$form->addSubmit('update'); // default
		$form->addSubmit('delete')
			->onClick[] = \callback($this, 'songDeleteClicked');

		$form->onSuccess[] = array($this, 'songEditFormSucceed');

		return $form;
	}

	/**
	 * @param $form
	 * @param $values
	 */
	public function songEditFormSucceed($form, $values)
	{
		// sets song id by form hidden or by url parameter
		$songId = (strlen($values->songId) > 0) ? $values->songId : $songId = $this->getParameter('id');
		unset($values->songId);

		if ($values->markersUpdated == '1') {
			if ($values->markers !== "")
				$updateMarkers = explode(',',$values->markers); // markers were updated
			else
				$updateMarkers = null; // all markers were deleted
		} else {
			$updateMarkers = false; // there was no marker change
		}
		$gameIdArray = (!empty($values->game)) ? $values->game : null;

		unset($values->markers, $values->markersUpdated, $values->game);

		$this->song->updateById($songId, $values);
		if ($updateMarkers)// if markers were set by the form, delete old and insert new
			$this->song->updateMarkers($songId, $updateMarkers);

		$this->game->updateSongAssoc($songId, $gameIdArray);

		$this->flashMessage("Song description been successfully updated.", 'success');
		$this->redirect('default');
	}

	public function songDeleteClicked()
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

		if (!$this->song->deleteById($id)) {
			$this->flashMessage("Song not found.", 'error');
		} else {
			$this->flashMessage("Song has been deleted.", 'success');
		}
		$this->redirect('default');
	}

	public function	renderDefault()
	{

	}

	public function actionAdd()
	{
		$this->gameList = $this->game->getAll()->where('uses_song', TRUE)->order('name ASC');
		$this->genreList = $this->genre->getAll(); // fetch genre list for form
	}

	public function actionEdit($id)
	{
		if ($this->songRecord = $this->song->getById($id)) {
			$this->gameAssoc = $this->game->getBySong($id)->fetchPairs(NULL, 'game_id');
//			$this->genreList = $this->song->related('genre');
			$this->songMarkers = $this->song->getMarkersAll($id);

			$this->gameList = $this->game->getAll()->where('uses_song', TRUE)->order('name ASC');
			$this->genreList = $this->genre->getAll(); // fetch genre list for form
		} else {
			$this->flashMessage('Sorry, this song was not found.', 'error');
			$this->redirect('default');
		}
	}

	public function renderAdd()
	{

	}

	public function	renderEdit()
	{
		$this->template->song = $this->songRecord;
		$this->template->songMarkers = implode(',',$this->songMarkers->fetchPairs('id', 'timecode'));
		$this['songEditForm']->setDefaults($this->template->song->toArray());
		$this['songEditForm']['game']->setDefaultValue($this->gameAssoc);
	}

	/**
	 * uses Fine Uploader to handle uploaded music file
	 */
	public function handleUploadFile() {
		try {
			$uploadResult = $this->song->handleUpload();
			$saveResult = $this->song->save($uploadResult);
			$saveResult['durationReadable'] = $this->getSongTimeFormat($saveResult['duration']); // format duration
		} catch (\Exception $e) {
			$this->sendResponse(new Nette\Application\Responses\JsonResponse(array(
				'error' => $e->getMessage(),
			)));
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($saveResult));
	}


	protected function createComponentGrid($name)
	{
		$grid = new Grid($this, $name);
		$grid->setModel($this->song->getAll());

//		$grid->setFilterRenderType(Grido\Components\Filters\Filter::RENDER_OUTER);

		$grid->addColumnNumber('id', 'id')
			->setSortable();
//			->setFilterText();

		$grid->addColumnText('artist', 'Artist')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('title', 'Title')
			->setSortable()
			->setFilterText();

		$grid->addColumnText('duration', 'Duration')
			->setSortable()
			->setCustomRender(function($item) {
				return $this->getSongTimeFormat($item->duration);
			})
			->setFilterText();

		$grid->addColumnText('genre_id', 'Genre')
			->setSortable()
			->setCustomRender(function($item) {
				return $this->genre->getById($item->genre_id)->name;
			});

		$genres[''] = '';
		foreach ($this->genre->getAll() as $genre)
			$genres[$genre->id] = $genre->name;

		$grid->addFilterSelect('genre_id', 'Genre', $genres);

		$grid->addColumnText('games', 'Games')
			->setSortable()
			->setCustomRender(function($item) {
				$games = $this->game->getBySong($item->id)->fetchPairs(NULL, 'game_id');
				$render = '';
				foreach ($games as $g) {
					$gameName = $this->game->getById($g)->name;
					$render .= '<span class=\'grid-cell-subitem\'>'.$gameName.'</span>';

					if ($gameName == 'melodicCubes')
						$render = '<a href=\''.$this->link(':Front:Game:MelodicCubes:', $item->id).'\'>'.$gameName.'</a>';
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
