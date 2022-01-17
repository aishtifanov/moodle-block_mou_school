<?php // $Id: rooms.php,v 1.8 2012/02/13 10:32:25 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../lib_school.php');
	require_once('../authbase.inc.php');    

	if (!has_capability('block/mou_school:editschedule', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	


    switch ($action)	{
    	case 'excel': rooms_download($rid, $sid, $yid);
        			  exit();
		case 'del': $id = required_param('id', PARAM_INT);       // Room id
		
					if (record_exists_mou('monit_school_class_schedule_'.$rid, 'roomid', $id))	{
						notify(get_string('roomhasnotdeleted', 'block_monitoring'));
					}	else {
						delete_records('monit_school_room', 'id', $id);
					}	
		break;
	}
	
    if ($recs = data_submitted())  {
		// print_r($recs); echo '<hr>';
		$redirlink = "rooms.php?sid=$sid&amp;yid=$yid&amp;rid=$rid";
		
		if (isset($recs->setexample))	{
        	$room->schoolid = $sid;
        	$room->seats = 0;
			for ($i=1; $i<=4; $i++)	{
  				$room->floor = $i;
  				for ($j=0; $j<=5; $j++)	{
					$room->name = $i.'0'.$j;
					if (!record_exists('monit_school_room', 'schoolid', $sid, 'floor', $i, 'name', $room->name))	{
			       		if (!insert_record('monit_school_room', $room))	{
							error(get_string('errorinaddingroom','block_mou_school'), $redirlink);
				  		}
				  	}	
				}	
			}
		}	else {
			$rooms = array();
			foreach($recs as $fieldname => $value)	{
			    $mask = substr($fieldname, 0, 2);
			    switch ($mask)  {
					case 'f_': 	$ids = explode('_', $fieldname);
			            		$rooms[$ids[1]]->floor = $value;
	  				break;
					case 'n_': 	$ids = explode('_', $fieldname);
			            		$rooms[$ids[1]]->name = $value;
	  				break;
					case 's_': 	$ids = explode('_', $fieldname);
			            		$rooms[$ids[1]]->seats = $value;
	  				break;
	  			}
	  		}
			foreach ($rooms as $key => $room)  {
				if ($key > 0) {
					$room->id = $key;
			        if (!update_record('monit_school_room', $room))	{
						error(get_string('errorinupdatingroom','block_mou_school'), $redirlink);
				    }
	        	} else {
	        		if ($room->floor != 0 && $room->name != '-') { // && $room->seats != 0)  {
	        			$room->schoolid = $sid;
			       		if (!insert_record('monit_school_room', $room))	{
							error(get_string('errorinaddingroom','block_mou_school'), $redirlink);
				  		}
				  	}
	        	}
			}
		}	
	}

    $currenttab = 'createschedule';
    include('tab_act.php');

    $currenttab = 'rooms';
    include('tab_create.php');

    echo  '<form name="rooms" method="post" action="rooms.php">';
	echo  '<input type="hidden" name="rid" value="' .  $rid . '">';
	echo  '<input type="hidden" name="sid" value="' .  $sid . '">';
	echo  '<input type="hidden" name="yid" value="' .  $yid . '">';
	$table = table_rooms ($yid, $rid, $sid);
	print_color_table($table);
	echo  '<div align="center">';
	echo  '<input type="submit" name="savepoints" value="'. get_string('savechanges') . '">';
	echo  '<input type="submit" name="setexample" value="'. get_string('autocreatexample', 'block_mou_school') . '">';
	echo  '</form>';

    print_footer();



function table_rooms ($yid, $rid, $sid)
{
	global $CFG;


	$table->head  = array (get_string('floorroom','block_mou_school'), get_string("nameroom","block_mou_school"),
						   get_string('setsroom','block_mou_school'), get_string("action","block_mou_school"));
	$table->align = array ('center', 'center', 'center', 'center');
    $table->size = array ('10%', '30%', '20%', '10%');
	$table->columnwidth = array (7, 20, 20, 9);
    // $table->datatype = array ('char', 'char');
    $table->class = 'moutable';
   	$table->width = '70%';
    // $table->size = array ('10%', '10%');
    $table->titles = array();
    $table->titles[] = get_string('rooms', 'block_mou_school');
    $table->worksheetname = 'rooms';

	$options = array('-');
    for ($i=1; $i<=7; $i++) {
        $options[$i] = $i;
    }

	$rooms = get_records('monit_school_room', 'schoolid', $sid );
	if ($rooms)	{
		foreach ($rooms as $room) 	{
			$tabledata = array();
			$roomfloor = 0;
			if (isset($room->floor) && !empty($room->floor)) 	{
				$roomfloor = $room->floor;
			}
			// $tabledata[] = "<input type=text  name=f_{$room->id} size=1 maxlength=1 value=\"$roomfloor\">";
        	$tabledata[] = choose_from_menu ($options, 'f_'.$room->id, $roomfloor, '0', "", "", true);


			$roomname = '-';
			if (isset($room->name) && !empty($room->name)) 	{
				$roomname = $room->name;
			}
			$tabledata[] = "<input type=text  name=n_{$room->id} size=15 value=\"$roomname\">";

			$roomseat = 0;
			if (isset($room->seats) && !empty($room->seats)) 	{
				$roomseat = $room->seats;
			}
			$tabledata[] = "<input type=text  name=s_{$room->id} size=3 maxlength=4 value=\"$roomseat\">";

			$title = get_string('deletingroom', 'block_mou_school');
		    $strlinkupdate = "<a title=\"$title\" href=\"rooms.php?action=del&amp;id={$room->id}&amp;sid=$sid&amp;yid=$yid&amp;rid=$rid\">";
			$strlinkupdate .= "<img src=\"{$CFG->pixpath}/i/cross_red_big.gif\" alt=\"$title\" /></a>&nbsp;";
			$tabledata[] = $strlinkupdate;

			$table->data[] = $tabledata;
		}
    }

	$tabledata = array();
	// $tabledata[] = "<input type=text  name=f_0 size=1 maxlength=1 value=0>";
	$tabledata[] = choose_from_menu ($options, 'f_0', 0, '0', "", "", true);
	$tabledata[] = "<input type=text  name=n_0 size=15  value=->";
	$tabledata[] = "<input type=text  name=s_0 size=3 maxlength=4 value=0>";
	$table->data[] = $tabledata;

    return $table;
}

?>


