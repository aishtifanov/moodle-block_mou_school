<?PHP // $Id: adddiscip.php,v 1.18 2010/08/25 08:36:25 Shtifanov Exp $

    require_once('../../../config.php');
    require_once('../../monitoring/lib.php');

    $mode = required_param('mode', PARAM_ALPHA);    // new, add, edit, update
    $rid  = required_param('rid',  PARAM_INT);
	$sid  = required_param('sid',  PARAM_INT);
	$yid  = required_param('yid',  PARAM_INT);
    $ddid = optional_param('ddid', 0, PARAM_INT);   // Domain Discipline id
	$did  = optional_param('did',  0, PARAM_INT);	    // Discipline id

	$context = get_context_instance(CONTEXT_SCHOOL, $sid);
	if (!has_capability('block/mou_school:editdiscipline', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

   	$indexlink = $CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs = "<a href=\"$indexlink\">".get_string('title','block_mou_school').'</a>';
	$strtitle = get_string('discipline','block_mou_school');
    $redirlink = "{$CFG->wwwroot}/blocks/mou_school/curriculum/discipline.php?rid=$rid&amp;yid=$yid&amp;sid=$sid";
	$breadcrumbs .= "-> <a href=\"{$redirlink}\">$strtitle</a>";
   	$strtitle = get_string('editingdiscipline','block_mou_school');
	$breadcrumbs .= "-> $strtitle";
    print_header("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	if ($mode != 'new' && $recs = data_submitted())  {
		// print_r($recs); echo '<hr>';
        // notice(get_string('succesavedata','block_monitoring'), $CFG->wwwroot.'/blocks/mou_school/index.php');

		if ($mode === 'add')  {
			$rec->schoolid = $sid;
			$rec->disciplinedomainid = $recs->domainname;
			$rec->name = $recs->name;
			$rec->shortname = $recs->shortname;
			$rec->componentid = 0;
			$rec->parentid = 0;
			if (find_form_discipline_errors($rec, $err) == 0) {
				if ($did = insert_record('monit_school_discipline', $rec))	{
					 add_to_log(1, 'school', 'discipline added', $redirlink, $USER->lastname.' '.$USER->firstname);
					 save_subgroups($recs, $sid, $did);
					 notice(get_string('disciplineadded','block_mou_school'), $redirlink);
				} else
					error(get_string('errorinaddingdisc','block_mou_school'), $redirlink);
			} else $mode = "new";		
		} else if ($mode === 'update')	{
			$rec->id = required_param('currid', PARAM_INT);
			$rec->name = required_param('name');
			$rec->shortname = optional_param('shortname');
			$rec->disciplinedomainid = $recs->domainname;

			if (find_form_discipline_errors($rec, $err) == 0) {
				$rec->timemodified = time();
				if (update_record('monit_school_discipline', $rec))	{
					 add_to_log(1, 'school', 'discipline update', $redirlink, $USER->lastname.' '.$USER->firstname);
					 save_subgroups($recs, $sid, $did);
					 // notice(get_string('disciplineupdate','block_mou_school'), $redirlink);
					 redirect($redirlink, get_string('disciplineupdate','block_mou_school'), 0);
				} else
					error(get_string('errorinupdatingdisc','block_mou_school'), $redirlink);
			}
		}
        //notice(get_string('succesavedata','block_mou_school'), $CFG->wwwroot.'/blocks/mou_school/curriculum/discipline.php?mode=2&amp;sid=$sid&amp;yid=$yid&amp;rid=$rid');
		// redirect("setpoints.php?rid=0&amp;yid=$yid", get_string('succesavedata','block_monitoring'), 0);
	}


	 $discip_domains = get_records_sql ("SELECT *  FROM {$CFG->prefix}monit_school_discipline_domain
									     WHERE schoolid=$sid ORDER BY name");

	 $domainmenu = array();
	 $domainmenu[0] = get_string ('selecteducationarea', 'block_mou_school') . ' ...'; // $domainmenuselect1;

	 foreach ($discip_domains as $domains) {
		     $domainmenu[$domains->id] = $domains->name;
	 }

	print_heading($strtitle, "center", 2);

    if ($mode === 'new')  {
		// $rec->teacher = "";
		$rec->name = "";
		$rec->shortname = "";
		$rec->id = 0;
		$rec2->id = 0;
		$ddid = $did = 0;
    	$mode = 'add';

    } else if ($mode === 'edit')	{
			if ($did > 0) 	{
				$discipline = get_record('monit_school_discipline', 'id', $did);
				$rec->id = $discipline->id;
				$rec->name = $discipline->name;
				$rec->shortname = $discipline->shortname;

				$st = get_record('monit_school_teacher','id', $did);
				$rec2->id = $st->id;
			}
	    	$mode = 'update';
	}
    //  print_r($rec);
    // print_simple_box_start("center");
?>

	<form name="addform" method="post" action="adddiscip.php">
	<center>
	<table cellpadding="5">

	<tr valign="top">
	    <td align="right"><b><?php  print_string("domainname","block_mou_school") ?>:</b></td>
		<td align="left">  <?php   choose_from_menu ($domainmenu, 'domainname', $ddid, "", "", "", false); ?>
						  <?php if (isset($err["domainname"])) formerr($err["domainname"]); ?>
		</td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string("name") ?>:</b></td>
	    <td align="left">
			<input name="name" type="text" id="name" value="<?php p($rec->name) ?>" size="50" />
			<?php if (isset($err["name"])) formerr($err["name"]); ?>
	    </td>
	</tr>

	<tr valign="top">
	    <td align="right"><b><?php  print_string("shortname") ?>:</b></td>
	    <td align="left">
			<input name="shortname" type="text" id="shortname" value="<?php p($rec->shortname) ?>" size="10" />
			<?php if (isset($err["shortname"])) formerr($err["shortname"]); ?>
	    </td>
	</tr>
	</table>
  </div>
	 </center>


<?php

    $strsubgroup = '';
    $strshort = '';
  //  $subgroup = get_records('monit_school_subgroup','disciplineid',$did);
    $subgroup = get_records_sql("SELECT id, name, shortname FROM {$CFG->prefix}monit_school_subgroup
    							WHERE disciplineid=$did AND schoolid=$sid");
    // print_r($subgroup);
    $straddperiod = get_string('disciplinesubgroups','block_mou_school');
    print_heading($straddperiod, "center", 3);

    $table->head  = array (	get_string('fullname', 'block_mou_school'), get_string('short_name', 'block_mou_school'));
   	$table->align = array ("center", "center");
    $table->class = 'moutable';
    $table->width = '40%';
	$table->size = array ('10%', '10%');

    //$subgroups = get_records_sql("SELECT * FROM {$CFG->prefix}monit_school_subgroup WHERE schoolid=$sid AND disciplineid={$disc->id}");

      if ($subgroup){
      $insidetable = '<table align="center" border=0>';
      $insidetable2 = '<table align="center" border=0>';
	        foreach ($subgroup as $sub){
	    	 $strsubgroup = $sub->name;
	    	 $strshort = $sub->shortname;
	    	 $insidetable .= "<td><input type=text name=num_f_{$sub->id} size=20 value=\"$strsubgroup\"></td></tr>";
	    	 $insidetable2 .= "<td><input type=text name=num_s_{$sub->id} size=20 value=\"$strshort\"></td></tr>";
	        }
      }  else {
      	    $insidetable = '<table align="center" border=0>';
            $insidetable2 = '<table align="center" border=0>';

            $insidetable .= "<td><input type=text name=num_a_0 size=20 value=''></td></tr>";
   	 		$insidetable2 .= "<td><input type=text name=num_b_0 size=20 value=''></td></tr>";
   	 		$insidetable .= "<td><input type=text name=num_c_0 size=20 value=''></td></tr>";
   	 		$insidetable2 .= "<td><input type=text name=num_d_0 size=20 value=''></td></tr>";
   	 		$insidetable .= "<td><input type=text name=num_e_0 size=20 value=''></td></tr>";
   	 		$insidetable2 .= "<td><input type=text name=num_g_0 size=20 value=''></td></tr>";

      }
   	  $insidetable .= "<td><input type=text name=num_f_0 size=20 value=''></td></tr>";
   	  $insidetable2 .= "<td><input type=text name=num_s_0 size=20 value=''></td></tr>";
      $insidetable .= '</table>';
      $insidetable2 .= '</table>';
      $table->data[] = array ($insidetable,$insidetable2);

	echo  '<input type="hidden" name="mode" value="' . $mode . '">';
	echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
	echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
	echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
	echo  '<input type="hidden" name="ddid" value="' .  $ddid . '">';
	echo  '<input type="hidden" name="did" value="' .  $did . '">';
	echo  '<input type="hidden" name="currid" value="' .  $rec->id . '">';
	print_color_table($table);
	echo  '<div align="center">';
	echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '"></div>';
	echo  '</form><p>&nbsp;</p>';


	print_simple_box_start('center', '60%');
	
	echo '<br><b><center>Примеры задания подгрупп предметов:</center></b><br><br>
	<table border=1 class=moutable align=center cellspacing="2" cellpadding="5">
<tr>
	<td>Информатика и ИКТ</td>
	<td>* группа А<br>* группа В</td>
</tr>
<tr>
	<td>Технология</td>
	<td>* мальчики<br>* девочки</td>
</tr>
<tr>
	<td>Физкультура</td>
	<td>* мальчики<br>* девочки</td>
</tr>
<tr>
	<td>Иностранный язык</td>
	<td>* Английский<br>* Немецкий</td>
</tr>
	</table>';
	print_simple_box_end();
	print_footer();


function find_form_discipline_errors(&$rec, &$err, $mode='add') {

    if (empty($rec->name))	{
	    $err["name"] = get_string("missingname");
	}
    if (empty($rec->shortname))	{
	    $err["shortname"] = get_string("missingname");
	}

	if ($rec->disciplinedomainid == 0)	{
		$err["domainname"] = get_string("missingname");
	}
    return count($err);
}


function save_subgroups($recs, $sid, $did)
{
	global $CFG;

	$arrsubgroupids = array();
    $flag=true;
	foreach($recs as $fieldname => $profilename)	{

		if ($profilename != '')	{
            $mask = substr($fieldname, 0, 4);
            if ($mask == 'num_')	{
            	$ids = explode('_', $fieldname);
            	$f_s = $ids[1];
            	$subgroupid = $ids[2];
            	
            	if ($subgroupid != 0)	{
            		$arrsubgroupids[] = $subgroupid;
	            	if (record_exists('monit_school_subgroup', 'id', $subgroupid, 'schoolid', $sid))	{
	            		if ($f_s == 'f')	{
		           			set_field('monit_school_subgroup', 'name', $profilename, 'id', $subgroupid, 'schoolid', $sid);
		           		} else 	if ($f_s == 's')	{
		           			set_field('monit_school_subgroup', 'shortname', $profilename, 'id', $subgroupid, 'schoolid', $sid);
		           		}
	            	}
	            } else {
	            	if ($flag)	{
	            		$name_f = array('num_a_0', 'num_c_0', 'num_e_0', 'num_f_0');
	            		$name_s = array('num_b_0', 'num_d_0', 'num_g_0', 'num_s_0');

	            		for ($i = 0; $i < 4; $i++)	{
		            		if (isset($recs->{$name_f[$i]}) && !empty($recs->{$name_f[$i]}) &&
			            		isset($recs->{$name_s[$i]}) && !empty($recs->{$name_s[$i]}))	{

			            		$newrec->schoolid = $sid;
			            		$newrec->disciplineid = $did;
			            		$newrec->name = $recs->{$name_f[$i]};
		            		    $newrec->shortname = $recs->{$name_s[$i]};

						       if (!$arrsubgroupids[] = insert_record('monit_school_subgroup', $newrec))	{
									error(get_string('errorinaddingcomponent','block_mou_school'), $CFG->wwwroot.'/blocks/mou_school/components.php');
							   }
							}
					   }
					   $flag=false;
					}

            	}
            }

        }
	}

	// print_r($arrsubgroupids); echo '<hr>'; 
	if ($dissubgroups =  get_records_select('monit_school_subgroup', "disciplineid = $did and schoolid = $sid"))	{
		foreach ($dissubgroups  as $dissubgroup)	{
			if (!in_array($dissubgroup->id, $arrsubgroupids))	{
				// print_r($dissubgroup); echo '<hr>';
				delete_records('monit_school_subgroup', 'id', $dissubgroup->id);  
			}
		}
	}	

	
 }


?>