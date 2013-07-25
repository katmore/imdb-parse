<?php
/*
 * Purpose:
 *    this is run when an imepisode object is done
 *    ie: it's run when all the values that can be filled are filled
 */

/*
 * maintain a total of all
 */
if (empty($this->eventdata['episode'])) {
   $this->eventdata['episode']->total = 1;
} else {
   $this->eventdata['episode']->total ++;
}
// echo "\n\n--begin episode dump--\n\n";
// var_dump($imepisode);
// var_dump($this->eventdata);
// echo "\n\n--end episode dump--\n\n";

// $qihash = array('ihash' => $imepisode->ihash);
// 
// $cihash = $this->eventdata['mongo']->episode->find($qihash)->limit(1);
// 
// //echo "\n\ncount of query:".$cihash->count()."\n\n";
// if ($cihash->count()==0) {
   //$this->eventdata['mongo']->episode->insert($imepisode);
   try {
      $this->eventdata['mongo']->episode->insert($imepisode);
   } catch (Exception $e) {
      /*
       * if it's not a 'duplicate key' error, do something fussy
       */
      if (11000!=$e->getCode()) {
         echo "\n\nmongo failure:".$e->getMessage()."\n\n";
         
         echo "\n\n--begin episode dump--\n\n";
         var_dump($imepisode);
         echo "\n\n--end episode dump--\n\n";
         
         echo "\n\n--begin parse info--\n\n";
         var_dump($this->eventdata['parse']);
         echo "\n\n--end parse info--\n\n";
         
         
         $data['timestamp'] = time();
         $data['parseinfo'] = $this->eventdata['parse'];
         $data['object_type'] = 'episode';
         $data['message'] = base64_encode($e->getMessage());
         $data['object_base64'] = base64_encode(serialize(($imepisode)));
         
         if (ACTORCLI_halt_on_parse_error) {
            echo "\n\n----begin error data dump----\n\n";
            var_dump($data);
            echo "\n\n----end error data dump----\n\n";
            die();
         }
         
         $this->eventdata['mongo']->parselog->insert($data);
         
         echo "\n\n(logged error to mongo parselog)\n\n";
      }
   }
   
//}