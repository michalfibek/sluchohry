<?php
namespace App\Model;

use Nette,
    App\Services;

/**
 * Manipulate with song files.
 */
class Song extends Base
{
    const
        TABLE_NAME_SONG = 'song',
        TABLE_NAME_MARKER = 'marker',
        TABLE_NAME_GAME = 'game';

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
     * @param Nette\Database\Context $db
     * @param $uploadDir
     * @param $saveDir
     * @param Services\UploadHandler $uploadHandler
     * @param Services\SongTagHandler $tagHandler
     */
    public function __construct($uploadDir, $saveDir, Nette\Database\Context $db, Services\UploadHandler $uploadHandler, Services\SongTagHandler $tagHandler)
    {
        parent::__construct($db);
        $this->uploadDir = $uploadDir;
        $this->saveDir = $saveDir;
        $this->uploadHandler = $uploadHandler;
        $this->tagHandler = $tagHandler;
        $this->songDefaultExtension = 'mp3';
        $this->allowedExtensions = array("mp3", "wav", "ogg");
    }

    /** @return Nette\Database\Table\Selection */
    public function getGameAll()
    {
        return $this->db->table(self::TABLE_NAME_GAME)
            ->order('name ASC');
    }

    public function getById($songId, $requireMarkers = false)
    {
        if ($requireMarkers) {
            $hasMarkers = $this->db->table('marker')->where('song_id', $songId)->fetchAll();
            if (!$hasMarkers) return false;
        }
        return parent::getById($songId);
    }

    /**
     * @param array $omitSongs Array of songs to skip in select
     * @param bool $requireMarkers
     * @param int $gameLimit Game id
     * @param int $songCount Count of songs
     * @return array song info
     */
    public function getRandom($omitSongs = null, $requireMarkers = false, $gameLimit = null, $songCount = 1)
    {
        $songsAll = $this->getAll();
        $markedSongs = $this->db->table('marker')->select('DISTINCT song_id')->fetchPairs(null, 'song_id');

        // fetch only songs with set markers
        if ($requireMarkers) {
            $songsAll = $songsAll->where('id IN', $markedSongs);
        }

        // skip songs with $omitSongs id's
        if ($omitSongs) {
            $songsAll = $songsAll->where('id NOT IN', $omitSongs);
        }

        // fetch only songs for certain game
        if ($gameLimit) {
            $songsForGame = $this->db->table('game_has_song')->where('game_id', $gameLimit)->fetchPairs(null, 'song_id');
            $songsAll = $songsAll->where('id IN', $songsForGame);
        }

        if ($songCount == 1) { // fetching only one song

            $fetch = $songsAll->fetchAll();
            if ($fetch) return $songsAll[array_rand($fetch)]; else return null;

        } elseif ($songCount > 1 && count($songsAll) >= $songCount) { // fetching more than one song

            $keys = array_rand($songsAll->fetchAll(), $songCount);

            foreach ($keys as $key) {
                $returnSongs[] = $songsAll[$key];
            }

            return $returnSongs;


        } else { // no song found
            return null;
        }

    }

    public function getMarkersAll($songId)
    {
        return $this->db->table(self::TABLE_NAME_SONG)->get($songId)->related(self::TABLE_NAME_MARKER)->order('timecode ASC');
    }

    /**
     * @param $songId
     * @param $cubeCount Count of splits
     * @return array Associative array of markers -> eg. array(0 => 3491, 1 => 5979, 2 => 14291)
     */
    public function getCubeMarkersByCount($songId, $cubeCount)
    {
        $cubeSplits = ($cubeCount > 1) ? $cubeCount-1 : $cubeCount;

        $markersAll = $this->db->table(self::TABLE_NAME_SONG)->get($songId)->related(self::TABLE_NAME_MARKER)->order('timecode ASC')->fetchAll();

        if (count($markersAll) < $cubeSplits) $cubeSplits = count($markersAll);

        $randKeys = array_rand($markersAll, $cubeSplits);

        $markers[] = array(0, $markersAll[$randKeys[0]]->timecode);

        if ($cubeSplits > 1)
        {
            for ($i = 0; $i < $cubeSplits-1; $i++)
            {
                $inPoint = $markersAll[$randKeys[$i]]->timecode;
                $outPoint = $markersAll[$randKeys[$i+1]]->timecode - $markersAll[$randKeys[$i]]->timecode;
                $markers[] = array($inPoint, $outPoint);
            }
        }

        $markers[] = array($markersAll[$randKeys[$cubeSplits-1]]->timecode, $this->getById($songId)->duration - $markersAll[$randKeys[$cubeSplits-1]]->timecode);

        return $markers;
    }

    public function handleUpload()
    {
        $this->uploadHandler->allowedExtensions = $this->allowedExtensions;
        return $this->uploadHandler->handleUpload($this->uploadDir);
    }

    public function updateById($id, $data)
    {
        $data['update_time'] = new Nette\Utils\DateTime;
        return parent::updateById($id, $data);
    }

    public function updateMarkers($songId, $markers = null)
    {
        // delete old markers
        $this->deleteMarkers($songId);

        if ($markers) {
            // insert new markers
            foreach ($markers as $singleMarker) {
                $this->db->table(self::TABLE_NAME_MARKER)->insert( array(
                    'song_id' => $songId,
                    'timecode' => $singleMarker
                ));
            }
        }
    }


    public function save($uploadResult, $userId)
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
        $row['update_user_id'] = $userId;

        try {
            $songInsert = $this->insert($row);
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

    public function deleteById($id)
    {
        $song = $this->getById($id);
        if (!$song) return false;
        try {
            // delete song file
            Nette\Utils\FileSystem::delete($this->saveDir . $song->filename . '.' . $this->songDefaultExtension);

            // finally, delete song itself from db
            $song->delete();

            return true;

        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function deleteMarkers($songId)
    {
        $markers = $this->db->table(self::TABLE_NAME_SONG)->get($songId)->related(self::TABLE_NAME_MARKER);
        foreach ($markers as $singleMarker) {
            $singleMarker->delete();
        }
    }

}