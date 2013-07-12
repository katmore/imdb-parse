<?php
/*
 * Purpose:
 *    this is run after actress.list file is successfully opened
 *    
 */

echo "\n\n--parse-start: begin eventdata dump--\n\n";
var_dump($this->eventdata);
echo "\n\n--parse-start: end eventdata dump--\n\n";
//die();


//IM_mongohost
//IM_mongoport


$mongo = new MongoClient("mongodb://".IM_mongohost.":".IM_mongoport);

$this->eventdata['mongo']->client = $mongo; 

$data->parserun->timestamp = time();
$data->parserun->random = mt_rand(0,100000);

//$this->eventdata['mongo']->db = $this->eventdata['mongo']->client->{'implay_'.$data->parserun->timestamp};

$this->eventdata['mongo']->db = $this->eventdata['mongo']->client->implay;

$this->eventdata['mongo']->actress = $this->eventdata['mongo']->db->selectCollection("actress");
$this->eventdata['mongo']->cast = $this->eventdata['mongo']->db->selectCollection("cast");
$this->eventdata['mongo']->episode = $this->eventdata['mongo']->db->selectCollection("episode");
$this->eventdata['mongo']->project = $this->eventdata['mongo']->db->selectCollection("project");
$this->eventdata['mongo']->parserun = $this->eventdata['mongo']->db->selectCollection("parserun");
$this->eventdata['mongo']->parselog = $this->eventdata['mongo']->db->selectCollection("parselog");

$this->eventdata['mongo']->parserun->insert($data);

//$this->eventdata['mongo']->col = $this->eventdata['mongo']->db->selectCollection("actors.".$data->parserun->timestamp);