imdb-parse
==========

This project is not affiliated with IMDb, imdb.com, Amazon, or any related service.


The goal of this project is to have an event based parser for IMDB alternative interfaces (plaintext files) available from
http://www.imdb.com/interfaces

Description:

A php script intended to be run via php-cli (actress-cli.php) parses an entire IMDB file from begining to end. It converts the data into instances of classes (objects). These classes are defined in the im-include folder, structured to be as simple as possible. An event is triggered when an object is "ready". "Ready" means the object has all the data possible to get from the IMDB file. In the im-config.php file, one or more folders with a set of php files with predefined filenames (such as actress-ready.php) can be indicated. At certain parsing 'events' (such as when the actress class is filled completly with all available data) the corresponding event file will be run; this was the choice made to have a very simplistic method of registering a 'hook' to an event.

Usage Steps:

1. copy im-config-sample.php to im-config.php
2. download actresses.list or actors.list from above link
3. run from command line: #$php actress-cli.php actresses.list 
4. it will just display the data with im-config-sample.php values as included.
if you want to do anything else with the data, like put it into a database, you need to add another eventdir in the config file. For example, to have it insert into mongodb add the line:
	$cfgIMEventDir[] = 'im-event-mongoinsert';
to im-config.php
5. you may be interested in NOT having the data displayed (dumped to screen) as it parses, in this case just REMOVE the line
	$cfgIMEventDir[] = 'im-event-vardump';
6. you may desire to have some visual indication of progress, there is an eventdir included for this purpose.
	$cfgIMEventDir[] = 'im-event-justprogress';  

Customization:

1. if you want to have the data be stored somewhere other than MongoDB, say MySQL, start by making a copy of the folder "im-event-vardump" to "im-event-mysqlinsert", and edit files there to do the inserts. There are events that fire before and after parsing occurs (parse-start.inc.php, parse-done.inc.php) in addition to a persistent object variable that can be used to store things like your db connection variables ($this->eventdata). see the im-event-mongoinsert folder contents for an example on how that works.
2. it would be nice to contribute any customizations back here

Project License:

2-clause Free BSD License

Clarification:

This project does not contain any code that uses imdb web services, apis, etc. It can only get information from a static file.


imdb/amazon information copyright notice:

WARNING - WARNING - WARNING: The information contained in the Alternative Interfaces files is NOT free. The fact you may store data derived from the files on your own systems (in your own database server, etc) does not change the fact that IMDb/Amazon OWNS that data. IMDb/Amazon has very specified a narrow range of public uses which are allowed. The following statement comes from the Alternative Interfaces webpage:

Please refer to the copyright/license information listed in each file for instructions on allowed usage. The data is NOT FREE although it may be used for free in specific circumstances.
http://www.imdb.com/Copyright


Trademark Information:

IMDb and other terms are registered trademarks, and the use of this data in conjunction with the term "IMDb" may be restricted. Refer to IMDb's published 'conditions of use' for more information. http://www.imdb.com/help/show_article?conditions 

Reiteration: 

This project is not affiliated with IMDb, imdb.com, Amazon, or any related service.


