<?php

namespace App\Services;

use Nette;
use Tracy\Debugger;

class GameSaver extends Nette\Object
{
    private $save;

    public function __construct(Nette\Http\Session $session)
    {
        $this->save = $session->getSection(__CLASS__);
    }

    public function add($gameId, $itemId)
    {
        Debugger::barDump($this->save[$gameId], 'gamesaver dump');

//        if ( !isset( $this->save[$gameId]) ) {
////            $this->save[$gameId] = $itemId;
//            $this->save->offsetSet($gameId . '-' . $itemId,$itemId);
//        } else {
//            $this->save->offsetSet($gameId,$itemId);
//        }

        $this->save->offsetSet($gameId . '-' . $itemId,$itemId);

    }

    public function remove()
    {
        $this->save->remove();
    }

    public function removeForGame($gameId)
    {
        $this->save->offsetUnset($gameId);
    }

    public function getItems()
    {
        return $this->save->getIterator()->getArrayCopy();
    }

    public function getItemsForGame($gameId)
    {
        if (!isset($this->save->getIterator()->getArrayCopy()[$gameId])) return null;
        return $this->save->getIterator()->getArrayCopy()[$gameId];
    }
}