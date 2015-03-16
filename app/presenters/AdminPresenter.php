<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;
use Tracy\Debugger;


/**
 * Sign in/out presenters.
 */
class AdminPresenter extends BasePresenter
{
	/** @var Nette\Database\Context */
	private $database;
	private $songList;
	private $song;
	private $songMarkers;
	private $genreList;
	private $songBaseDir;
	private $songDefaultFormat;

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
		$this->songBaseDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/sounds/songs/';
		$this->songDefaultFormat = 'mp3';
	}

	protected function startup()
	{
		// TODO autorizovat korektne uzivatele podle urovne opravneni! + ukladat request pro aktualni stranku a vracet zpet pri prihlaseni
		parent::startup();
		if(!$this->user->loggedIn) $this->redirect('Homepage:');
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
		$form->addHidden('markers')
			->setValue(implode(',',$this->songMarkers->fetchPairs('id', 'timecode')));
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
		try {
			unset($values->songId);
			$updateMarkers = ($values->markers != 0) ? explode(',',$values->markers) : null;
			unset($values->markers);
			$values['update_time'] = new Nette\Utils\DateTime;

			$song = $this->database->table('song')->get($songId);
			$song->update($values);

			// if markers were set by the form, delete old and insert new
			if ($updateMarkers != null) {
				// delete old markers
				foreach ($song->related('marker') as $singleMarker) {
					$singleMarker->delete();
				}
				// insert new markers
				foreach ($updateMarkers as $singleMarker) {
					$this->database->table('marker')->insert( array(
						'song_id' => $songId,
						'timecode' => $singleMarker
					));
				}
			}

			$this->flashMessage("Song description been successfully updated.", 'success');
			$this->redirect('songs');
		} catch (\Exception $e) {
			Debugger::log($e->getMessage());
		}
	}

	public function songDeleteClicked()
	{
		$songId = $this->getParameter('id');
		$song = $this->database->table('song')->get($songId);
		try {
			Nette\Utils\FileSystem::delete($this->songBaseDir . $song->filename . '.' . $this->songDefaultFormat);
			$song->delete();
		} catch (\Exception $e) {
			Debugger::log($e->getMessage());
		}
		$this->flashMessage("Song has been deleted.", 'success');
		$this->redirect('songs');
	}

	public function actionSetRole($userId, $roleName)
	{
		$this->addRole('guest');
		$this->getUser()->logout();
		$this->flashMessage('The role has been successfully set.', 'success');
		$this->redirect('Homepage:');
	}

	public function actionSongs()
	{
		$this->songList = $this->database->table('song')
			->order('create_time DESC');
	}

	public function	renderSongs()
	{
		$this->template->songList = $this->songList;
	}

	public function actionEditSong($id = null)
	{
		if (isset($id)) {
			$this->song = $this->database->table('song')->get($id);
//			$this->genreList = $this->song->related('genre');
			if (!$this->song) {
				$this->flashMessage('Sorry, this song was not found.', 'error');
				$this->redirect('Admin:');
			}
			$this->songMarkers = $this->song->related('marker');
		}
		$this->genreList = $this->database->table('genre'); // fetch genre list for form
	}

	public function	renderEditSong()
	{
		if (isset($this->song)) {
			$this->template->song = $this->song;
			$this->template->songMarkers = implode(',',$this->songMarkers->fetchPairs('id', 'timecode'));
			$this['songEditForm']->setDefaults($this->template->song->toArray());
		}
	}

	/**
	 * uses Fine Uploader to handle uploaded music file
	 * TODO - vyclenit praci s ukladanim souboru do samostatneho modelu
     */
	public function handleUploadFile() {
		$uploadDirName = __DIR__ . '/../../uploads/';
		$uploader = new \UploadHandler();
		$uploader->allowedExtensions = array("mp3", "wav", "ogg");
		try {
			// TODO osefovat kontrolu duplicity souboru v databazi, jinak pokracovat
			$result = $uploader->handleUpload($uploadDirName);
			$uploadFilePath = $uploadDirName . $result['uuid'] . '/' . $uploader->getUploadName();

			$getID3 = new \getID3;
			$fileInfo = $getID3->analyze($uploadFilePath);
			\getid3_lib::CopyTagsToComments($fileInfo); // merges all detected tags and copies them into single 'comments' array (or 'comments_html')
			$duration = round($fileInfo['playtime_seconds']*1000);

			if (isset($fileInfo['comments'])) {
				$artist = implode(' & ', $fileInfo['comments']['artist']); // merges artist names if more of them are present
				$title = $fileInfo['comments']['title'][0];
			} else {
				$artist = "";
				$title = str_replace('.mp3','',str_replace('_', ' ', $uploader->getUploadName()));
			}

			$targetFilePath = $this->songBaseDir . $result['uuid'] . '.' . $fileInfo['fileformat'];

			Nette\Utils\FileSystem::copy($uploadFilePath, $targetFilePath);
			Nette\Utils\FileSystem::delete(__DIR__ . '/../../uploads/' . $result['uuid']);

			$songRecord['artist'] = $artist;
			$songRecord['title'] = $title;
			$songRecord['duration'] = $duration;
			$songRecord['filename'] = $result['uuid'];

			$song = $this->database->table('song')->insert($songRecord);

			$result['ext'] = $uploader->getFileExtension();
			$result['artist'] = $artist;
			$result['title'] = $title;
			$result['duration'] = $this->getSongTimeFormat($duration);
			$result['songId'] = $song->id;
			$result['fileName'] = $song->filename;

		} catch (\Exception $e) {
			$this->sendResponse(new Nette\Application\Responses\JsonResponse(array(
				'error' => $e->getMessage(),
			)));
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($result));
	}

}
