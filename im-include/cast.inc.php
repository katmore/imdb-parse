<?php
class imcast {
   
   public $ehash; //md5 of below data
   
   public $projectehash;
   
   //imactress.ehash
   public $actressehash; 
   
   public $episodehash;
   
   //[Character Name]
   public $character;
   
   public $echaracter; //html escaped char name
   
   //(as xxxxx)
   public $alias; 
   
   //<1>
   public $billpos;
   
   //(uncredited)
   public $isUncredited; //boolean
}