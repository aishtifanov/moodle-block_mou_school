<?php // $Id: lib_plans.php,v 1.9 2012/02/21 06:34:41 shtifanov Exp $

function listbox_plans2($scriptname, $sid, $yid, $gid, $cdid, &$planid)
{
  global $CFG;

  $did = get_record('monit_school_class_discipline','id', $cdid);
  $pid = get_record('monit_school_class','id', $gid);
			
  $strtitle = get_string('selectlessonplan', 'block_mou_school') . '...';
  $planmenu = array();

  $planmenu[0] = $strtitle;
  $currid = 0;
  if ($yid != 0 && $sid != 0 && $gid != 0 && $cdid != 0)  {

		$plans =  get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_discipline_plan
 								  WHERE schoolid = $sid and parallelnum = {$pid->parallelnum} and disciplineid = {$did->disciplineid}");
		if ($plans)	{
			foreach ($plans as $p) 	{
				$planmenu[$p->id] = $p->name;
				$currid = $p->id;
			}
		}
		
		$count = count($plans);
		if ($count == 1) {    
		    $planid = $currid;
        } else {
            $class = get_record_select('monit_school_class', "id = $gid", 'id, name');
            // print_r($class);
            $cntplansforoneclass = 0;
            foreach ($plans as $p) 	{
                // $pname = mb_strtoupper($p->name); 
                $pos = mb_strpos($p->name, $class->name);
                if ($pos === false) continue;
                else $cntplansforoneclass++;
            }    
            
            if ($cntplansforoneclass==1)    {
                foreach ($plans as $p) 	{
                    // $pname = mb_strtoupper($p->name); 
                    $pos = mb_strpos($p->name, $class->name);
                    if ($pos === false) continue;
                    $planid = $p->id;
                    break;
                }
            }        
        }      
  }

  echo '<tr><td>'.get_string('planplan','block_mou_school').':</td><td>';
  popup_form($scriptname, $planmenu, "switchplan", $planid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}

// Display list discipline for parallel
function listbox_plans($scriptname, $sid, $yid, $pid, $did, $planid)
{
  global $CFG;

  $strtitle = get_string('selectlessonplan', 'block_mou_school') . '...';
  $planmenu = array();

  $planmenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $pid != 0 && $did != 0)  {

		$plans =  get_records_sql ("SELECT id, name FROM {$CFG->prefix}monit_school_discipline_plan
										  WHERE schoolid = $sid and parallelnum = $pid and disciplineid = $did");
		if ($plans)	{
			foreach ($plans as $p) 	{
				$planmenu[$p->id] = $p->name;
			}
		}
  }

  echo '<tr><td>'.get_string('curriculums','block_mou_school').':</td><td>';
  popup_form($scriptname, $planmenu, "switchplan", $planid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


// Display list discipline for parallel
function listbox_units($scriptname, $sid, $yid, $pid, $did, $planid, $unitid)
{
  global $CFG;

  $strtitle = get_string('selectunitsplan', 'block_mou_school') . '...';
  $unitmenu = array();

  $unitmenu[0] = $strtitle;

  if ($yid != 0 && $sid != 0 && $pid != 0 && $did != 0 && $planid != 0)  {

		$units =  get_records_sql("SELECT id, schoolid, planid, number, name, description
								   FROM {$CFG->prefix}monit_school_discipline_unit
				     			   WHERE planid = $planid
				     			   ORDER BY number");

		if ($units)	{
			foreach ($units as $u) 	{
				$unitmenu[$u->id] = $u->name;
			}
		}
  }

  echo '<tr><td>'.get_string('unitplans','block_mou_school').':</td><td>';
  popup_form($scriptname, $unitmenu, "switchunit", $unitid, "", "", "", false);
  echo '</td></tr>';
  return 1;
}


?>