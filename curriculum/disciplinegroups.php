<?php // $Id: disciplinegroups.php,v 1.8 2010/08/25 08:36:25 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

	$currenttab = 'disciplinegroups';
    include('tabsdis.php');

/*
    if ($recs = data_submitted())  {
    	
		if (!has_capability('block/mou_school:editdiscipline', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	
    	
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&amp;yid=$yid");
        $redirlink = "disciplinegroups.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
        if (isset($recs->setdefaults))	{
        	$standartvalues = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_datadir_dgroup");
        	foreach ($standartvalues as $standartvalue)	{ 
            	if (!record_exists('monit_school_discipline_group', 'name', $standartvalue->dgroupname, 'schoolid', $sid))	{
            		$newrec->schoolid = $sid;
            		$newrec->name = $standartvalue->dgroupname;
            		$newrec->disciplinedomainid = $standartvalue->domdatadirid;
			        if (!insert_record('monit_school_discipline_group', $newrec))	{
						error(get_string('errorinaddingdisgroup','block_mou_school'), $redirlink);
				    }
            	}
            }
	        notice(get_string('succeinsertdata','block_mou_school'), $redirlink);
         } else {
			foreach($recs as $fieldname => $dgname)	{

				if ($dgname != '')	{
		            $mask = substr($fieldname, 0, 4);
		            if ($mask == 'num_')	{
		            	$ids = explode('_', $fieldname);
		            	$dgroupid = $ids[1];

		            	if (record_exists('monit_school_discipline_group', 'id', $dgroupid, 'schoolid', $sid))	{
		           			set_field('monit_school_discipline_group', 'name', $dgname, 'id', $dgroupid, 'schoolid', $sid);
		            	} else {
		            		$newrec->schoolid = $sid;
		            		$newrec->name = $dgname;
					       if (!insert_record('monit_school_discipline_group', $newrec))	{
								error(get_string('errorinaddingdisgroup','block_mou_school'), $redirlink);
						   }

		            	}
		            }

		        }
			}
	        notice(get_string('succesavedata','block_mou_school'), $redirlink);
			// redirect($redirlink, get_string('succesavedata','block_monitoring'), 0);
		}
	}

	if (has_capability('block/mou_school:editdiscipline', $context))	{
	    echo  '<form name="disciplinegroupform" method="post" action="disciplinegroups.php">';
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		$table = table_disciplinegroups ($yid, $rid, $sid);
		print_color_table($table);
		echo  '<div align="center">';
		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
		echo  '<input type="submit" name="setdefaults" value="'. get_string('setstandartvalue', 'block_mou_school') . '"></div>';
		echo  '</form>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
	
*/
	echo "<center><a href=\"$CFG->wwwroot/file.php/1/create_subgroup.pdf\"> Как создать подгруппы классов при делении класса по предметам и по элективным курсам. </a><p></center>";
	
	notice('&nbsp;&nbsp;&nbsp;&nbsp;Рекомендуется использовать деление предмета школы на подгруппы, а не группировать несколько дисциплин в один предмет. <br><br>&nbsp;&nbsp;&nbsp;&nbsp;Например, вместо создания двух предметов "Английский язык" и "Немецкий язык" и их последующего группирования лучше создать один предмет "Иностранный язык" и задать для него две подгруппы: "английскую" и "немецкую".  ');
	
	
    print_footer();


function table_disciplinegroups ($yid, $rid, $sid)
{
	global $CFG;

	$table->head  = array (	get_string('disciplinegroup', 'block_mou_school'),
							get_string('disciplines', 'block_mou_school'),
							get_string('action', 'block_mou_school'));
    $table->align = array ("left", "left", "center");
    $table->class = 'moutable';
  	$table->width = '60%';
    $table->size = array ('30%', '40%', '5%');

	$disgroups = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_discipline_group
								 WHERE schoolid=$sid ORDER BY name");

	if ($disgroups)	{

			foreach ($disgroups as $dg) {

				$strdiscipline = $dg->name;
				$insidetable = "<input type=text  name=num_{$dg->id} size=30 value=\"$strdiscipline\">";

 				if ($discipline = get_records_sql("SELECT id, name, shortname FROM {$CFG->prefix}monit_school_discipline
               									  WHERE schoolid=$sid AND dgroupid = {$dg->id}
               									  ORDER by name"))  {
                   $strdisc = '';
	               foreach ($discipline as $disc) {
       		           	$strdisc .= '* ' . $disc->name . '<br>';
			       }
	            } else {
	               	$strdisc = '-';
	            }



				$title = get_string('editdisciplinegroup','block_mou_school');
				$strlinkupdate = "<a title=\"$title\" href=\"editdiscipgroup.php?sid=$sid&amp;rid=$rid&amp;yid=$yid&amp;dgid={$dg->id}\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/edit.gif\" alt=\"$title\" /></a>&nbsp;";

				$title = get_string('deletedisgroup','block_mou_school');
			    $strlinkupdate .= "<a title=\"$title\" href=\"delcurriculum.php?part=dg&amp;sid=$sid&amp;id={$dg->id}&amp;rid=$rid&amp;yid=$yid\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

				$table->data[] = array ($insidetable, $strdisc, $strlinkupdate);
			}
	}

    $insidetable = "<input type=text  name=num_0 size=30 value=''>";
	$table->data[] = array ($insidetable, '');

    return $table;
}
?>
