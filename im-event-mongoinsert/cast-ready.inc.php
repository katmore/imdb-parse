<?php
/*
 * Purpose:
 *    this is run when an imcast object is done
 *    ie: it's run when all the values that can be filled are filled
 * 
 *    
 */

if (empty($this->eventdata['cast'])) {
   $this->eventdata['cast']->total = 1;
} else {
   $this->eventdata['cast']->total ++;
}
// echo "\n\n--begin cast dump--\n\n";
// var_dump($imcast);
// var_dump($this->eventdata);
// echo "\n\n--end cast dump--\n\n";

$qEhash = array('ehash' => $imcast->ehash);

$cEhash = $this->eventdata['mongo']->cast->find($qEhash)->limit(1);

//echo "\n\ncount of query:".$cEhash->count()."\n\n";
if ($cEhash->count()==0) {
   try {
      $this->eventdata['mongo']->cast->insert($imcast);
   } catch (Exception $e) {
      echo "\n\nmongo failure:".$e->getMessage()."\n\n";
      
      echo "\n\n--begin cast dump--\n\n";
      var_dump($imcast);
      echo "\n\n--end cast dump--\n\n";
      
      echo "\n\n--begin parse info--\n\n";
      var_dump($this->eventdata['parse']);
      echo "\n\n--end parse info--\n\n";
      
      $data['timestamp'] = time();
      $data['parseinfo'] = $this->eventdata['parse'];
      $data['object_type'] = 'cast';
      $data['message'] = base64_encode($e->getMessage());
      $data['object_base64'] = base64_encode(serialize(($imcast)));
      
      $this->eventdata['mongo']->parselog->insert($data);
      
      echo "\n\n(logged error to parselog)\n\n";
   }
}













