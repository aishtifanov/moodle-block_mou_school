<?php // $Id: components.php,v 1.19 2011/12/13 12:05:32 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');


    if ($recs = data_submitted())  {
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&amp;yid=$yid");
		if (!has_capability('block/mou_school:editprofiles', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	

		$redirlink = "components.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
        if (isset($recs->setstandart))	{
	 		if (!record_exists('monit_school_component', 'schoolid', $sid))		{
	 			 $strcomplist = get_string('componentlist','block_mou_school');
	 			 $complist = explode (';', $strcomplist);
				 foreach ($complist as $componentname)	{
    				unset($newrec);
		    		$newrec->schoolid = $sid;
	    			$newrec->name = $componentname;
			        if (!insert_record('monit_school_component', $newrec))	{
			        	print_r($newrec);
						error(get_string('errorinaddingcomponent','block_mou_school'), $redirlink);
			    	}
			    }	
	 		} else {
	 			notice(get_string('componentalreadyexists','block_mou_school'), $redirlink);	
	 		}	
		} else {
			
			
			foreach($recs as $fieldname => $compname)	{
	
				if ($compname != '')	{
		            $mask = substr($fieldname, 0, 4);
		            if ($mask == 'num_')	{
		            	$ids = explode('_', $fieldname);
		            	$compid = $ids[1];
	
		            	if (record_exists('monit_school_component', 'id', $compid, 'schoolid', $sid))	{
		           			set_field('monit_school_component', 'name', $compname, 'id', $compid, 'schoolid', $sid);
		            	} else {
		            		$newrec->schoolid = $sid;
		            		$newrec->name = $compname;
					       if (!insert_record('monit_school_component', $newrec))	{
								error(get_string('errorinaddingcomponent','block_mou_school'), $redirlink);
						   }
	
		            	}
		            }
	
		        }
			}
	        // notice(get_string('succesavedata','block_mou_school'), $redirlink);
			redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
		}	
	}


	$currenttab = 'components';
    include('tabsup.php');

//	print_heading(get_string('component', 'block_mou_school'), 'center');

    $view_capability = has_capability('block/mou_school:viewprofiles', $context);
    $edit_capability = has_capability('block/mou_school:editprofiles', $context);

	if ($view_capability)	{
	    echo  '<form name="components" method="post" action="components.php">';
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		$table = table_component ($yid, $rid, $sid);
		print_color_table($table);
        if ($edit_capability)	{
    		echo  '<div align="center">';
    		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
    		echo  '<input type="submit" name="setstandart" value="'. get_string('setstandartvalue', 'block_mou_school') . '">';	
    		echo  '</div></form>';
        }  else {
            echo  '</form>';
        }  
	}	


    print_footer();


function table_component ($yid, $rid, $sid)
{
	global $CFG;

	$table->head  = array (	get_string('name', 'block_mou_school'), get_string('action', 'block_mou_school'));
    $table->align = array ("left", "center");
    $table->class = 'moutable';
  	$table->width = '40%';
    $table->size = array ('10%', '10%');

	$component = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_component
 								   WHERE schoolid=$sid
								   ORDER BY id");

	if ($component)	{
			foreach ($component as $comp) {

				$insidetable = "<input type=text  name=num_{$comp->id} size=50 value=\"{$comp->name}\">";

				$title = get_string('deletecurriculum','block_mou_school');
			    $strlinkupdate  = "<a title=\"$title\" href=\"delcurriculum.php?part=com&amp;sid=$sid&amp;id={$comp->id}&amp;rid=$rid&amp;yid=$yid\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

				$table->data[] = array ($insidetable, $strlinkupdate);
			}
	}
    $insidetable = "<input type=text  name=num_0 size=50 value=''>";
	$table->data[] = array ($insidetable, '');

    return $table;
}

?>
