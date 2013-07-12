<?php

class improject {
   public $iname; //imdb name
   public $ename; //html escaped imdb name
   public $ehash; //md5 of ename.'+'.year.'+'.$type
   public $type; //movie,tv series,tv mini series,tv movie,direct to video
   public $iyear;
   public $year;
   public $iref; //imdb reference number
}
