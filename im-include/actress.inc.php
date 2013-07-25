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
   
   //idesg=I when iname=Last, First (I)
   public $idesg;
   
   //$lastname=Last when $iname=Last, First (I)
   public $lastname;
   
   //firstname=First when $iname=Last, First (I)
   public $firstname;
   
   public $origin; //actor, actress, etc
   
}




























