<?php

class imepisode {
   public $season;
   public $episode;
   public $rundate; //if given or if no season.episode given
   public $iname; //imdb name of episode
   public $ename; //html escaped imdb name
   public $ehash; //md5 of projectehash.'+'.season.'+'.episode.'+'.rundate.'+'.ename
   public $projectehash; //improject.ehash
}