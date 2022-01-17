<?php  // $Id: edittextbook.php,v 1.1 2011/01/17 07:16:53 shtifanov Exp $

    require_once("../../../config.php");
    require_once('../../monitoring/lib.php');
    require_once('../../mou_ege/lib_ege.php');
    require_once('edittb_form.php');

    $rid = required_param('rid', PARAM_INT);          // Rayon id
    $sid = required_param('sid', PARAM_INT);       // School id
    $yid = required_param('yid', PARAM_INT);       // Year id
	$catid  = required_param('catid', PARAM_INT);       // Category textbook
	$tbid  = required_param('tbid', PARAM_INT);       // Textbook ID

    require_login();
    
	$context = get_context_instance(CONTEXT_REGION, 1);
	 $strsql = "SELECT a.id, roleid, contextid, contextlevel, instanceid, path, depth  
				FROM mdl_role_assignments a	RIGHT JOIN mdl_context ctx ON a.contextid=ctx.id
			   WHERE userid={$USER->id}";
	  // echo $strsql . '<hr>';
	 if ($ctxs = get_records_sql($strsql))	{
	 		// print_r($ctxs);
			foreach($ctxs as $ctx1)	{
				if ($ctx1->contextlevel > 1000 && $ctx1->contextlevel <= CONTEXT_RAYON)	{
					$context = $ctx1;
					break;
				}
			}
	}				

	if (!has_capability('block/mou_school:editclasslist', $context))	{
		error(get_string('permission', 'block_mou_school'), '../index.php');
	}	

	 $strtextbook = get_string('textbooks', 'block_mou_ege');
	 $strtitle = get_string('edittextbook', 'block_mou_ege');


	$breadcrumbs = '<a href="'.$CFG->wwwroot."/blocks/mou_school/index.php?rid=$rid&amp;yid=$yid&amp;sid=$sid\">".get_string('title','block_mou_school').'</a>';
	$breadcrumbs .= "-> <a href=\"textbook.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;catid=$catid\">$strtextbook</a>";
	$breadcrumbs .= " -> $strtitle";
    print_header_mou("$SITE->shortname: $strtitle", $SITE->fullname, $breadcrumbs);

	// print_tabs_years($yid, "edittextbook.php?rid=$rid&amp;sid=$sid&amp;yid=");

	$redirlink = "textbook.php?rid=$rid&amp;sid=$sid&amp;yid=$yid&amp;catid=$catid";
	
	if ($tbid)	{
    	if (!$textbook = get_record('monit_textbook', 'id', $tbid))	{
    		error('Text book not found!', $redirlink);
    	}
    } else {
    	$textbook = null;
    }	

	$editform = new edittb_form('edittextbook.php');
	    // now override defaults if course already exists
 	if (!empty($textbook)) {
        $editform->set_data($textbook);
    }
	    
    if ($editform->is_cancelled())	{
		redirect($redirlink, '', 0);
    } else if ($data = $editform->get_data()) 	{
            // print_r($data); echo  '<hr>';
	    	if (!empty($textbook))	 {
		    	$data->id =  $textbook->id;
		    	// print_r($data);
		        if (update_record('monit_textbook', $data)) {
		            // notice(get_string('giaresultupdated', 'block_mou_ege', $data->id));
		            redirect($redirlink, get_string('textbookupdated', 'block_mou_ege', $data->id), 0);
		        } else {
		            error('Error in update textbook.', $redirlink);
		        }
		    } else {
		    	$rec->categoryid  = $catid;
		    	$rec->authors = $data->authors;
		    	$rec->name = $data->name;
		    	$rec->numclass  = $data->numclass;
		    	$rec->publisher  = $data->publisher;
		        if ($newid = insert_record('monit_textbook', $rec)) {
		            // notice(get_string('giaresultupdated', 'block_mou_ege', $data->id));
		            redirect($redirlink, get_string('textbookupdated', 'block_mou_ege', $newid), 0);
		        } else {
		            print_r($rec);
		            error('Error in insert pupil mark.');
		        }

		    }
	} 
	
	$editform->display();
 
    print_footer();

?>
