<?php
/*
 * Purpose:
 *    this is run when an imactress object is done
 *    ie: it's run when all the values that can be filled are filled
 * 
 *    
 */

if (empty($this->eventdata['actress'])) {
   $this->eventdata['actress']->total = 1;
} else {
   $this->eventdata['actress']->total ++;
}



// echo "\n\n--begin actress dump--\n\n";
// var_dump($imactress);
// var_dump($this->eventdata);
// echo "\n\n--end actress dump--\n\n";

$qEhash = array('ehash' => $imactress->ehash);

$cEhash = $this->eventdata['mongo']->actress->find($qEhash)->limit(1);

//echo "\n\ncount of query:".$cEhash->count()."\n\n";
// if ($cEhash->count()==0) {
   // $this->eventdata['mongo']->actress->insert($imactress);
// }

if ($cEhash->count()==0) {
   try {
      $this->eventdata['mongo']->actress->insert($imactress);
   } catch (Exception $e) {
      echo "\n\nmongo failure:".$e->getMessage()."\n\n";
      
      echo "\n\n--begin actress dump--\n\n";
      var_dump($imactress);
      echo "\n\n--end actress dump--\n\n";
      
      echo "\n\n--begin parse info--\n\n";
      var_dump($this->eventdata['parse']);
      echo "\n\n--end parse info--\n\n";
      
      
      $data['timestamp'] = time();
      $data['parseinfo'] = $this->eventdata['parse'];
      $data['object_type'] = 'actress';
      $data['message'] = base64_encode($e->getMessage());
      $data['object_base64'] = base64_encode(serialize(($imactress)));
      
      $this->eventdata['mongo']->parselog->insert($data);
      
      echo "\n\n(logged error to mongo parselog)\n\n";
      
   }
}



