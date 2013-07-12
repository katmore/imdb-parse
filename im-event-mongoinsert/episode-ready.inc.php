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

$qEhash = array('ehash' => $imepisode->ehash);

$cEhash = $this->eventdata['mongo']->episode->find($qEhash)->limit(1);

//echo "\n\ncount of query:".$cEhash->count()."\n\n";
if ($cEhash->count()==0) {
   //$this->eventdata['mongo']->episode->insert($imepisode);
   try {
      $this->eventdata['mongo']->episode->insert($imepisode);
   } catch (Exception $e) {
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
      
      $this->eventdata['mongo']->parselog->insert($data);
      
      echo "\n\n(logged error to mongo parselog)\n\n";
      
   }
   
}