<?php
/*
 * Purpose:
 *    this is run when an imactress object is done
 *    ie: it's run when all the values that can be filled are filled
 * 
 *    
 */

if (empty($this->eventdata['actress'])) {
   $this->eventdata['actress']->total = 1;
} else {
   $this->eventdata['actress']->total ++;
}
// echo "\n\n--begin actress dump--\n\n";
// var_dump($imactress);
// var_dump($this->eventdata);
// echo "\n\n--end actress dump--\n\n";

