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
class SongPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	private $songList;
	private $song;
	private $songMarkers;
	private $gameAssoc;
	private $genreList;
	private $gameList;

	/** @var Model\SongStorage */
	private $songStorage;

	public function __construct(Model\SongStorage $songStorage)
	{
		parent::__construct();
		$this->songStorage = $songStorage;
	}

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
	 * @param $name
	 * @return Grid
	 */
	protected function createComponentSongDataGrid($name) {
		$source = new NetteDbDataSource($this->songStorage->getSongAll());
		$grid = new Grid($this, $name);
		$table_id = 'id';
		$grid->setPrimaryKey($table_id); // primary key is now used always
		$grid->setDataSource($source);

		$grid->addNumber('id');
		$grid->addText('artist', 'Artist');
		$grid->addText('title', 'Title');
		$grid->addText('duration', 'duration')
			->setCallback(function($data) {
				return $this->getSongTimeFormat($data['duration']);
		});
		$grid->addDate('create_time', 'Created')
			->setFormat('j.n.Y H:i:s');
		$grid->addDate('update_time', 'Updated')
			->setFormat('j.n.Y H:i:s');

		$actions = $grid->addActions('Actions');
		$actions->addButton()
			->setType('btn-primary')
			->setIcon('fa fa-pencil')
			->setTitle('edit')
			->setAttribute('href', new Link('edit', array(
				'id' => '{'.$table_id.'}'
			)));
		$actions->addButton()
			->setType('btn-danger')
			->setIcon('fa fa-remove')
			->setConfirm('Do you really want to delete this song?')
			->setTitle('delete')
			->setAttribute('href', new Link('delete!', array(
				'id' => '{'.$table_id.'}'
			)));
		return $grid;
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

		$this->songStorage->updateSong($songId, $values);
		if ($updateMarkers)// if markers were set by the form, delete old and insert new
			$this->songStorage->updateMarkers($songId, $updateMarkers);

		$this->songStorage->updateGameAssoc($songId, $gameIdArray);

		$this->flashMessage("Song description been successfully updated.", 'success');
		$this->redirect('default');
	}

	public function songDeleteClicked()
	{
		$this->handleDelete($this->getParameter('id'));
	}

	public function actionDefault()
	{
//		$this->songList = $this->songStorage->getSongAll();
	}

	/**
	 * @param $id
	 * @throws \Exception
	 */
	public function handleDelete($id)
	{

		if (!$this->songStorage->deleteSong($id)) {
			$this->flashMessage("Song not found.", 'error');
		} else {
			$this->flashMessage("Song has been deleted.", 'success');
		}
		$this->redirect('default');
	}

	public function	renderDefault()
	{
		$this->template->songList = $this->songList;
	}

	public function actionEdit($id = null)
	{
		if (isset($id)) {
			$this->song = $this->songStorage->getSongById($id);
			$this->gameAssoc = $this->songStorage->getGameAssoc($id);
//			$this->genreList = $this->song->related('genre');
			if (!$this->song) {
				$this->flashMessage('Sorry, this song was not found.', 'error');
				$this->redirect('default');
			}
			$this->songMarkers = $this->songStorage->getMarkersAll($id);
		}
		$this->gameList = $this->songStorage->getGameAll();
		$this->genreList = $this->songStorage->getGenres(); // fetch genre list for form
	}

	public function	renderEdit()
	{
		if (isset($this->song)) {
			$this->template->song = $this->song;
			$this->template->songMarkers = implode(',',$this->songMarkers->fetchPairs('id', 'timecode'));
			$this['songEditForm']->setDefaults($this->template->song->toArray());
			$this['songEditForm']['game']->setDefaultValue($this->gameAssoc);
		}
	}

	/**
	 * uses Fine Uploader to handle uploaded music file
	 */
	public function handleUploadFile() {
		try {
			$uploadResult = $this->songStorage->handleUpload();
			$saveResult = $this->songStorage->save($uploadResult);
			$saveResult['durationReadable'] = $this->getSongTimeFormat($saveResult['duration']); // format duration
		} catch (\Exception $e) {
			$this->sendResponse(new Nette\Application\Responses\JsonResponse(array(
				'error' => $e->getMessage(),
			)));
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($saveResult));
	}

}
