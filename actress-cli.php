<?php
/**
 * File:
 *    actor-cli.php
 * 
 * Purpose:
 *    parse IMDB files available from
 *    http://www.imdb.com/interfaces
 *    ftp://ftp.fu-berlin.de/pub/misc/movies/database/
 * 
 * Notes:
 *    i did not use regex because i hate it and it's slow
 * 
 * 
 */

mb_internal_encoding("UTF-8");

define("ACTORCLI_ver","0.1");
define("ACTORCLI_maxlines_tostart",250); 
define("ACTORCLI_maxlines_actors",0);
define("ACTORCLI_halt_on_parse_error",true);
define("ACTORCLI_max_line_size",4096);

echo "\nactor-cli is an imdb actor file parser'\n\n";
echo "\tversion: ".ACTORCLI_ver."\n";

/*
 * 
 * if missing argument for file, end script
 * 
 */
if (empty($argv[1])) {
   echo "\tusage:\n\t\tactor-cli.php /path/to/actresses(or actors).list\n";
   echo "\n";
   if (!file_exists("im-config.php")) echo "\tim-config.php is missing\n\n";
   die();
}
 
$fActress = $argv[1];

if (!file_exists("im-config.php")) {
   echo "\tim-config.php is missing\n\n";
   die();
}


/*
 * parse file argument for 'origin'
 */
$origin = "";
if (false !== ($ospos = strrpos($fActress,"/"))) {
   $origin = substr($fActress,$ospos);
} else {
   $origin = $fActress;
}

if (!empty($origin)) {
   if (false === strpos($origin,".list")) {
      $origin = "";
   }   
}

if (!empty($origin)) {
   $origin = str_replace(".list", "", $origin);
}

if (!empty($origin)) {
   echo "\torigin: $origin\n";
}

require("im-config.php");

require(IM_includedir."/actress.inc.php");
require(IM_includedir."/cast.inc.php");
require(IM_includedir."/episode.inc.php");
require(IM_includedir."/project.inc.php");
require(IM_includedir."/event.inc.php");

if (!is_readable($fActress)) {
   echo "\tcannot open .list file\n";
   echo "\t\t".$fActress."\n\n";
   die();
}

$hActress = fopen($fActress,"r");

if (!$hActress) {
   echo "\t\nresource issue opening .list file\n";
   echo "\t\t".$fActress."\n\n";
   die();
}

if (IM_verbose_parse) echo "\topened .list file\n";
if (IM_verbose_parse) echo "\t\t".$fActress."\n\n";

$eventdata = array();
$e=0;
foreach ($cfgIMEventDir as $dir) {
   
   $imactress_event[$e] = new imactress_event();
   $imactress_event[$e]->setEventdata($eventdata);
   $imactress_event[$e]->setReadyInc($dir."/actress-ready.inc.php");
   
   $imcast_event[$e] = new imcast_event();
   $imcast_event[$e]->setEventdata($eventdata);
   $imcast_event[$e]->setReadyInc($dir."/cast-ready.inc.php");
   
   $imepisode_event[$e] = new imepisode_event();
   $imepisode_event[$e]->setEventdata($eventdata);
   $imepisode_event[$e]->setReadyInc($dir."/episode-ready.inc.php");
   
   $improject_event[$e] = new improject_event();
   $improject_event[$e]->setEventdata($eventdata);
   $improject_event[$e]->setReadyInc($dir."/project-ready.inc.php");
   
   $parse_event[$e] = new parse_event();
   $parse_event[$e]->setEventdata($eventdata);
   $parse_event[$e]->setStartInc($dir."/parse-start.inc.php");
   $parse_event[$e]->setMod1000lineInc($dir."/parse-mod1000line.inc.php");

   $e++;
}



$eventdata['parse']->filename = $fActress; 
$eventdata['parse']->startstamp = time();

foreach($parse_event as $e) {
   $e->start();
}

$search = "----\t\t\t------";
$i=-1;
$eventdata['parse']->line = &$i;
for ($i=1;$i<ACTORCLI_maxlines_tostart+1;$i++) {
   
   $line = fgets($hActress,ACTORCLI_max_line_size);
   
   // echo "--begin line $i--\n";
//    
   // echo $line."\n";
//    
   // echo "--end line $i--\n";
   
   if (false !== strpos($line,$search)) {
      $liststart = ($i+1);
      if (IM_verbose_parse) echo "list starts at " . $liststart."\n";
      break 1;
   }
}

$aCount=0; //actress count
$pCount=0; //project count
while( $line = fgets($hActress,ACTORCLI_max_line_size)) {
   $i++;
   if ( ($i % 1000) == 0) {
      foreach($parse_event as $e) {
         $e->mod1000line();
      }
   }
   if (false === strpos($line,"\t")) {
      //echo "line $i has no tabs\n";
      continue;
   }
   
   $poffset = 0;
   if (substr($line,0,1)!="\t") {
      $aCount++;
      
      $actress = new imactress();
      
      $tab1pos = strpos($line,"\t");
      
      $poffset = $tab1pos;
      
      $actress->iname = utf8_encode(substr($line,0,$tab1pos));
      
      $actress->ihash = md5($actress->iname);
      
      if (IM_verbose_parse) {
         echo "\nline $i is new actress '".$actress->iname."'\n";
         echo "\tihash: ".$actress->ihash."\n";
         echo "\tname: ".$actress->iname."\n";
      }
      
      //check for imdb unique name designator (roman numeral)
      $aname = $actress->iname;
      if(false!== ($par1pos = strpos($actress->iname,"("))) {
         if (false !== ($par2pos = strpos($actress->iname,")"))) {
            $actress->idesg = substr($actress->iname,$par1pos+1,($par2pos-$par1pos)-1);
            if (IM_verbose_parse) echo "\tactor has imdb unique name designator '".$actress->idesg."'\n";
            $aname = trim(str_replace("(".$actress->idesg.")","",$aname));
         }
      }
      
      $aname_strip = str_replace("'", "", $aname);
      $aname_strip = str_replace("\"", "", $aname_strip);
      $namepart = explode(",",$aname);
      foreach($namepart as $curpart) {
         $actress->namepart[] = trim($curpart);
         $actress->lcnamepart[] = strtolower(trim($curpart));
      }
      
      $aname_strip = str_replace(",", "", $aname_strip);

      $nameword = explode(" ",$aname_strip);
      foreach($nameword as $curpart) {
         $actress->nameword[] = $curpart;
         $actress->lcnameword[] = strtolower($curpart);
      }
      
      if (count($namepart)<2) {
         
         $actress->lastname = utf8_encode($aname);
         
        if (IM_verbose_parse)  echo "\t(actor only uses 1 name)\n";
         
      } else {
         
         $actress->lastname = (trim($namepart[0]));
         $actress->lclastname = (strtolower(trim($namepart[0])));
         
         $actress->firstname = (trim($namepart[1]));
         $actress->lcfirstname = (strtolower(trim($namepart[1])));
         
         $actress->lcfirstlast = $actress->lcfirstname . " " . $actress->lclastname;
         $actress->firstlast = $actress->firstname . " " . $actress->lastname;
         
         $actress->lclastfirst = $actress->lclastname . " " . $actress->lcfirstname;
         
      }
      $actress->lclastname = (strtolower($actress->lastname));
      if (IM_verbose_parse) {
         echo "\tlastname: ".$actress->lastname."\n";
         echo "\tfirstname: ".$actress->firstname."\n";
         
         echo "\n";
      }
      
      $actress->origin = $origin;
      
      foreach($imactress_event as $e) {
         $e->ready($actress);
      }
   } else {
      //echo "line $i only contains project, so belongs to '$actress'\n";

   }
   
   $improject = new improject();
   
   /*
    * logic will assume movie unless proven some other type
    */
   $improject->type = "movie";
   
   $projectpart = trim(substr($line,$poffset));

   
   
   /*
    * check if it's a TV series
    */
   if ('"' == substr($projectpart,0,1)) { //anything starting with " is a TV series
      if (false !== ($qpos1 = strpos($projectpart,'"'))) {
         if (false === ($qpos2 = strpos($projectpart,'"',$qpos1+1))) {
            if (IM_display_exceptions) echo "\n\n\nline $i: no terminating double-quote as expected for tv\n\n";
            if (ACTORCLI_halt_on_parse_error) die();
            continue;
         }
      }
      $improject->iname = utf8_encode(substr($projectpart,$qpos1+1,($qpos2-$qpos1)-1));
      $improject->type = "tv series";
      
      /*
       * get project year
       */
      // if ("("==substr($projectpart,$qpos2+2,1)) {
         // $yrparpos1 = $qpos2+2;
         // $yrparpos2 = $yrparpos1+5;
         // if (")"==substr($projectpart,$yrparpos2,1)) {
            // $improject->iyear = substr($projectpart,$yrparpos1+1,4);
         // } else {
            // echo "\n\n\nline $i: no closing parenth as expected for project year\n\n";
            // if (ACTORCLI_halt_on_parse_error) die();
         // }
      // } else {
         // echo "\n\n\nline $i: project missing year\n\n";
         // if (ACTORCLI_halt_on_parse_error) die();
      // }
      if (false !==($yppos1 = strpos($projectpart,"(",0))) {
         if (false !==($yppos2 = strpos($projectpart,")",0))) {
            $year = substr($projectpart,$yppos1+1,($yppos2-$yppos1)-1);
            $improject->year = preg_replace("/[^0-9]/", "", $year);
         } else {
            if (IM_display_exceptions) echo "\n\n\nline $i: closing parenth for tv year does not exist as expected\n";
            if (ACTORCLI_halt_on_parse_error) die();
         }
      }
      
      
      $improject->ihash = md5($improject->iname."+".$improject->year."+".$improject->type);
      /*
       * get episode information
       */
      $epinfopart = "";
      if (false !== ($squigpos1 = strpos($projectpart,"{",0))) {
         if (false !== ($squigpos2 = strpos($projectpart,"}",$squigpos1+1))) {
            $epinfopart = substr($projectpart,$squigpos1+1,($squigpos2-$squigpos1)-1);
         }
      } 
      $imepisode = new imepisode();
      if (!empty($epinfopart)) {
         if (empty($improject->ihash)) throw new Exception('missing ihash during episode');
         $imepisode->projectihash = $improject->ihash;
         //date if exists instead of season.episode
         //iname
         
         //season number
         //episode number
         
         /*
          * get the season/episode part into a string
          */
         $seppart = "";
         if (false !== ($ppos1 = strpos($epinfopart,"(",0))) {
            if (false !== ($ppos2 = strpos($epinfopart,")",$ppos1+1))) {
               $seppart = substr($epinfopart,$ppos1+1,($ppos2-$ppos1)-1);
            }
         }
         
         /*
          * see if there's an episode title
          */
         $eptitlepart = substr($epinfopart,0,$ppos1);
         if (!empty($eptitlepart)) {
            $imepisode->iname = utf8_encode(trim($eptitlepart));
         }
         
         $sepnumpart = "";
         if (!empty($seppart)) {
            if (false !== ($dpos = strpos($seppart,"-",0))) {
               $justDate = true;
               
            } else {
               $justDate = false;
            }
         
         
            if ($justDate) {
               if (strtotime($seppart)!==false) {
                  $imepisode->rundate = $seppart;
               } else {
                  $imepisode->rundate = utf8_encode($seppart);
               }
            } else {
               if (false !== ($hpos1 = strpos($seppart,"#",0))) {
                  $sepnumpart = substr($seppart,$hpos1+1);
               }
            }
            
            if (!empty($sepnumpart)) {
               
               if (false !== (strpos($sepnumpart,".",0))) {
                  $sepnum = explode(".",$sepnumpart);
                  if (count($sepnum)==2) {
                     $imepisode->season = $sepnum[0];
                     $imepisode->episode = $sepnum[1];
                  } else {
                     if (IM_display_exceptions) echo "\n\n\nline $i: season episode not layed out as expected\n";
                     if (ACTORCLI_halt_on_parse_error) die();
                  }
                  //echo "\n\n$line\n\n";
               }
               
            }
         
         } else { /*end if has season/episode info*/
            /*
             * check if has title
             */
            $imepisode->iname = utf8_encode(trim($epinfopart));
            
            if (empty($imepisode->iname)) {
            
               if (IM_display_exceptions) echo "\n\n\nline $i: tv project did not have season/episode/date and did not contain title\n";
               if (IM_display_exceptions) echo "\n\n\nepinfopart: '$epinfopart'\n";
               if (ACTORCLI_halt_on_parse_error) die();
            }
         }
         

         if (empty($imepisode->projectihash)) throw new Exception("cannot save episode without project to reference (missing projectihash)");
         $imepisode->ihash = md5(
            $imepisode->projectihash."+".
            $imepisode->season."+".
            $imepisode->episode."+".
            $imepisode->rundate."+".
            $imepisode->iname
         );  
         
   
   // public season;
   // public episode;
   // public rundate; //if given or if no season.episode given
   // public iname; //imdb name of episode
         
         if (IM_verbose_parse) {
            echo "\t\t\tseason: '".$imepisode->season."'\n";
            echo "\t\t\tepisode: '".$imepisode->episode."'\n";
            echo "\t\t\trundate: '".$imepisode->rundate."'\n";
            echo "\t\t\tiname: '".$imepisode->iname."'\n";
            echo "\t\t\tprojectihash: '".$imepisode->projectihash."'\n\n";
            
            echo "\t\t\tepinfopart: '".$epinfopart."'\n\n";
         }
         
         //$imepisode_event->ready($imepisode);
         
         foreach ($imepisode_event as $e) {
            $e->ready($imepisode);
         }
      } else { /*end if it has an episode part*/
         if (IM_verbose_parse) echo "\n\n\nline $i: tv project did not have episode information\n";
      }
      
   } /*end if TV project*/ 
   else {
      /*
       * it is a Movie
       */
      //$projectpart
      
      /*
       * get project year
       */
      if (false !==($yppos1 = strpos($projectpart,"(",0))) {
         if (false !==($yppos2 = strpos($projectpart,")",0))) {
            $year = substr($projectpart,$yppos1+1,($yppos2-$yppos1)-1);
            $improject->year = preg_replace("/[^0-9]/", "", $year);
         } else {
            if (IM_display_exceptions) echo "\n\n\nline $i: closing parenth for movie year does not exist as expected\n";
            if (ACTORCLI_halt_on_parse_error) die();
         }
      
         
         //(TV)           = TV movie, or made for cable movie
         //(V)            = made for video movie (this category does NOT include TV
         if (false !== (strpos($projectpart,"(TV)",$yppos2))) {
            $improject->type = "tv movie";
         } else
         if (false !== (strpos($projectpart,"(V)",$yppos2))) {
            $improject->type = "direct to video movie";
         }
         
         $improject->iname = utf8_encode(trim(substr($projectpart,0,$yppos1 - 1)));

         $improject->ihash = md5($improject->iname."+".$improject->year."+".$improject->type); 
      } else {
         if (IM_display_exceptions) echo "\n\n\nline $i: movie year info not as expected\n";
         if (ACTORCLI_halt_on_parse_error) die();
      }
   }
   
   $imcast = new imcast();
   
   if (false !==($bpos1 = strpos($projectpart,"[",0))) {
      
      if (false !==($bpos2 = strpos($projectpart,"]",0))) {
         
         $imcast->character = utf8_encode(substr($projectpart,$bpos1+1,($bpos2-$bpos1)-1));
         
      }
      
   }
   
   $testUncredited = substr($projectpart,$bpos1-13,strlen("uncredited"));
   
   if ($testUncredited=='uncredited') {
      $imcast->isUncredited = 'true';
      if (IM_verbose_parse) echo "\n\n\nis uncredited\n";
   } else {
      $imcast->isUncredited = 'false';
   }
   
   if (false !==($cpos1 = strpos($projectpart,"<",0))) {
      
      if (false !==($cpos2 = strpos($projectpart,">",0))) {
         
         $imcast->billpos = substr($projectpart,$cpos1+1,($cpos2-$cpos1)-1);
         
      }
      
   }
   
   
   if (false !==($aspos1 = strpos($projectpart,"(as ",0))) {
      //echo "\n\n\nfound alias\n";die();
      if (false !== ($aspos2 = strpos($projectpart,")",$aspos1+1))) {
         
         $imcast->alias = utf8_encode(substr($projectpart,$aspos1+4,($aspos2-($aspos1+4))-1));
         
          
      }
      
   }
   
   if (empty($actress->ihash)) throw new Exception("missing actress ihash");
   
   $imcast->actressihash = $actress->ihash;
   
   if (empty($improject->ihash)) throw new Exception("missing project ihash");
   
   $imcast->projectihash = $improject->ihash;
   
   if (!empty($imepisode->ihash)) {
      $imcast->episodeihash = $imepisode->ihash;
   }
   
   $imcast->ihash = md5 ( 
      $imcast->projectihash . "+" . 
      $imcast->actressihash . "+" . 
      $imcast->character . "+" . 
      $imcast->episodeihash . "+" . 
      $imcast->alias . "+" . 
      $imcast->billpos . "+" . 
      $imcast->isUncredited
      );
   
   //echo "\n\ntestUncredited: '$testUncredited'\n\n";
   if (IM_verbose_parse) {
      echo "\t\t\tcharacter: '".$imcast->character."'\n";
      echo "\t\t\talias: '".$imcast->alias."'\n";
      echo "\t\t\tbillpos: '".$imcast->billpos."'\n";
      echo "\t\t\tisUncredited: '".$imcast->isUncredited."'\n";
      echo "\t\t\tprojectihash: '".$imcast->projectihash."'\n";
      echo "\t\t\tactressihash: '".$imcast->actressihash."'\n";
      echo "\t\t\tihash: '".$imcast->ihash."'\n\n";
      
      // if (!empty($imcast->alias)) {
         // echo "\n\n\nfound alias\n";die();
      // }
      
      echo "\t\tiname: '".$improject->iname."'\n";
      echo "\t\tihash: '".$improject->ihash."'\n";
      echo "\t\ttype: '".$improject->type."'\n";
      echo "\t\tyear: '".$improject->year."'\n\n";
   }
   
   //$imcast_event->ready($imcast);
   foreach($imcast_event as $e) {
      $e->ready($imcast);
   }
   
   //$improject_event->ready($improject);
   $improject->ihash = md5($improject->iname."+".$improject->year."+".$improject->type); 
   foreach($improject_event as $e) {
      $e->ready($improject);
   }
   if (ACTORCLI_maxlines_actors>0) {
      if (ACTORCLI_maxlines_actors+$liststart<=$i) {
         echo "\n\tend parse: ACTORCLI_maxlines_actors limits us\n\n";
         break 1;
      }
   }
   
   //if ($i==47298) die();
}

$eventdata['parse']->donestamp = time();

/*
 * replace this with 'done' event call
 */
echo "\n\n -- done parsing --\n\n";
var_dump($eventdata);
echo "\n\n -- end script --\n\n";

/*
 * 
 * 
RULES:
1       Movies and recurring TV roles only, no guest appearances
2       Please submit entries in the format outlined at the end of the list
3       Feel free to submit new actresses

"xxxxx"        = a television series
"xxxxx" (mini) = a television mini-series
[xxxxx]        = character name
<xx>           = number to indicate billing position in credits
(TV)           = TV movie, or made for cable movie
(V)            = made for video movie (this category does NOT include TV
                 episodes repackaged for video, guest appearances in
                 variety/comedy specials released on video, or
                 self-help/physical fitness videos)

Extra Rules for TV series:
{tv episode title if exists (episode-designation)} = episode description
episode-designation = YYYY-MM-DD |OR| #Season.Episode ie: #2.3
 * 
 * 
 */














