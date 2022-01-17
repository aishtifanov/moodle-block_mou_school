<?php // $Id: timelessons.php,v 1.16 2012/02/13 10:32:25 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');    
	require_once('../authbase.inc.php');

	if (!has_capability('block/mou_school:editschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	
    $wid = optional_param('wid', 1, PARAM_INT);   // Day number in week

    switch ($action)	{
    	case 'excel': timelessons_download($rid, $sid, $yid);
        			  exit();

		case 'del':   $tid = required_param('tid', PARAM_INT);       // Time lesson id
					  if (!record_exists_mou('monit_school_class_schedule_'.$rid, 'schedulebellsid', $tid))	{
					  	delete_records('monit_school_schedule_bells', 'id', $tid);
					  	notify(get_string('notifydeltimelesson', 'block_mou_school'), 'green');
		  			  } else {
		  			  	notify(get_string('errordeltimelesson', 'block_mou_school'));
		  			  }
		break;
	}


    if ($recs = data_submitted())  {
		$redirlink = "timelessons.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
		
		if (isset($recs->nextday) && $wid < 6)	{
				$newwid = $wid+1;
				if ($timelessons = get_records_select('monit_school_schedule_bells', "schoolid=$sid  AND weekdaynum = $wid")) {			
					foreach ($timelessons as $timelesson)	{
						if (!record_exists_select('monit_school_schedule_bells', "schoolid=$sid AND smena={$timelesson->smena} AND lessonnum = {$timelesson->lessonnum} AND weekdaynum = $newwid")) {	
							unset($timelesson->id);
							$timelesson->weekdaynum = $newwid;
				       		if (!insert_record('monit_school_schedule_bells', $timelesson))	{
								error(get_string('errorinaddingshedulebells','block_mou_school'), $redirlink);
					  		}
						}	
					}
				}
				redirect("timelessons.php?sid=$sid&amp;yid=$yid&amp;rid=$rid&amp;wid=$newwid", '', 0);	
		}
			
 		if (isset($recs->setexample))	{
	        $timelesson->schoolid = $sid;
		    $timelesson->yearid = $yid;
		    $timelesson->smena = 1;
			$arrstart= array('08:30:00', '09:25:00', '10:25:00', '11:20:00', '12:15:00', '13:10:00', '14:10:00','15:05:00');
			$arrend = array('09:15:00', '10:10:00','11:10:00','12:05:00','13:00:00','13:55:00','14:55:00','15:50:00');    	
	
		    for ($weekday=1; $weekday<=6; $weekday++ )	{
		    	$timelesson->weekdaynum = $weekday;
				foreach ($arrstart as $key => $value)	{
					$timelesson->lessonnum = $key+1;
					$timelesson->timestart = $arrstart[$key];
					$timelesson->timeend = $arrend[$key];
					if (!record_exists_select('monit_school_schedule_bells', "schoolid=$sid AND weekdaynum=$weekday AND smena={$timelesson->smena} AND lessonnum = {$timelesson->lessonnum} ")) { 
				       		if (!insert_record('monit_school_schedule_bells', $timelesson))	{
								error(get_string('errorinaddingshedulebells','block_mou_school'), $redirlink);
					  		}
					}	  	
				}
		    }
		}    
		
		if (isset($recs->savepoints))	{
	   		// echo $wid . '<br>';
			// print_r($recs); echo '<hr>';
			$timelessons = array();
			foreach($recs as $fieldname => $value)	{
			    $mask = substr($fieldname, 0, 2);
			    switch ($mask)  {
					case 's_': 	$ids = explode('_', $fieldname);
			            		$timelessons[$ids[1]]->smena = $value;
	  				break;
					case 'l_': 	$ids = explode('_', $fieldname);
			            		$timelessons[$ids[1]]->lessonnum = $value;
	  				break;
					case 'h_': 	$ids = explode('_', $fieldname);
			            		$timelessons[$ids[1]]->shour = $value;
	  				break;
					case 'm_': 	$ids = explode('_', $fieldname);
			            		$timelessons[$ids[1]]->smin = $value;
	  				break;
					case 'e_': 	$ids = explode('_', $fieldname);
			            		$timelessons[$ids[1]]->ehour = $value;
	  				break;
					case 'n_': 	$ids = explode('_', $fieldname);
			            		$timelessons[$ids[1]]->emin = $value;
	  				break;
	  			}
	  		}	
			foreach ($timelessons as $key => $timelesson)  {
				$timelesson->timestart = $timelesson->shour . ':' . $timelesson->smin . ':00';
				$timelesson->timeend = $timelesson->ehour . ':' . $timelesson->emin . ':00';
				// echo $key . '<br>';
				
				if ($key > 0) {
					$timelesson->id = $key;
                    // print_r($timelesson); echo '<hr>';
					//if (record_exists_select('monit_school_schedule_bells', "schoolid=$sid AND smena={$timelesson->smena} AND lessonnum = {$timelesson->lessonnum} AND weekdaynum = $wid")) {
					 if ($timelesson->lessonnum != 0 && $timelesson->ehour != 0 && $timelesson->shour != 0)  {  
				        if (!update_record('monit_school_schedule_bells', $timelesson))	{
							error(get_string('errorinupdatingroom','block_mou_school'), $redirlink);
					    }
                     }   
					//}    
	        	} else {
	        		if ($timelesson->lessonnum != 0 && $timelesson->ehour != 0 && $timelesson->shour != 0)  {
	        			
	        			$strsql = "SELECT id 
								   FROM {$CFG->prefix}monit_school_schedule_bells
								   WHERE schoolid=$sid AND smena={$timelesson->smena} AND
								   		lessonnum = {$timelesson->lessonnum} AND weekdaynum = $wid";
						if (!get_record_sql($strsql))	{				    
		        			$timelesson->schoolid = $sid;
		        			$timelesson->yearid = $yid;
		        			$timelesson->weekdaynum = $wid;
		        			// print_r($timelesson); echo '<hr>';
				       		if (!insert_record('monit_school_schedule_bells', $timelesson))	{
								error(get_string('errorinaddingshedulebells','block_mou_school'), $redirlink);
					  		}
					  	}	
				  	}	
	        	}
			}
		}	
	}

    $currenttab = 'createschedule';
    include('tab_act.php');

    $currenttab = 'timelessons';
    include('tab_create.php');

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_weekday("timelessons.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;wid=", $sid, $yid, $wid);
	echo '</table>';

	if ($wid != 0)	{
	    echo  '<form name="timelessons" method="post" action="timelessons.php">';
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		echo  '<input type="hidden" name="wid" value="' .  $wid . '">';
		$table = table_timelessons ($yid, $rid, $sid, $wid);
		print_color_table($table);
		echo  '<div align="center">';
		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"><hr>';
		echo  '<input type="submit" name="setexample" value="'. get_string('autocreatexample', 'block_mou_school') . '"><hr>';
		//	echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"><hr>';
		//	echo  '<input type="submit" name="setexample" value="'. get_string('autocreateshedule', 'block_mou_school') . '"><hr>';

		//if ($strcurdate <= $strdate){
		echo  '<input type="submit" name="nextday" value="'. get_string('copytimelesnextday', 'block_mou_school') . '">';
		
		echo  '</form>';
	}
	
    print_footer();



function table_timelessons ($yid, $rid, $sid, $wid)
{
	global $CFG;


	$table->head  = array (get_string('smena','block_mou_school'), get_string("lessonnum","block_mou_school"),
						   get_string('eventstarttime','calendar'), get_string('eventendtime','calendar'),
						   get_string("action","block_mou_school"));
	$table->align = array ('center', 'center', 'center', 'center', 'center');
    $table->size = array ('10%', '10%', '20%', '20%', '10%');
	$table->columnwidth = array (7, 20, 20, 9, 9);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '60%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('timelessons', 'block_mou_school');
    $table->worksheetname = 'timelessons';

	$smenaoptions = array();
    for ($i=1; $i<=3; $i++) {
        $smenaoptions[$i] = $i;
    }
    
	$lessonoptions = array();
    for ($i=0; $i<=14; $i++) {
        $lessonoptions[$i] = $i;
    }

	$timelessons = get_records_select('monit_school_schedule_bells', "schoolid = $sid AND weekdaynum = $wid", 'smena, lessonnum, timestart');
	if ($timelessons)	{
		foreach ($timelessons as $timelesson) 	{
			$tabledata = array();
			$smena = 0;
			if (isset($timelesson->smena) && !empty($timelesson->smena)) 	{
				$smena = $timelesson->smena;
			}	
			$tabledata[] = choose_from_menu ($smenaoptions, 's_'.$timelesson->id, $smena, '0', "", "", true);
 
			$lessonnum = 0;
			if (isset($timelesson->lessonnum) && !empty($timelesson->lessonnum)) 	{
				$lessonnum = $timelesson->lessonnum;
			}	
        	$tabledata[] = choose_from_menu ($lessonoptions, 'l_'.$timelesson->id, $lessonnum, '0', "", "", true);
 			
			$tabledata[] = print_time_selector_mou('h_'.$timelesson->id, 'm_'.$timelesson->id, $timelesson->timestart, 5, true);
			$tabledata[] = print_time_selector_mou('e_'.$timelesson->id, 'n_'.$timelesson->id, $timelesson->timeend, 5, true);
			
			$title = get_string('deltimelesson','block_mou_school');
		    $strlinkupdate = "<a title=\"$title\" href=\"timelessons.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;wid=$wid&amp;tid={$timelesson->id}&action=del\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			$tabledata[] = $strlinkupdate;
			
			$table->data[] = $tabledata;
		}
    }
   
	$tabledata = array();
	$tabledata[] = choose_from_menu ($smenaoptions, 's_0', 0, '0', "", "", true);
	$tabledata[] = choose_from_menu ($lessonoptions, 'l_0', 0, '0', "", "", true);
    $tabledata[] = print_time_selector_mou('h_0', 'm_0', '00:00:00', 5, true);
	$tabledata[] = print_time_selector_mou('e_0', 'n_0', '00:00:00', 5, true);
	$table->data[] = $tabledata;

    return $table;
}


/**
 *Prints form items with the names $hour and $minute
 *
 * @param string $hour  fieldname
 * @param string ? $minute  fieldname
 * @param $intime A default time in format '00:00:00'
 * @param int $step minute spacing
 * @param boolean $return
 */
function print_time_selector_mou($hour, $minute, $intime='00:00:00', $step=5, $return=false) 
{
    $extime = explode (':', $intime);

	$currenttime = mktime($extime[0], $extime[1], $extime[2]);
	
    $currentdate = usergetdate($currenttime);
    if ($step != 1) {
        $currentdate['minutes'] = ceil($currentdate['minutes']/$step)*$step;
    }
    for ($i=0; $i<=23; $i++) {
        $hours[$i] = sprintf("%02d",$i);
    }
    for ($i=0; $i<=59; $i+=$step) {
        $minutes[$i] = sprintf("%02d",$i);
    }

    return choose_from_menu($hours,   $hour,   $currentdate['hours'],   '','','0',$return)
          .choose_from_menu($minutes, $minute, $currentdate['minutes'], '','','0',$return);
}


