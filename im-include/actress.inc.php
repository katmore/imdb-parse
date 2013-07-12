<?php
/**
 * File:
 *    im.inc.php
 * 
 * Purpose:
 *    really simple class objects for storing data between php/js
 * 
 * 
 */

 

class imactress {
   
   //$iname=Last, First (I) when $line=Last, First (I)\t...
   public $iname; //imdb name
   
   public $ihash; //md5 hash of name as in file
   
   public $ename; //htmlentities escaped imdb name
   
   public $ehash; //md5 hash of ename
   
   //idesg=I when iname=Last, First (I)
   public $idesg;
   
   //$lastname=Last when $iname=Last, First (I)
   public $lastname;
   
   public $elastname;
   
   //firstname=First when $iname=Last, First (I)
   public $firstname;
   
   public $efirstname;
   
   public $iref; //imdb reference number
   
   public $origin; //actor, actress, etc
   
}




























