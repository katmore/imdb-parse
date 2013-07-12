<?php

class implayevent {
   protected $eventdata;

   public function setEventdata(&$eventdata) {
      $this->eventdata = &$eventdata;
   }
}

class parse_event extends implayevent {
   private $mod1000lineInc;
   private $startInc;
   
   public function setMod1000lineInc($eventinc) {
      $this->mod1000lineInc = $eventinc;
   }
   public function mod1000line() {
      require($this->mod1000lineInc);
   }
   
   public function setStartInc($eventinc) {
      $this->startInc = $eventinc;
   }
   public function start() {
      require($this->startInc);
   }
}

class implay_readyevent extends implayevent {
   protected $readyInc;
   public function setReadyInc($eventinc) {
      $this->readyInc = $eventinc;
   }
}

class imactress_event extends implay_readyevent {

   public function ready(imactress $imactress) {
      //require(IM_eventdir."/actress-ready.inc.php");
      require($this->readyInc);
   }
}

class imcast_event extends implay_readyevent {

   public function ready(imcast $imcast) {
      //require(IM_eventdir."/cast-ready.inc.php");
      require($this->readyInc);
   }
}

class imepisode_event extends implay_readyevent {

   public function ready(imepisode $imepisode) {
      //require(IM_eventdir."/episode-ready.inc.php");
      require($this->readyInc);
   }
}

class improject_event extends implay_readyevent {

   public function ready(improject $improject) {
      //require(IM_eventdir."/project-ready.inc.php");
      require($this->readyInc);
   }
}










