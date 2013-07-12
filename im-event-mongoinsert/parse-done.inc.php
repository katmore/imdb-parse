<?php
/*
 * Purpose:
 *    run after actress.list file has successfully completed parsing 
 */


echo "DONE!\n\n";

echo "\n\n--parse-done: begin eventdata dump--\n\n";
var_dump($this->eventdata);
echo "\n\n--parse-done: end eventdata dump--\n\n";