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

	public function __construct(Nette\Database\Context $database)
	{
		$this->database = $database;
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
		$form->addText('artist', 'Artist:');
		$form->addText('title', 'Title:')
			->setRequired();
		$form->addSubmit('update', 'Update');
		$form->addHidden('songId');

		$form->onSuccess[] = array($this, 'songEditFormSucceed'); // a přidat událost po odeslání

		return $form;
	}

	public function songEditFormSucceed($form, $values)
	{
		// sets song id by form hidden or by url parameter
		$songId = (strlen($values->songId) > 0) ? $values->songId : $songId = $this->getParameter('id');

		if ($songId) {
			unset($values->songId);
			$values['update_time'] = new Nette\Utils\DateTime;
			$song = $this->database->table('song')->get($songId);
			$song->update($values);
			$this->flashMessage("Song description been successfully updated.", 'success');
		}
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
			if (!$this->song) {
				$this->flashMessage('Sorry, this song was not found.', 'error');
				$this->redirect('Admin:');
			}
		}
	}

	public function	renderEditSong()
	{
		if (isset($this->song)) {
			$this->template->song = $this->song;
			$this['songEditForm']->setDefaults($this->template->song->toArray());
		}
	}

	/**
	 * uses Fine Uploader to handle uploaded music file
	 * TODO - vyclenit praci s ukladanim souboru do samostatneho modelu
     */
	public function handleUploadFile() {
		$uploadDirName = __DIR__ . '/../../uploads/';
		$targetDirName = $_SERVER['DOCUMENT_ROOT'] . '/assets/sounds/songs/';
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
			$artist = implode(' & ', $fileInfo['comments']['artist']); // merges artist names if more of them are present
			$title = $fileInfo['comments']['title'][0];

			$targetFilePath = $targetDirName . $result['uuid'] . '.' . $fileInfo['fileformat'];

			Nette\Utils\FileSystem::copy($uploadFilePath, $targetFilePath);
			Nette\Utils\FileSystem::delete(__DIR__ . '/../../uploads/' . $result['uuid']);

			$songRecord['artist'] = $artist;
			$songRecord['title'] = $title;
			$songRecord['duration'] = $duration;
			$songRecord['filename'] = $result['uuid'];

			$song = $this->database->table('song')->insert($songRecord);
//			$song['id'] = 2;

			$result['ext'] = $uploader->getFileExtension();
			$result['artist'] = $artist;
			$result['title'] = $title;
			$result['duration'] = $duration/1000 . ' s';
			$result['filename'] = $uploader->getUploadName();
			$result['songId'] = 2;

		} catch (\Exception $exc) {
			$this->sendResponse(new Nette\Application\Responses\JsonResponse(array(
				'error' => $exc->getMessage(),
			)));
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($result));
	}

}
