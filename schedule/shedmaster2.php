<?php // $Id: shedmaster2.php,v 1.5 2012/02/13 10:32:25 shtifanov Exp $

//Замечание: всю неделю крутим только в кейсе
    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

	if (!has_capability('block/mou_school:editschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    $gid = optional_param('gid', 0, PARAM_INT);   // Class id
    $pid = optional_param('pid', 0, PARAM_INT);   // Parallel number
    $termid = optional_param('termid', 0, PARAM_INT); // Term id
    $nw = optional_param('nw', 1, PARAM_INT);   // Number of week in study year 
 	$COUNT_OF_DAY_IN_WEEK = required_param('cdw', PARAM_INT);
  	$MAX_LESSON_IN_DAY = required_param('mld', PARAM_INT);

	$GLDATESTART = array();
	$curryearfull = current_edu_year();
	$curyear = explode('/', $curryearfull);
	$datestartGLOB = $curyear[0].'-09-01';
	$dateendGLOB = $curyear[1].'-05-31';	
    $allweeksinyear = make_all_weeks_in_year($datestartGLOB, $dateendGLOB);
	
	$arr_date = explode ('-', $datestartGLOB);
	$temdatestart = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
	$arr_date = explode ('-', $dateendGLOB);
	$temdateend = make_timestamp ($arr_date[0],  $arr_date[1], $arr_date[2], 12);
		
	$redirlink = "createschedule.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;gid=$gid&amp;nw=$nw";
				
    if ($recs = data_submitted())  {
    	//print_r($recs);
    	if (isset($recs->submit1)){
    		$sheds = get_example_shedule($sid, $gid, 1);
    	}	else if (isset($recs->submit2)){
  			$sheds = get_example_shedule($sid, $gid, 2);
  		} else  if (isset($recs->submit3)){
  			$sheds = get_example_shedule($sid, $gid, 2);
  		}
	
		
  		if (!empty($sheds))	{
  			$ids = array();
			$datestart = $GLDATESTART[$nw];
			$strdate1 = date("Y-m-d", $datestart);
			$datend = $datestart + $COUNT_OF_DAY_IN_WEEK*DAYSECS;
			$strdate2 = date("Y-m-d", $datend);

			$strsql = "classid = $gid AND datestart >= '$strdate1' AND datestart <= '$strdate2'";
		   	//echo $strsql.'<hr>';
			if (record_exists_select('monit_school_class_schedule_'.$rid, $strsql)){
		   		notice(get_string('existrecord','block_mou_school'), "createschedule.php?rid=$rid&amp;sid=$sid&amp;gid=$gid&amp;yid=$yid&amp;nw=$nw");
		   	}

		   	foreach ($sheds as $i => $shed)	{
		   	 	$strdate = date("Y-m-d", $datestart);
			 	$rusformat = date("d.m.y", $datestart);
				//	print_r($rusformat);
				// print_r($SCHDATE[$i]);	   		
			   		$lesson = get_records_select('monit_school_schedule_bells',"schoolid = $sid AND weekdaynum=$i", 'lessonnum');
					foreach ($lesson as $less){
						$ids[$less->lessonnum] = $less->id;
					}
					if ($datestart >= $temdatestart && $datestart <= $temdateend)	{
				       	foreach ($shed as $j => $discid)	{
						  		
							$newsched->schoolid = $sid;
							$newsched->classid  = $gid;						
							$newsched->disciplineid = $discid;
							$newsched->lessonid  = 0; // !!!!
							$newsched->teacherid = 0;
			     			$newsched->roomid = 0;
			     			$newsched->datestart = $strdate;
			   				$newsched->schedulebellsid = $ids[$j];
	
							$strsql = "SELECT id FROM {$CFG->prefix}monit_school_class_discipline
									   WHERE classid=$gid and disciplineid = $discid";
							$classdisciplines = get_records_sql ($strsql);
							//print_r($classdisciplines);		
							foreach($classdisciplines as $classdiscipline) {
								$newsched->classdisciplineid = $classdiscipline->id;	
			   				
								if (record_exists_select('monit_school_class_schedule_'.$rid, 
													 "classdisciplineid = {$newsched->classdisciplineid} AND 
													  datestart = '{$newsched->datestart}' AND 
													  schedulebellsid = {$newsched->schedulebellsid}")) continue;			   				
	
	  							if (!insert_record('monit_school_class_schedule_'.$rid, $newsched))	{
	     							print_r($newsched);
									error(get_string('errorininseringschedule','block_mou_school'), $redirlink);
	  								// notify(get_string('succesavedata','block_mou_school'), 'green');
	  							}
				     		}
						}
					}
				$datestart = $datestart + DAYSECS;	
			}
			
		    redirect($redirlink, get_string('succesavedata','block_mou_school'), 0);	
			exit();  			
  		}

    }

	$table->head  = array (get_string('dayanddiscipline', 'block_mou_school'), 
	 					   get_string('disciplineandday','block_mou_school'),
						   get_string('withmaxcolhours','block_mou_school'));

	$table->align = array ('left', 'left', 'left');
    $table->size = array ('33%', '33%', '33%');
	$table->columnwidth = array (33,  33, 33);
    $table->class = 'moutable';
   	$table->width = '70%';
    $table->titles = array();
    $table->titles[] = get_string('createschedule', 'block_mou_school');
    $table->worksheetname = 'createschedule';

	$tabledata = array();
	$tabledata[0] = $tabledata[1] = $tabledata[2] = '';
	$sheds = get_example_shedule($sid, $gid, 1);

   	foreach ($sheds as $i => $shed)	{
     	$tabledata[0] .= '<b>'.get_string('day1','block_mou_school',$i).'</b>'.'<br>';
       	foreach ($shed as $j => $discid)	{	
   			 $dscp = get_record('monit_school_discipline', 'id', $discid);
     		$tabledata[0] .= $j.'.'.$dscp->name.'<br>';
     	}	
   	}

	$sheds = get_example_shedule($sid, $gid, 2);
   	foreach ($sheds as $i => $shed)	{
     	$tabledata[1] .= '<b>'.get_string('day1','block_mou_school',$i).'</b>'.'<br>';
       	foreach ($shed as $j => $discid)	{	
   			 $dscp = get_record('monit_school_discipline', 'id', $discid);
     		$tabledata[1] .= $j.'.'.$dscp->name.'<br>';
     	}	
   	}

	$sheds = get_example_shedule($sid, $gid, 3);
   	foreach ($sheds as $i => $shed)	{
     	$tabledata[2] .= '<b>'.get_string('day1','block_mou_school',$i).'</b>'.'<br>';
       	foreach ($shed as $j => $discid)	{	
   			 $dscp = get_record('monit_school_discipline', 'id', $discid);
     		$tabledata[2] .= $j.'.'.$dscp->name.'<br>';
     	}	
   	}

	$table->data[] = array($tabledata[0], $tabledata[1], $tabledata[2]);  
			          
			          
	$submit1 = '<input type="submit" name="submit1" value="'. get_string('applay','block_mou_school') . '">';
	$submit2 = '<input type="submit" name="submit2" value="'. get_string('applay','block_mou_school') . '">';
	$submit3 = '<input type="submit" name="submit3" value="'. get_string('applay','block_mou_school') . '">';
	$table->data[] = array($submit1, $submit2, $submit3);  
	
	echo  '<form name="applay" method="post" action="shedmaster2.php">';
	echo  '<input type="hidden" name="rid" value="' . $rid . '">';
	echo  '<input type="hidden" name="sid" value="' . $sid . '">';
	echo  '<input type="hidden" name="yid" value="' . $yid . '">';
	echo  '<input type="hidden" name="pid" value="' . $pid . '">';
	echo  '<input type="hidden" name="gid" value="' . $gid . '">';	          
	echo  '<input type="hidden" name="termid" value="' . $termid . '">';
	echo  '<input type="hidden" name="nw" value="' . $nw . '">';
	echo  '<input type="hidden" name="cdw" value="' . $COUNT_OF_DAY_IN_WEEK . '">';
	echo  '<input type="hidden" name="mld" value="' . $MAX_LESSON_IN_DAY . '">';
	print_color_table($table);
	echo  '</form>';

    print_footer();
 
 

function get_example_shedule($sid, $gid, $variant=1)
{
	global $CFG, $COUNT_OF_DAY_IN_WEEK, $MAX_LESSON_IN_DAY, $rid, $yid;
	
 $sheds = array();	
 if ($class = get_record ('monit_school_class', 'id', $gid))     {
 	
	  $hoursdisciplines = array();
	  $strsql = "SELECT id, name, disciplineid FROM {$CFG->prefix}monit_school_class_discipline
	                                     WHERE classid=$gid
	                                     ORDER BY name";
                              
	  if ($classdisciplines = get_records_sql ($strsql))     {
	  	
	       foreach ($classdisciplines as $classdiscipline) {
	            if ($curriculum = get_record_select('monit_school_curriculum', " (schoolid = $sid) AND (classid = $gid) AND (disciplineid = {$classdiscipline->disciplineid})"))  {
	                 $discipline = get_record('monit_school_discipline', 'id', $classdiscipline->disciplineid);
	                 $hoursdisciplines[$classdiscipline->disciplineid] = $curriculum->hours; 
	            } else {																	
	                 notice (get_string('hourscount','block_mou_school', $classdiscipline->name), $CFG->wwwroot."/blocks/mou_school/curriculum/curriculum.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
	            }
	       }

	       $sumhours = 0;
	       foreach ($hoursdisciplines as $dhour)     {
	            $sumhours += $dhour;
	       } 

	       $h_oneday = (int)((int)$sumhours /((int)$COUNT_OF_DAY_IN_WEEK - 1));
	       $h_6day = $sumhours - ($h_oneday*($COUNT_OF_DAY_IN_WEEK-1));
	       
			switch ($variant) 	{
				case 1: 		
				       for ($i=1; $i<=$COUNT_OF_DAY_IN_WEEK; $i++)     {
				            $j = 1;
				            foreach ($hoursdisciplines as $discid => $dhour)     {
				            	 if ($dhour == 0) continue;
				                 if ($j <= $h_oneday)  {
	                    			  $sheds[$i][$j]=$discid;
				                      $hoursdisciplines[$discid] = $dhour-1;
				                      $j++;
				                 } else {
				                      break;
				                 }
				    		}
				      }			
				break;
				
				case 2:   
				       $i = $j = 1;
		               do {
			               foreach ($hoursdisciplines as $discid => $dhour)     {
			              	
								if ($dhour == 0) continue;
  								$sheds[$i][$j]=$discid;
			                    $hoursdisciplines[$discid] = $dhour-1;
									               	
			               		if ($j < $h_oneday) {
			               			$j++;	
			               		}
			               		else {
			               			$j = 1;
			               			$i++;
			               		}
			            	}
						} while ($i<$COUNT_OF_DAY_IN_WEEK);	
				break;
				
				case 3:    
						$i = $j = 1;
		               do {
			               foreach ($hoursdisciplines as $discid => $dhour)     {
			              	
								if ($dhour == 0) continue;
			                    $hoursdisciplines[$discid] = $dhour-1;
			                    $sheds[$i][$j]=$discid;
									               	
			               		if ($j < $h_oneday) {
			               			$j++;	
			               		}
			               		else {
			               			$j = 1;
			               			$i++;
			               		}
			            	}
						} while ($i<$COUNT_OF_DAY_IN_WEEK);		
		               	
		               	foreach ($sheds as $i => $shed)	{
		               		if ($i == $COUNT_OF_DAY_IN_WEEK) break;
		               		$countshed = count($shed);
		               		if ($countshed > $MAX_LESSON_IN_DAY) {
		               			$j = $countshed - $MAX_LESSON_IN_DAY;
		               			for ($m=1, $k=$countshed; $m<$j; $m++, $k--) {
		               				$sheds[$COUNT_OF_DAY_IN_WEEK][] = $sheds[$i][$k];
									unset($sheds[$i][$k]);    
		               			}
		               		}
		               	}
	           break;
			}
	       
	  	
	 }
 }

 return $sheds;

}		       	
				

?>