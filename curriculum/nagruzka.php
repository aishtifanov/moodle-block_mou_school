<?php // $Id: nagruzka.php,v 1.20 2011/12/13 12:05:32 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

	if ($recs = data_submitted())  {
		// print_r($recs); echo '<hr>';

		if (!has_capability('block/mou_school:editprofiles', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	

        $redirlink = "nagruzka.php?rid=$rid&amp;sid=$sid&amp;yid=$yid";
        
        if (isset($recs->setdefaults))	{
   			$strcomponents = get_string('componentlist','block_mou_school');
			$arrcmpnts = explode(';', $strcomponents);

			$components = get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_component
										    WHERE schoolid=$sid ORDER BY id");
							    
			if ($components)	{
				foreach ($arrcmpnts as $key => $arrcmpnt) {
		        	foreach ($components as $component)  {
   						if ($component->name === $arrcmpnt)	{
							$strlimits = get_string('limitnagruzkahour'.$key, 'block_mou_school');
							$arrlimits = explode(',', $strlimits);
						    for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
								if (!record_exists('monit_school_curriculum_totals', 'componentid', $component->id,
													 'parallelnum', $p, 'schoolid', $sid))	{
				            		$newrec->schoolid = $sid;
				            		$newrec->parallelnum = $p;
				            		$newrec->componentid = $component->id;
				            		$newrec->yearid = $yid;
				            		$newrec->hourstotal = $arrlimits[$p-1];
							        if (!insert_record('monit_school_curriculum_totals', $newrec))	{
										error(get_string('errorinaddingpp','block_mou_ege'), $redirlink);
								    }
								}   
							}    
						}		
					}
				}
			}	
        } else {
			foreach($recs as $name => $hourstotal)	{
				if ($hourstotal != '')	{
		            $mask = substr($name, 0, 4);
		            if ($mask == 'num_')	{
		            	$ids = explode('_', $name);
		            	$compid = $ids[1];
		            	$parallelid = $ids[2];
		            	if (record_exists('monit_school_curriculum_totals', 'componentid', $compid, 'parallelnum', $parallelid, 'schoolid', $sid))	{
		            			//delete_records('monit_school_curriculum_totals', 'componentid', $compid, 'parallelnum', $parallelid);
		            			set_field('monit_school_curriculum_totals', 'hourstotal', $hourstotal, 'componentid', $compid, 'parallelnum', $parallelid, 'schoolid', $sid);
		            	} else {
		            		$newrec->schoolid = $sid;
		            		$newrec->parallelnum = $parallelid;
		            		$newrec->componentid = $compid;
		            		$newrec->yearid = $yid;
		            		$newrec->hourstotal = $hourstotal;
					       if (!insert_record('monit_school_curriculum_totals', $newrec))	{
								error(get_string('errorinaddingpp','block_mou_ege'), $redirlink);
						   }
		            	}
		            }
		        }
			}
		}	
        // notice(get_string('succesavedata','block_monitoring'), $redirlink);
		redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
	}


	$currenttab = 'nagruzka';
    include('tabsup.php');


    $view_capability = has_capability('block/mou_school:viewprofiles', $context);
    $edit_capability = has_capability('block/mou_school:editprofiles', $context);

	if ($view_capability)	{
		echo  '<form name="nagruzka" method="post" action="nagruzka.php">';
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
	   	$table = table_nagruzka ($yid, $rid, $sid);
		print_color_table($table);
        if ($edit_capability)	{
    		echo  '<div align="center">';
    		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
    		echo  '<input type="submit" name="setdefaults" value="'. get_string('autocreatexample', 'block_mou_school') . '"></div>';
    		echo  '</form>';
        }  else {
            echo  '</form>';
        }  

        echo '<hr>';        
		print_simple_box_start_old('center','', 'white');
		notify('<i>'. get_string('notifynagruzka','block_mou_school').'</i>', '');
		print_simple_box_end_old();
	}	
	
    print_footer();


function table_nagruzka ($yid, $rid, $sid)
{
	global $CFG, $admin_is, $region_operator_is;

    $table->dblhead->head1  = array (get_string ('components','block_mou_school'), get_string ('hoursinparallel','block_mou_school'));
    $table->dblhead->span1  = array ("rowspan=2", "colspan={$CFG->maxparallelnumber}");
	$table->align = array ('left', 'center');
	$table->columnwidth = array (20);
	$table->dblhead->size = array ('25%');
	$table->wraphead = 'nowrap';

    for ($i = 1; $i <= $CFG->maxparallelnumber; $i++)	{
	    $table->dblhead->head2[]  = $i;
		$table->align[] = 'center';
		$table->size[] = '5%';
		$table->columnwidth[] = 10;
    }
    
    $table->class = 'moutable';
   	$table->width = '95%';

	$table->titles = array();
    $table->titles[] = get_string('nagruzka','block_mou_school');
    $table->worksheetname = $yid;

	$tabledata = array ('<b><i>'.get_string('limitnagruzka','block_mou_school').'</b></i>');
	if (record_exists('monit_school_curriculum_totals', 'componentid', 0, 'schoolid', $sid))	{
	
    for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
		    if ($rec = get_record_sql("SELECT  hourstotal FROM {$CFG->prefix}monit_school_curriculum_totals
							WHERE componentid = 0 and schoolid=$sid AND parallelnum = $p"))	{
				$is = $rec->hourstotal;
			} else {
				$is = '-';
			}
			$tabledata[] = "<input type=text name=num_0_$p size=1 MAXLENGTH=2 value=$is>";
		}
	} else {
		$strlimits = get_string('limitnagruzkahour','block_mou_school');
		$arrlimits = explode(',', $strlimits);
		$p = 1;
		foreach ($arrlimits as $arrlimit)	{
			$tabledata[] = "<input type=text name=num_0_$p size=1 MAXLENGTH=2 value=$arrlimit>";
			$p++;
		}
	}
	$table->data[] = $tabledata;

	$components = get_records_sql ("SELECT *  FROM {$CFG->prefix}monit_school_component
								    WHERE schoolid=$sid ORDER BY id");
								    
	$itog = array(0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
	if ($components)	{
        
        foreach ($components as $component) {

   			$tabledata = array ($component->name);

			for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{

			    if ($i = get_record_sql("SELECT  hourstotal FROM {$CFG->prefix}monit_school_curriculum_totals
								WHERE componentid = {$component->id} and schoolid=$sid AND parallelnum = $p"))	{
					$is = $i->hourstotal;
					$itog[$p] += $is;
				} else {
					$is = '-';
				}
				$tabledata[] = "<input type=text name=num_{$component->id}_$p size=1 MAXLENGTH=2 value=$is>";
			}

			$table->data[] = $tabledata;
        }
    }

	$tabledata = array ('<b><i>'.get_string('allhoursweek','block_mou_school').'</b></i>');
	for ($p = 1; $p <= $CFG->maxparallelnumber; $p++)	{
		if ($rec = get_record_sql("SELECT  hourstotal FROM {$CFG->prefix}monit_school_curriculum_totals
							WHERE componentid = 0 and schoolid=$sid AND parallelnum = $p")){
			if ($itog[$p]>$rec->hourstotal){
				$tabledata[] = "<font color='#CC0000'>". "<b>". $itog[$p]. "</b>";	
			}else{
				$tabledata[] = "<font color='#336699'>". "<b>". $itog[$p]. "</b>";
			}
		}
           
	}
	$table->data[] = $tabledata;

    return $table;
}

?>