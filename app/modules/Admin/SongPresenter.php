<?php

namespace App\Module\Admin\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;


/**
 * Sign in/out presenters.
 */
class SongPresenter extends \App\Module\Base\Presenters\BasePresenter
{
	private $songList;
	private $song;
	private $songMarkers;
	private $genreList;

	/** @var Model\SongStorage */
	private $songStorage;

	public function __construct(Model\SongStorage $songStorage)
	{
		$this->songStorage = $songStorage;
	}

	protected function startup()
	{
		parent::startup();

		// TODO autorizovat korektne uzivatele podle urovne opravneni! + ukladat request pro aktualni stranku a vracet zpet pri prihlaseni
		/* user authorization */
		if ($this->user->isLoggedIn()) {
//			if (!$this->user->isAllowed($this->name, $this->action)) { // check if user is allowed
//				$this->flashMessage("You are not allowed for this module.", "error");
//				$this->redirect("Homepage");
//			}
		} else {
//		} else if ($this->action != "login") {
//			if ($this->action != "default") {
//				if ($this->user->getLogoutReason() === User::INACTIVITY) {
//					$this->flashMessage("You have been logged out due to inactivity.");
//				} else {
//					$this->flashMessage("You are not logged.", "error");
//				}
//			}
			$this->redirect(":Front:Default:");
		}
	}

	protected function createComponentSongEditForm()
	{
		$form = new Form;
		$form->addText('artist');
		$form->addText('title')
			->setRequired();
		$form->addSelect('genre_id')
			->setItems($this->genreList->fetchPairs('id', 'name'));
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

		$form->onSuccess[] = array($this, 'songEditFormSucceed'); // a přidat událost po odeslání

		return $form;
	}

	public function songEditFormSucceed($form, $values)
	{
		// sets song id by form hidden or by url parameter
		$songId = (strlen($values->songId) > 0) ? $values->songId : $songId = $this->getParameter('id');
		unset($values->songId);

		$updateMarkers = ($values->markers != 0 && $values->markersUpdated == 'true') ? explode(',',$values->markers) : null;
		unset($values->markers);
		unset($values->markersUpdated);

		$this->songStorage->updateSong($songId, $values);
		if ($updateMarkers != null) $this->songStorage->updateMarkers($songId, $updateMarkers); // if markers were set by the form, delete old and insert new

		$this->flashMessage("Song description been successfully updated.", 'success');
		$this->redirect('default');
	}

	public function songDeleteClicked()
	{
		$this->actionDelete($this->getParameter('id'));
	}

	public function actionDelete($id)
	{
		$this->songStorage->deleteSong($id);

		$this->flashMessage("Song has been deleted.", 'success');
		$this->redirect('default');
	}

	public function actionDefault()
	{
		$this->songList = $this->songStorage->getSongAll();
	}

	public function	renderDefault()
	{
		$this->template->songList = $this->songList;
	}

	public function actionEdit($id = null)
	{
		if (isset($id)) {
			$this->song = $this->songStorage->getSongById($id);
//			$this->genreList = $this->song->related('genre');
			if (!$this->song) {
				$this->flashMessage('Sorry, this song was not found.', 'error');
				$this->redirect('default');
			}
			$this->songMarkers = $this->songStorage->getMarkersAll($id);
		}
		$this->genreList = $this->songStorage->getGenres($id); // fetch genre list for form
	}

	public function	renderEdit()
	{
		if (isset($this->song)) {
			$this->template->song = $this->song;
			$this->template->songMarkers = implode(',',$this->songMarkers->fetchPairs('id', 'timecode'));
			$this['songEditForm']->setDefaults($this->template->song->toArray());
		}
	}

	/**
	 * uses Fine Uploader to handle uploaded music file
     */
	public function handleUploadFile() {
		try {
			$uploadResult = $this->songStorage->handleUpload();
			$saveResult = $this->songStorage->save($uploadResult);
			$saveResult['duration'] = $this->getSongTimeFormat($saveResult['duration']); // format duration
		} catch (\Exception $e) {
			$this->sendResponse(new Nette\Application\Responses\JsonResponse(array(
				'error' => $e->getMessage(),
			)));
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($saveResult));
	}

}
