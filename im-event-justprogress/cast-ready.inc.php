<?php
/*
 * Purpose:
 *    this is run when an imcast object is done
 *    ie: it's run when all the values that can be filled are filled
 * 
 *    
 */

if (empty($this->eventdata['cast'])) {
   $this->eventdata['cast']->total = 1;
} else {
   $this->eventdata['cast']->total ++;
}
// echo "\n\n--begin cast dump--\n\n";
// var_dump($imcast);
// var_dump($this->eventdata);
// echo "\n\n--end cast dump--\n\n";