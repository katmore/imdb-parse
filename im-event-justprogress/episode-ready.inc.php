<?php
/*
 * Purpose:
 *    this is run when an imepisode object is done
 *    ie: it's run when all the values that can be filled are filled
 */

/*
 * maintain a total of all
 */
if (empty($this->eventdata['episode'])) {
   $this->eventdata['episode']->total = 1;
} else {
   $this->eventdata['episode']->total ++;
}
// echo "\n\n--begin episode dump--\n\n";
// var_dump($imepisode);
// var_dump($this->eventdata);
// echo "\n\n--end episode dump--\n\n";