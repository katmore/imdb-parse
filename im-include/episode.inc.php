<?php

class imepisode {
   public $season;
   public $episode;
   public $rundate; //if given or if no season.episode given
   public $iname; //imdb name of episode
   public $ihash; //md5 of projectehash.'+'.season.'+'.episode.'+'.rundate.'+'.ename
   public $projectihash; //improject.ehash
}