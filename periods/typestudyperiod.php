<?php // $Id: typestudyperiod.php,v 1.11 2010/09/03 11:50:22 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');
	
	$currenttab = 'typestudyperiod';
    include('tabsup.php');

	/// A form was submitted so process the input
	if ($recs = data_submitted())  {
		if (!has_capability('block/mou_school:edittypestudyperiod', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	
		// print_r($recs); echo '<hr>';
		// exit ();
        $fields = array();
        $termunique = array();
		foreach($recs as $name => $termtypeid)	{
			if ($termtypeid != '')	{
	            $mask = substr($name, 0, 5);
	            if ($mask == 'term_')	{
   				    $fields[] = $name;
	            	$ids = explode('_', $name);
	            	$parallelnum = $ids[1];
	            	// $termtypeid = $ids[2];
	            	$termunique[] = $termtypeid;
	            	
	            	// print_r($ids); echo '<hr>';

	            	if (record_exists('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $parallelnum))	{
	            		if (!set_field('monit_school_class_termtype', 'termtypeid', $termtypeid, 'schoolid', $sid, 'parallelnum', $parallelnum)) {
	            			error(get_string('errorinaddintermtype','block_mou_school'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
	            		}
	            	} else {
	            		$newrec->schoolid = $sid;
	            		$newrec->parallelnum = $parallelnum;
	      	     		$newrec->termtypeid = $termtypeid;
				        if (!insert_record('monit_school_class_termtype', $newrec))	{
							error(get_string('errorinaddintermtype','block_mou_school'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
					   }
	            	}
	            }
	        }
		}

		// delete
		
		$spns = get_records('monit_school_class_termtype');
		foreach($spns as $spn)	{
			$name = 'term_' . $spn->parallelnum; // . '_' . $spn->termtypeid;
			if (!in_array($name, $fields))	{
				delete_records('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $spn->parallelnum, 'termtypeid', $spn->termtypeid);
				// delete_records('monit_school_term', 'id', $spn->termtypeid);
				// delete_records('monit_school_point_forschool', 'pointnumber2id', $spn->id);
			}
		}

		
		// $unquiterms = array_unique($termunique);
		$unquiterms = get_records('monit_school_term_type');
		foreach ($unquiterms as $unquiterm1)	{
			$unquiterm = $unquiterm1->id;
			// echo $strparallelnum;
			if (!record_exists('monit_school_term', 'schoolid', $sid, 'termtypeid', $unquiterm))	{
				if ($period = get_record('monit_school_term_type', 'id', $unquiterm))	{
				    $suffix = get_suffix ($period->countsterm);
				    $studyperiods = explode(';', $period->periods);
					for ($j=1, $k=0; $j<=$period->countsterm; $j++)	{
	            		$newrec->yearid = $yid;
	            		$newrec->schoolid = $sid;
	            		$newrec->termtypeid = $unquiterm;
	      	     		$newrec->name = $j . $suffix . ' ' . $period->name;
	      	     		$newrec->datestart = $studyperiods[$k++];
	      	     		$newrec->dateend = $studyperiods[$k++];
				        if (!insert_record('monit_school_term', $newrec))	{
							error(get_string('errorinaddingperiod','block_mou_school'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
					    }
					}
				}	
			}	
			
			$strparallelnum = '';
			if ($class_termtype = get_records_select('monit_school_class_termtype', "schoolid = $sid AND termtypeid = $unquiterm"))	{
				foreach($class_termtype as $ct)	{
					$strparallelnum .= $ct->parallelnum . ',';
				}
				$strparallelnum .= '0';
			}
				
					
			if ($period = get_record('monit_school_term_type', 'id', $unquiterm))	{
				// print_r($period); echo '<hr>';
				$holidayperiods = explode(';', $period->holidays);
				for ($j=1, $k=0; $j<=$period->countsterm-1; $j++, $k+=3)	{
					$datestart=$holidayperiods[$k+1];
					$dateend  =$holidayperiods[$k+2];
					$strsql = "schoolid=$sid AND datestart='$datestart' AND dateend='$dateend' AND termtypeid = $unquiterm";
					if (record_exists_select('monit_school_holidays',  $strsql))  {
						$holiday = get_record_select('monit_school_holidays',  $strsql);
						// $holiday->parallelnum = $strparallelnum;
			       		if (!set_field('monit_school_holidays', 'parallelnum', $strparallelnum, 'id', $holiday->id))	{
							error(get_string('errorinaddingperiod','block_mou_school'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
						}
						
						// echo 'UPDATE' . $strsql . '<br>';
						// $strsql = " AND parallelnum = '$strparallelnum'";
					} else {	
						// echo 'INSERT' . $strsql . '<br>'; 
						
		           		$newrec->schoolid = $sid;
            			$newrec->termtypeid = $unquiterm;			           		
					    $newrec->name = $holidayperiods[$k];
		     			$newrec->datestart = $holidayperiods[$k+1];
			     		$newrec->dateend = $holidayperiods[$k+2];
     					$newrec->parallelnum = $strparallelnum;
			       		if (!insert_record('monit_school_holidays', $newrec))	{
							error(get_string('errorinaddingperiod','block_mou_school'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
						}
					}	
				}	
			}
		}	
        notice(get_string('succesavedata','block_monitoring'), "typestudyperiod.php?rid=$rid&amp;sid=$sid&amp;yid=$yid");
		// redirect("setpoints.php?rid=0&amp;yid=$yid", get_string('succesavedata','block_monitoring'), 0);
	}

	if (has_capability('block/mou_school:viewtypestudyperiod', $context))	{
		$table = table_studyperiod ($yid, $rid, $sid);
		echo  '<form name="points" method="post" action="typestudyperiod.php">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		print_color_table($table);
		if (has_capability('block/mou_school:edittypestudyperiod', $context))	{
			echo  '<div align="center">';
			echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
		}	
		echo  '</form>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}

    print_footer();



function table_studyperiod ($yid, $rid, $sid)
{
	global $CFG;

    // $str  = get_string ('typestudyperiod1','block_mou_school') . ' \ ' .  get_string ('parallels','block_mou_school');
    $table->dblhead->head1  = array (get_string ('typestudyperiod1','block_mou_school'), get_string ('parallels','block_mou_school'));
    $table->dblhead->span1  = array ("rowspan=2", "colspan=$CFG->maxparallelnumber");
	$table->align = array ('left', 'center');
	$table->columnwidth = array (20);

    for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{
	    $table->dblhead->head2[]  = $i;
		$table->align[] = 'center';
		$table->columnwidth[] = 10;
    }
    $table->class = 'moutable';
   	$table->width = '60%';

	$table->titles = array();
    $table->titles[] = get_string('typestudyperiod','block_mou_school');
    $table->worksheetname = $yid;

	$typeterms =  get_records ('monit_school_term_type');

    foreach ($typeterms as $typeterm)		{

		$tabledata = array ($typeterm->name);

	    for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{

			$check = '';

	    	if (record_exists('monit_school_class_termtype', 'schoolid', $sid, 'parallelnum', $i, 'termtypeid', $typeterm->id))		{
		    	$check = 'checked';
	    	}

    		// $tabledata[] = "<input type=checkbox $check name=term_{$i}_{$typeterm->id}>";
    		$tabledata[] = "<input type=radio $check name=term_{$i} value={$typeterm->id}>";
    	}
		$table->data[] = $tabledata;
	}
    return $table;
}


function get_suffix ($countsterm)
{
	$suffix = '';
	switch ($countsterm)	{
		case 1: $suffix = '-я';
		break;
		case 2: $suffix = '-е';
		break;
		case 3: $suffix = '-й';
		break;
	}

	return $suffix;
}

?>

