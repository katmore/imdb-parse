<?php
/*
 * Purpose:
 *    this is run when an $improject object is done
 *    ie: it's run when all the values that can be filled are filled
 */

if (empty($this->eventdata['project'])) {
   $this->eventdata['project']->total = 1;
} else {
   $this->eventdata['project']->total ++;
}
// echo "\n\n--begin project dump--\n\n";
// var_dump($improject);
// var_dump($this->eventdata);
// echo "\n\n--end project dump--\n\n";

// $qihash = array('ihash' => $improject->ihash);
// 
// $cihash = $this->eventdata['mongo']->project->find($qihash)->limit(1);
// 
// //echo "\n\ncount of query:".$cihash->count()."\n\n";
// if ($cihash->count()==0) {
   //$this->eventdata['mongo']->project->insert($improject);
   try {
      $this->eventdata['mongo']->project->insert($improject);
   } catch (Exception $e) {
      /*
       * if it's not a 'duplicate key' error, do something fussy
       */
      if (11000!=$e->getCode()) {
         echo "\n\nmongo failure:".$e->getMessage()."\n\n";
         echo "\n\nerror code: ".$e->getCode()."\n\n";
         echo "\n\n--begin project dump--\n\n";
         var_dump($improject);
         echo "\n\n--end project dump--\n\n";
         
         echo "\n\n--begin parse info--\n\n";
         var_dump($this->eventdata['parse']);
         echo "\n\n--end parse info--\n\n";
         
         
         $data['timestamp'] = time();
         $data['parseinfo'] = $this->eventdata['parse'];
         $data['object_type'] = 'project';
         $data['message'] = base64_encode($e->getMessage());
         $data['object_base64'] = base64_encode(serialize($improject));
         
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