<?php // $Id: educationareas.php,v 1.1 2010/08/23 08:48:05 Shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');

 	$currenttab = 'educationareas';
    include('tabsdis.php');

    if ($recs = data_submitted())  {

		if (!has_capability('block/mou_school:editdiscipline', $context))	{
			error(get_string('permission', 'block_mou_school'), '../index.php');
		}	
    	
		 echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), "points.php?rid=0&amp;yid=$yid");
        $redirlink = "educationareas.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
        if (isset($recs->setdefaults))	{
        	$standartvalues = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_datadir_domain");
        	foreach ($standartvalues as $standartvalue)	{
            	if (!record_exists('monit_school_discipline_domain', 'name', $standartvalue->dirname, 'schoolid', $sid))	{
            		$newrec->schoolid = $sid;
            		$newrec->name = $standartvalue->dirname;
			        if (!insert_record('monit_school_discipline_domain', $newrec))	{
						error(get_string('errorinaddingeduarea','block_mou_school'), $redirlink);
				    }
            	}
            }
	        notice(get_string('succeinsertdata','block_mou_school'), $redirlink);
			// redirect($redirlink, get_string('succeinsertdata','block_monitoring'), 0);

        } else {
			foreach($recs as $fieldname => $domname)	{

				if ($domname != '')	{
		            $mask = substr($fieldname, 0, 4);
		            if ($mask == 'num_')	{
		            	$ids = explode('_', $fieldname);
		            	$domid = $ids[1];

		            	if (record_exists('monit_school_discipline_domain', 'id', $domid, 'schoolid', $sid))	{
		           			set_field('monit_school_discipline_domain', 'name', $domname, 'id', $domid, 'schoolid', $sid);
		            	} else {
		            		$newrec->schoolid = $sid;
		            		$newrec->name = $domname;
					       if (!insert_record('monit_school_discipline_domain', $newrec))	{
								error(get_string('errorinaddingeduarea','block_mou_school'), $redirlink);
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

	    echo  '<form name="components" method="post" action="educationareas.php">';
		echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
		echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
		echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
		$table = table_domains ($yid, $rid, $sid);
		print_color_table($table);
		echo  '<div align="center">';
		echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
		echo  '<input type="submit" name="setdefaults" value="'. get_string('setstandartvalue', 'block_mou_school') . '"></div>';
		echo  '</form>';
	} else {
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}
	

    print_footer();


function table_domains ($yid, $rid, $sid)
{
	global $CFG;

	$table->head  = array (	get_string('name', 'block_mou_school'), get_string('action', 'block_mou_school'));
    $table->align = array ("left", "center");
    $table->class = 'moutable';
  	$table->width = '40%';
    $table->size = array ('10%', '10%');

	$domains = get_records_sql ("SELECT * FROM {$CFG->prefix}monit_school_discipline_domain
								  WHERE schoolid=$sid ORDER BY name");

	if ($domains)	{

			foreach ($domains as $domain) {

				$strdiscipline = $domain->name;
				$insidetable = "<input type=text  name=num_{$domain->id} size=50 value=\"$strdiscipline\">";

				$title = get_string('deletecurriculum','block_mou_school');
			    $strlinkupdate = "<a title=\"$title\" href=\"delcurriculum.php?part=dd&amp;sid=$sid&amp;id={$domain->id}&amp;rid=$rid&amp;yid=$yid\">";
				$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";

				$table->data[] = array ($insidetable, $strlinkupdate);
			}
	}

    $insidetable = "<input type=text  name=num_0 size=50 value=''>";
	$table->data[] = array ($insidetable, '');

    return $table;
}
?>