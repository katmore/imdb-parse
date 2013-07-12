<?php
/*
 * Purpose:
 *    every 1000 lines this event is run
 */

echo "\n\n--parse-mod1000line: begin eventdata dump--\n\n";
var_dump($this->eventdata);
echo "\n\n--parse-mod1000line: end eventdata dump--\n\n";
sleep(5);