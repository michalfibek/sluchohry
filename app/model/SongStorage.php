<?php
namespace App\Model;

use Nette,
    App\Services;

/**
 * Manipulate with song files.
 */
class SongStorage extends Nette\Object
{
    /** @var Nette\Database\Context */
    private $database;
    /** @var Services\UploadHandler */
    private $uploadHandler;
    /** @var Services\SongTagHandler */
    private $tagHandler;
    /** @var string contains dir reference for newly uploaded files */
    private $uploadDir;
    /** @var string contains dir for music files to save to */
    private $saveDir;
    /** @var string default file format extension */
    private $songDefaultExtension;
    /** @var array all allowed extensions in array*/
    private $allowedExtensions;

    /**
     * @param $uploadDir
     * @param $saveDir
     * @param Nette\Database\Context $database
     * @param Services\UploadHandler $uploadHandler
     * @param Services\SongTagHandler $tagHandler
     */
    public function __construct($uploadDir, $saveDir, Nette\Database\Context $database, Services\UploadHandler $uploadHandler, Services\SongTagHandler $tagHandler)
    {
        $this->uploadDir = $uploadDir;
        $this->saveDir = $saveDir;
        $this->database = $database;
        $this->uploadHandler = $uploadHandler;
        $this->tagHandler = $tagHandler;
        $this->songDefaultExtension = 'mp3';
        $this->allowedExtensions = array("mp3", "wav", "ogg");
    }

    /** @return Nette\Database\Table\Selection */
    public function getSongAll()
    {
        return $this->database->table('song')
            ->order('create_time DESC');
    }

    public function getSongById($songId)
    {
        return $this->database->table('song')->get($songId);
    }

    public function getMarkers($songId)
    {
        return $this->database->table('song')->get($songId)->related('marker')->order('timecode ASC');
    }

    public function getGenres($songId)
    {
        return $this->database->table('genre');
    }

    public function handleUpload()
    {
        $this->uploadHandler->allowedExtensions = $this->allowedExtensions;
        return $this->uploadHandler->handleUpload($this->uploadDir);
    }

    public function updateSong($songId, $values)
    {
        try {
            $values['update_time'] = new Nette\Utils\DateTime;
            $song = $this->database->table('song')->get($songId);
            $song->update($values);
        } catch (\Exception $e) {
            Debugger::log($e->getMessage());
        }

    }

    public function updateMarkers($songId, $markers)
    {
        // delete old markers
        $this->deleteMarkers($songId);

        // insert new markers
        foreach ($markers as $singleMarker) {
            $this->database->table('marker')->insert( array(
                'song_id' => $songId,
                'timecode' => $singleMarker
            ));
        }
    }

    public function save($uploadResult)
    {
        $uploadUUIDName = $uploadResult['uuid'];
        $uploadFilePath = $this->uploadDir . $uploadUUIDName . '/' . $this->uploadHandler->getUploadName();

        $songTags = $this->tagHandler->analyze($uploadFilePath)->fetchTags();
        list($artist, $title) = $songTags->getArtistTitle();
        if (!$artist) $artist = "";
        if (!$title) $title = Services\SongTagHandler::formatFilenameToTitle($this->uploadHandler->getUploadName(), $this->songDefaultExtension);
        $duration = $songTags->getDuration();
        $fileFormat = $songTags->getFileFormat();

        $targetFilePath = $this->saveDir . $uploadUUIDName . '.' . $fileFormat;

        // try to save file to new location
        try {
            Nette\Utils\FileSystem::copy($uploadFilePath, $targetFilePath);
            Nette\Utils\FileSystem::delete($this->uploadDir . $uploadUUIDName);
        } catch (\Exception $e) {
            throw $e;
        }

        $row['artist'] = $artist;
        $row['title'] = $title;
        $row['duration'] = $duration;
        $row['filename'] = $uploadUUIDName;

        try {
            $songInsert = $this->insertSong($row);
        } catch (\Exception $e) {
            throw $e;
        }

        $uploadResult['ext'] = $fileFormat;
        $uploadResult['artist'] = $artist;
        $uploadResult['title'] = $title;
        $uploadResult['duration'] = $duration;
        $uploadResult['songId'] = $songInsert->id;
        $uploadResult['fileName'] = $songInsert->filename;

        return $uploadResult;
    }

    public function deleteSong($songId)
    {
        $song = $this->database->table('song')->get($songId);
        try {
            // delete song file
            Nette\Utils\FileSystem::delete($this->saveDir . $song->filename . '.' . $this->songDefaultExtension);

            // delete song markers
            $this->deleteMarkers($songId);

            // finally, delete song itself from db
            $song->delete();

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteMarkers($songId)
    {
        $markers = $this->database->table('song')->get($songId)->related('marker');
        foreach ($markers as $singleMarker) {
            $singleMarker->delete();
        }
    }

    private function insertSong($row)
    {
        return $this->database->table('song')->insert($row);
    }


}