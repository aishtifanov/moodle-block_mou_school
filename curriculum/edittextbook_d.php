<?php // $Id: edittextbook_d.php,v 1.6 2010/08/23 08:48:04 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('../lib_school.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);          // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
    $did = required_param('did', PARAM_INT);       // Discipline id
	$catid  = optional_param('catid', '0', PARAM_INT);       // Category textbook

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editdiscipline', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	$discipline = get_record('monit_school_discipline', 'id', $did);
	
   	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';
	$strtitle = get_string('textbooks_d','block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/textbooks_d.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
   	$strtitle = get_string('editdisciplinetextbook','block_mou_school', $discipline->name);
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	echo '<table cellspacing="0" cellpadding="10" align="center" class="generaltable generalbox">';
	listbox_discipline_school("edittextbook_d.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;did=", $sid, $yid, $did);	
	listbox_textbook("edittextbook_d.php?rid=$rid&amp;yid=$yid&amp;sid=$sid&amp;did=$did&amp;catid=", $yid, $catid);
	echo '</table>';

    if ($schooltextbooks = data_submitted()) 		{
    		$redirlink = "textbooks_d.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";

			if ($textbooks =  get_records('monit_textbook'))	{

				$textbooksids = '';
				foreach ($textbooks as $textbook) 	{
					$pf = 'textbook_'.$textbook->id;
					if (isset($schooltextbooks->{$pf}) && $schooltextbooks->{$pf} == 1)  {
						$textbooksids .= $textbook->id . ',';
					}
				}
				$textbooksids .= '0';

		        if ($schtextbook =  get_record('monit_school_textbook',  'yearid', $yid , 'schoolid', $sid, 'discegeid', $did))  {
		        	$schtextbook->textbooksids .= $textbooksids;
		        	$schtextbook->timemodified = time();
	                if (update_record('monit_school_textbook', $schtextbook))	{
	                   	redirect($redirlink, get_string("changessaved"), 0);
	                }	else {
	                    print_r($schtextbook);
		                error("Could not update the school textbook record.");
	                }
	            } else {
	            	$rec->yearid	= $yid;
	            	$rec->schoolid	= $sid;
	            	$rec->disciplineid = $did;
	            	$rec->textbooksids = $textbooksids;
	            	$rec->timemodified = time();
	                if (insert_record('monit_school_textbook', $rec))	{
	                   	redirect($redirlink, get_string("changessaved"), 0);
	                }	else {
	                    print_r($schtextbook);
		                error("Could not insert the new school textbook record.");
	                }
	            }
			}
    }


    if ($rid != 0 && $sid != 0 && $did != 0 && $catid != 0)  {


	    print_simple_box_start("center", '80%', 'white');
		?>
		
		<table class="formtable">
		<form method="post" name="form" enctype="multipart/form-data" action="edittextbook_d.php">
		<input type="hidden" name="rid" value="<?php echo $rid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="yid" value="<?php echo $yid ?>" />
		<input type="hidden" name="did" value="<?php echo $did ?>" />
		
		
		<?php


		if ($textbooks =  get_records('monit_textbook',  'categoryid', $catid , 'authors'))	 {
		    $i = 0;
		    $strtextbooks = get_string('textbooks', 'block_mou_ege');
			echo "<tr><th>$strtextbooks:</th>";
			echo "<td>";

			$arr_egeids = array();
	        if ($schooltextbooks =  get_record('monit_school_textbook',  'yearid', $yid , 'schoolid', $sid, 'discegeid', $did))  {
			    $arr_egeids = explode(',', $schooltextbooks->textbooksids);
			}

			$strlowclass = get_string('lowclass', 'block_mou_ege');

			foreach ($textbooks as $textbook) {
				$name = 'textbook_'.$textbook->id;

				if (in_array($textbook->id, $arr_egeids))	{
					echo "<input name=$name type=checkbox checked=checked value=1>";
				} else {
					echo "<input name=$name type=checkbox value=1>";
				}
				// ++$i.'. ' .
				echo  ' '.$textbook->authors .' '. $textbook->name .'. - '. $textbook->publisher . ' (' . $textbook->numclass . ' '. $strlowclass . ')<br>';
			}
			echo "</td>";
		}


		echo '<tr><td colspan="2"><hr /></td></tr>';
		echo '<tr align=center><td align=right><input type="submit" value="' . get_string("savechanges") . '" /></td>';
  		echo '</form><td align=left>';
		/*
   		$options = array();
	    $options['rid'] = $rid;
	    $options['sid'] = $sid;
	    $options['yid'] = $yid;
	    print_single_button("gia_teachers.php", $options, get_string("revert"));
		*/
		?>
		
		<form method="post" name="form2" enctype="multipart/form-data" action="gia_textbooks.php">
		<input type="hidden" name="rid" value="<?php echo $rid ?>" />
		<input type="hidden" name="sid" value="<?php echo $sid ?>" />
		<input type="hidden" name="yid" value="<?php echo $yid ?>" />
		<input type="hidden" name="did" value="<?php echo $did ?>" />
		<input type="submit" value="<?php print_string("revert")?>" />
		</form>
		
		<?php
  		echo '</td></tr></table>';

	   	print_simple_box_end();
    }
    print_footer();


?>


