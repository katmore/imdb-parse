<?php
/*
 * Purpose:
 *    this is run when an $improject object is done
 *    ie: it's run when all the values that can be filled are filled
 */

if (empty($this->eventdata['project'])) {
   $this->eventdata['project']->total = 1;
} else {
   $this->eventdata['project']->total ++;
}
// echo "\n\n--begin project dump--\n\n";
// var_dump($improject);
// var_dump($this->eventdata);
// echo "\n\n--end project dump--\n\n";