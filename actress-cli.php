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

function IM_htmlentities($string) {
   return htmlentities($string,ENT_QUOTES,'UTF-8',false);
}

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
while( $line = utf8_encode(fgets($hActress,ACTORCLI_max_line_size))) {
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
      
      $actress->ename = IM_htmlentities($actress->iname);
      
      $actress->ehash = md5($actress->ename);
      
      if (IM_verbose_parse) {
         echo "\nline $i is new actress '".$actress->iname."'\n";
         echo "\tihash: ".$actress->ihash."\n";
         echo "\tescaped: ".$actress->ename."\n";
         echo "\tehash: ".$actress->ehash."\n";
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
         $actress->namepart[] = $curpart;
         $actress->lcnamepart[] = strtolower($curpart);
      }
      
      $aname_strip = str_replace(",", "", $aname_strip);

      $nameword = explode(" ",$aname_strip);
      foreach($nameword as $curpart) {
         $actress->nameword[] = $curpart;
         $actress->lcnameword[] = strtolower($curpart);
      }
      
      if (count($namepart)<2) {
         
         $actress->lastname = utf8_encode($aname);
         
         $actress->elastname = IM_htmlentities($actress->lastname);
         
        if (IM_verbose_parse)  echo "\t(actor only uses 1 name)\n";
         
      } else {
         
         $actress->lastname = utf8_encode(trim($namepart[0]));
         $actress->firstname = utf8_encode(trim($namepart[1]));
         
         $actress->elastname = IM_htmlentities($actress->lastname);
         $actress->efirstname = IM_htmlentities($actress->firstname);
         
         $actress->lcfirstname = strtolower($actress->firstname);
         $actress->lcefirstname = strtolower($actress->efirstname);
         
         $actress->lclastname = strtolower($actress->lastname);
         $actress->lcelastname = strtolower($actress->elastname);
         
         $actress->lcfirstlast = $actress->lcfirstname . " " . $actress->lclastname;
         $actress->firstlast = $actress->firstname . " " . $actress->lastname;
      }
      $actress->lclastname = utf8_encode(strtolower($actress->lastname));
      $actress->lcelastname = strtolower($actress->elastname);
      if (IM_verbose_parse) {
         echo "\tlastname: ".$actress->lastname."\n";
         echo "\tfirstname: ".$actress->firstname."\n";
         
         echo "\telastname: ".$actress->elastname."\n";
         echo "\tefirstname: ".$actress->efirstname."\n";
         
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
      $improject->ename = IM_htmlentities($improject->iname);
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
            $improject->iyear = substr($projectpart,$yppos1+1,($yppos2-$yppos1)-1);
            $improject->year = preg_replace("/[^0-9]/", "", $improject->iyear);
         } else {
            if (IM_display_exceptions) echo "\n\n\nline $i: closing parenth for tv year does not exist as expected\n";
            if (ACTORCLI_halt_on_parse_error) die();
         }
      }
      
      
      $improject->ehash = md5($improject->ename."+".$improject->iyear."+".$improject->type); //md5 of ename.'+'.year.'+'.$type
      
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
         
         $imepisode->projectehash = $improject->ehash;
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
            $imepisode->ename = IM_htmlentities($imepisode->iname);
         }
         
         $sepnumpart = "";
         if (!empty($seppart)) {
            

            
            if (false !== ($dpos = strpos($seppart,"-",0))) {
               $justDate = true;
               
            } else {
               $justDate = false;
            }
         
         
            if ($justDate) {
               $imepisode->rundate = $seppart;
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
               }
               
            }
         
         } else { /*end if has season/episode info*/
            /*
             * check if has title
             */
            $imepisode->iname = trim($epinfopart);
            $imepisode->ename = IM_htmlentities($imepisode->ename);
            
            if (empty($imepisode->iname)) {
            
               if (IM_display_exceptions) echo "\n\n\nline $i: tv project did not have season/episode/date and did not contain title\n";
               if (IM_display_exceptions) echo "\n\n\nepinfopart: '$epinfopart'\n";
               if (ACTORCLI_halt_on_parse_error) die();
            }
         }
         

         
         $imepisode->ehash = md5(
            $imepisode->projectehash."+".
            $imepisode->season."+".
            $imepisode->episode."+".
            $imepisode->rundate."+".
            $imepisode->ename
         );  //md5 of projectehash.'+'.season.'+'.episode.'+'.rundate.'+'.ename
         
         //ename
         //ehash is //md5 of projectehash.'+'.season.'+'.episode
   // public season;
   // public episode;
   // public rundate; //if given or if no season.episode given
   // public iname; //imdb name of episode
   // public ename; //html escaped imdb name
   // public ehash; //md5 of projectehash.'+'.season.'+'.episode.'+'.rundate
   // public projectehash; //improject.ehash
         
         if (IM_verbose_parse) {
            echo "\t\t\tseason: '".$imepisode->season."'\n";
            echo "\t\t\tepisode: '".$imepisode->episode."'\n";
            echo "\t\t\trundate: '".$imepisode->rundate."'\n";
            echo "\t\t\tiname: '".$imepisode->iname."'\n";
            echo "\t\t\tename: '".$imepisode->ename."'\n";
            echo "\t\t\tehash: '".$imepisode->ehash."'\n";
            echo "\t\t\tprojectehash: '".$imepisode->projectehash."'\n\n";
            
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
            $improject->iyear = substr($projectpart,$yppos1+1,($yppos2-$yppos1)-1);
            $improject->year = preg_replace("/[^0-9]/", "", $improject->iyear);
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
         $improject->ename = IM_htmlentities($improject->ename);

         $improject->ehash = md5($improject->ename."+".$improject->iyear."+".$improject->type); //md5 of ename.'+'.year.'+'.$type
         
      } else {
         if (IM_display_exceptions) echo "\n\n\nline $i: movie year info not as expected\n";
         if (ACTORCLI_halt_on_parse_error) die();
      }
   }
   
   $imcast = new imcast();
   
   if (false !==($bpos1 = strpos($projectpart,"[",0))) {
      
      if (false !==($bpos2 = strpos($projectpart,"]",0))) {
         
         $imcast->character = utf8_encode(substr($projectpart,$bpos1+1,($bpos2-$bpos1)-1));
         $imcast->echaracter = IM_htmlentities($imcast->character);
         
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
   
   
   $imcast->actressehash = $actress->ehash;
   
   $imcast->projectehash = $improject->ehash;
   
   if (!empty($imepisode->ehash)) {
      $imcast->episodehash = $imepisode->ehash;
   }
   
   $imcast->ehash = md5 ( 
      $imcast->projectehash . "+" . 
      $imcast->actressehash . "+" . 
      $imcast->echaracter . "+" . 
      $imcast->episodehash . "+" . 
      $imcast->alias . "+" . 
      $imcast->billpos . "+" . 
      $imcast->isUncredited
      );
   
   //echo "\n\ntestUncredited: '$testUncredited'\n\n";
   if (IM_verbose_parse) {
      echo "\t\t\tcharacter: '".$imcast->character."'\n";
      echo "\t\t\techaracter: '".$imcast->echaracter."'\n";
      echo "\t\t\talias: '".$imcast->alias."'\n";
      echo "\t\t\tbillpos: '".$imcast->billpos."'\n";
      echo "\t\t\tisUncredited: '".$imcast->isUncredited."'\n";
      echo "\t\t\tprojectehash: '".$imcast->projectehash."'\n";
      echo "\t\t\tactressehash: '".$imcast->actressehash."'\n";
      echo "\t\t\tehash: '".$imcast->ehash."'\n\n";
      
      // if (!empty($imcast->alias)) {
         // echo "\n\n\nfound alias\n";die();
      // }
      
      echo "\t\tiname: '".$improject->iname."'\n";
      echo "\t\tename: '".$improject->ename."'\n";
      echo "\t\tehash: '".$improject->ehash."'\n";
      echo "\t\ttype: '".$improject->type."'\n";
      echo "\t\tyear: '".$improject->iyear."'\n\n";
   }
   
   //$imcast_event->ready($imcast);
   foreach($imcast_event as $e) {
      $e->ready($imcast);
   }
   
   //$improject_event->ready($improject);
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














