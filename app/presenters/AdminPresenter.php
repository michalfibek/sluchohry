<?php

namespace App\Presenters;

use Nette,
	App\Model,
	Nette\Application\UI\Form;


/**
 * Sign in/out presenters.
 */
class AdminPresenter extends BasePresenter
{
	protected function createComponentSongInfoForm()
	{
		$form = new Form;
		$form->addText('artist', 'Artist:')
			->setRequired();
		$form->addText('title', 'Title:');
		$form->addSubmit('send', 'Save');

		$form->onSuccess[] = array($this, 'songInfoFormSubmitted'); // a přidat událost po odeslání

		return $form;
	}

	public function songInfoFormSubmitted($form, $values)
	{
		$this->flashMessage("Příspěvek byl úspěšně publikován.", 'success');
		$this->redirect('Homepage:');
	}

	public function actionSetRole($userId, $roleName)
	{
		$this->addRole('guest');
		$this->getUser()->logout();
		$this->flashMessage('The role has been successfully set.');
		$this->redirect('Homepage:');
	}

	public function	renderMelodicCubes() {

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

			$result['ext'] = $uploader->getFileExtension();
			$result['artist'] = $artist;
			$result['title'] = $title;
			$result['duration'] = $duration/1000 . ' s';
			$result['filename'] = $uploader->getUploadName();

			$targetFilePath = $targetDirName . $result['uuid'] . '.' . $fileInfo['fileformat'];

			Nette\Utils\FileSystem::copy($uploadFilePath, $targetFilePath);
			Nette\Utils\FileSystem::delete(__DIR__ . '/../../uploads/' . $result['uuid']);
		} catch (\Exception $exc) {
			$this->sendResponse(new Nette\Application\Responses\JsonResponse(array(
				'error' => $exc->getMessage(),
			)));
		}
		$this->sendResponse(new Nette\Application\Responses\JsonResponse($result));
	}

}
