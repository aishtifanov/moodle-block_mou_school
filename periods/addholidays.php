<?PHP // $Id: addholidays.php,v 1.8 2011/08/01 08:39:39 shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $rid = required_param('rid', PARAM_INT);
    $sid = required_param('sid', PARAM_INT);
    $yid = required_param('yid', PARAM_INT);
   	$hid = optional_param('hid', 0, PARAM_INT);			// Holiday id

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:edittypestudyperiod', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

    if ($mode === "new" || $mode === "add" ) {
    	$straddperiod = get_string('editholidays','block_mou_school');
    }  else 	{
		$straddperiod = get_string('editholidays','block_mou_school');
	}

	$strtitle = get_string('holidays', 'block_mou_school');

	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"{$CFG->wwwroot}/blocks/mou_school/periods/holidays.php?yid=$yid&amp;sid=$sid&amp;rid=$rid\">$strtitle</a>";
	$breadcrumbs .= "-> $straddperiod";
    print_header("$SITE->shortname: $straddperiod", $SITE->fullname, $breadcrumbs);

	$rec->schoolid = $sid;
	$rec->name = "";
	// $rec->datestart = "";
	// $rec->dateend = "";
	$redirlink = "holidays.php?yid=$yid&amp;sid=$sid&amp;rid=$rid";

	if ($mode === 'add')  {
		
		if($frm = data_submitted())		{
			// print_r($frm); echo '<hr>';
			if (find_form_curr_errors($frm, $err) == 0) 	{
				$rec->name = $frm->name;
			      
			    if (isset($frm->day) && $frm->day!=0 &&  $frm->month !=0 && $frm->year !=0)  {
				   	$rec->datestart = $frm->year . '-' . $frm->month . '-' . $frm->day;   
			    }	
			  		
			    if (isset($frm->day2) && $frm->day2!=0 &&  $frm->month2 !=0 && $frm->year2 !=0)  {
			  		$rec->dateend = $frm->year2 . '-' . $frm->month2 . '-' . $frm->day2;
			    }	
			  	$rec->termtypeid = $frm->termtype;
			  
			 	$plists = '';
			  	foreach($frm as $fieldname => $value)	{
					$mask = substr($fieldname, 0, 4);
		            if ($mask == 'fld_')	{
		            	$ids = explode('_', $fieldname);
	            		$plists .= $ids[1] . ',';
		            }		  	  	
		  	    }
		  	    if ($plists != '')	$plists .= '0';
			  	$rec->parallelnum = $plists;
			  	  
				// print_r($rec); echo '<hr>';
				
				if (insert_record('monit_school_holidays', $rec))	{
					 // add_to_log(1, 'school', 'one curriculum added', $redirlink, $USER->lastname.' '.$USER->firstname);
					 // notice(get_string('succesavedata','block_mou_school'), $redirlink);
					 redirect($redirlink, get_string('succesavedata','block_mou_school'), 0);
				} else {
					error(get_string('errorinaddingcurr','block_mou_school'), $redirlink);
				}
				
			} else $mode = "new";		
		} else $mode = "new";
	}
	else if ($mode === 'edit')	{
		if ($hid > 0) 	{
			$curr = get_record('monit_school_holidays', 'id', $hid);
			$rec->id = $curr->id;
			$rec->datestart = $curr->datestart;
			$rec->name = $curr->name;
			$rec->dateend = $curr->dateend;
		}
	}
	else if ($mode === 'update')	{
		
		if($frm = data_submitted())		{
			// print_r($frm); echo '<hr>';
			$rec->id   = $hid;
			$rec->name = $frm->name;
		      
		    if (isset($frm->day) && $frm->day!=0 &&  $frm->month !=0 && $frm->year !=0)  {
			   	$rec->datestart = $frm->year . '-' . $frm->month . '-' . $frm->day;   
			 	//$rc->datestart = get_timestamp_from_date($frm->day, $frm->month, $frm->year);
			 	// $convert = convert_date($rc->datestart, 'ru', 'en');
		    }	
		  		
		    if (isset($frm->day2) && $frm->day2!=0 &&  $frm->month2 !=0 && $frm->year2 !=0)  {
		  		$rec->dateend = $frm->year2 . '-' . $frm->month2 . '-' . $frm->day2;
		    }	
		  	$rec->termtypeid = $frm->termtype;
		  
		 	$plists = '';
		  	foreach($frm as $fieldname => $value)	{
				$mask = substr($fieldname, 0, 4);
	            if ($mask == 'fld_')	{
	            	$ids = explode('_', $fieldname);
            		$plists .= $ids[1] . ',';
	            }		  	  	
	  	    }
	  	    if ($plists != '')	$plists .= '0';
		  	$rec->parallelnum = $plists;
		  	  
			// print_r($rec); echo '<hr>';
			  
			if (update_record('monit_school_holidays', $rec))	{
				// add_to_log(1, 'school', 'curriculum update', $redirlink, $USER->lastname.' '.$USER->firstname);
				 // notice(get_string('succesavedata','block_mou_school'), $redirlink);
				 redirect($redirlink, get_string('succesavedata','block_mou_school'), 0);
			} else	{
				error(get_string('error'), $redirlink);
			}
				
		}
	}

	print_heading($straddperiod, "center", 3);

    print_simple_box_start("center", '40%');

	if ($mode === 'new')  $newmode='add';
	else   				  $newmode='update'; //  if ($mode === 'edit') 

	?>
	
	<form name="addform" method="post" action="addholidays.php">
	<center>
	<table cellpadding="5">
	<tr valign="top">
	    <td align="right"><b><?php  print_string("name") ?>:</b></td>
	    <td align="left">
			<input name="name" type="text" id="name" value="<?php p($rec->name) ?>" size="50" />
			<?php if (isset($err["name"])) formerr($err["name"]); ?>
	    </td>
	</tr>
	
	<?php

	 if (isset($rec->datestart))	{
	 	 $date_term = convert_date($rec->datestart, 'en', 'ru');
	 	 
	 	 $explode = explode('-', $rec->datestart);
	 	 $rc->year = $explode[0];
		 $rc->month = $explode[1];
		 $rc->day = $explode[2];
		 if($rc->year && $rc->month && $rc->day){
			$rc->g = get_timestamp_from_date($rc->day, $rc->month, $rc->year);
		 }
	 }	 	 	

	 if (isset($rec->dateend))	{
	 	 $date_term2 = convert_date($rec->dateend, 'en', 'ru');
	 	 $explode2 = explode('-', $rec->dateend);
		 $rc2->year = $explode2[0];
		 $rc2->month = $explode2[1];
		 $rc2->day = $explode2[2];
		 if($rc2->year && $rc2->month && $rc2->day){
			$rc2->g = get_timestamp_from_date($rc2->day, $rc2->month, $rc2->year);
		 }
	 }	 

	 

	 
	 echo '<tr valign="top"><td align="right"><b>';
     print_string('timestart', 'block_mou_school');
     echo ':</b></td> <td align="left">';

	 if(isset($rc->g)){
		print_date_monitoring("day", "month", "year", $rc->g, 1);
	 }else{
		print_date_monitoring("day", "month", "year", 0, 1);
	 }	 
	
     echo '</td> </tr>';
	 echo '<tr valign="top"><td align="right"><b>';
     print_string('timeend', 'block_mou_school');
     echo ':</b></td> <td align="left">';
	      
	 if(isset($rc2->g)){
		print_date_monitoring("day2", "month2", "year2", $rc2->g, 1);
	 }else{
		print_date_monitoring("day2", "month2", "year2", 0, 1);
	 }	 
	 echo '</td></tr>';
	 
	 $term_types = get_records('monit_school_term_type');
	 $holiday = get_record('monit_school_holidays','id', $hid);
	 $type = get_record('monit_school_term_type', 'id', $holiday->termtypeid);
	 
	 echo '<tr valign="top"><td align="right"><b>';
	 print_string('typestudyperiod1', 'block_mou_school');
	 echo ':</b></td> <td align="left">';
	 
	 print_simple_box_start('','','white');
	 
	 foreach($term_types as $term_type){
	 	$check = '';
	 	if($holiday->termtypeid == $term_type->id){
	 		$check = 'checked';
			 echo "<input type=radio $check name='termtype' value={$term_type->id}>".' '.$term_type->name.'<br>';
	 	}else{
	 		 echo "<input type=radio $check name='termtype' value={$term_type->id}>".' '.$term_type->name.'<br>';
	 	}

	 }
	 print_simple_box_end();	 
	 echo '</td></tr>';

	 echo '<tr valign="top"><td align="right"><b>';
	 print_string('parallels', 'block_mou_school');
	 echo ':</b></td> <td align="left">';
	 print_simple_box_start('','','white');
	 $explode = explode(',', $holiday->parallelnum);
		
	 $list_of_parallels = array();		
	 foreach($explode as $ids){
		 $list_of_parallels[] = $ids;		 		
	 }	
	 
	 for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
 		if (in_array($p, $list_of_parallels)) 	{
			$is = '+';
	  	    if ($existholiday = get_record_sql("SELECT DISTINCT parallelnum FROM {$CFG->prefix}monit_school_holidays WHERE id=$hid"))	{
				echo "<input type=checkbox checked size=1 name=fld_{$p} value='$is'>".' '.$p. '<br>';
			} else {
				echo "<input type=checkbox size=1 name=fld_{$p} value='$is'>".' '.$p. '<br>';
			}		
		} else {
			$is = '-';
			echo "<input type=checkbox size=1 name=fld_{$p} value='$is'>".' '.$p. '<br>';			
		}
	}		

	 print_simple_box_end();	 
	 echo '</td></tr></table>';

?>
   <div align="center">
     <input type="hidden" name="mode" value="<?php echo $newmode ?>">
     <input type="hidden" name="yid" value="<?php echo $yid ?>">
     <input type="hidden" name="rid" value="<?php echo $rid ?>">
     <input type="hidden" name="sid" value="<?php echo $sid ?>">
     <input type="hidden" name="hid" value="<?php echo $hid ?>">
 	 <input type="submit" name="adddisc" value="<?php print_string('savechanges')?>">
  </div>
 </center>
</form>


<?php
    print_simple_box_end();

	print_footer();


/// FUNCTIONS ////////////////////
function find_form_curr_errors(&$frm, &$err)
{
    if (empty($frm->name))		{
	    $err["name"] = get_string("missingname");
	} 

    if ($frm->day == 0 ||  $frm->month == 0 || $frm->year == 0)  {
		notify(get_string('timestart', 'block_mou_school') . '???');
		$err["day"] = 1;   
    }	
		  		
    if ($frm->day2 == 0 ||  $frm->month2 == 0 || $frm->year2 == 0)  {
		notify(get_string('timeend', 'block_mou_school') . '???');
		$err["day2"] = 1;   
    }	
    
    if (empty($frm->termtype))	{
		notify(get_string('selectaperiod', 'block_mou_school') . '!!!');
		$err["termtypeid"] = 1;   
    }

/*
    if (empty($frm->parallelnum))	{
		notify(get_string('selectaparallelnum', 'block_mou_school') . '!!!');
		$err["parallelnum"] = 1;   
    }
*/	 
    return count($err);
}


?>