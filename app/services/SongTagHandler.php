<?php

namespace App\Services;


class SongTagHandler {
    /** @var \getID3 */
    private $id3;
    /** @var array file info from analysis */
    private $fileInfo;

    public function __construct(\getID3 $id3)
    {
        $this->id3 = $id3;
    }

    public function analyze($filePath)
    {
        $this->fileInfo = $this->id3->analyze($filePath);
        return $this;
    }

    /**
     * @return $this
     */
    public function fetchTags()
    {
        \getid3_lib::CopyTagsToComments($this->fileInfo);
        return $this;
    }

    public function getArtistTitle()
    {
        if (isset($this->fileInfo['comments'])) {
            $artist = implode(' & ', $this->fileInfo['comments']['artist']); // merges artist names if more of them are present
            $title = $this->fileInfo['comments']['title'][0];
            return array($artist, $title);
        } else {
            return null;
        }
    }

    /**
     * @return float duration in milliseconds
     */
    public function getDuration()
    {
        return round($this->fileInfo['playtime_seconds']*1000);
    }

    public function getFileFormat()
    {
        return $this->fileInfo['fileformat'];
    }

    public static function formatFilenameToTitle($filename, $extension)
    {
        return str_replace('.'.$extension, '', str_replace('_', ' ', $filename));
    }

}