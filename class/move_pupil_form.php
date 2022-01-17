<?php  // $Id: move_pupil_form.php,v 1.3 2010/05/06 13:20:39 Shtifanov Exp $

require_once($CFG->libdir.'/formslib.php');

class editmarks_form extends moodleform {
    function definition() {

        global $rid, $sid, $gid, $uid, $fullname;
	   
	    if (!$user1 = get_record('user', 'id', $uid) ) {
	        error('No such pupil in this class!', '../index.php');
		}
	
	   	$fullname = fullname($user1);
   	
        $mform =& $this->_form;

        $mform->addElement('header','', get_string('pupilmovein', 'block_mou_school', $fullname));

        $mform->addElement('text', 'cc', get_string('county','block_mou_school'), 'maxlength="100" size="100"');
        $mform->addRule('cc', get_string('missingname'), 'required', null, 'client');
        $mform->setType('cc', PARAM_TEXT);

        $mform->addElement('text', 'rr',  get_string('rayon','block_mou_school'), 'maxlength="100" size="100"');
        $mform->addRule('rr', get_string('missingname'), 'required', null, 'client');
        $mform->setType('rr', PARAM_TEXT);

        $mform->addElement('text', 'nn',  get_string('NasPunkt','block_mou_school'), 'maxlength="100" size="100"');
        $mform->addRule('nn', get_string('missingname'), 'required', null, 'client');
        $mform->setType('nn', PARAM_TEXT);

        $mform->addElement('text', 'ss',  get_string('school','block_mou_school'), 'maxlength="100" size="100"');
        $mform->addRule('ss', get_string('missingname'), 'required', null, 'client');
        $mform->setType('ss', PARAM_TEXT);

        $mform->addElement('text', 'cl',  get_string('class','block_mou_school'), 'maxlength="10" size="10"');
        $mform->addRule('cl', get_string('missingname'), 'required', null, 'client');
        $mform->setType('cl', PARAM_TEXT);
        
        $mform->addElement('text', 'date',  get_string('dateout','block_mou_school'), 'maxlength="10" size="10"');
        $mform->addRule('date', get_string('missingname'), 'required', null, 'client');
        $mform->setType('date', PARAM_TEXT);
        
		$mform->addElement('hidden', 'rid', $rid);  $mform->setType('rid', PARAM_INT);
		$mform->addElement('hidden', 'sid', $sid);  $mform->setType('sid', PARAM_INT);
		$mform->addElement('hidden', 'gid', $gid);  $mform->setType('gid', PARAM_INT);
		$mform->addElement('hidden', 'uid', $uid);  $mform->setType('uid', PARAM_INT);

        $this->add_action_buttons();
    }

    function validation($data) {
        $errors = array();

        if (0 == count($errors)){
            return true;
        } else {
            return $errors;
        }

    }

}
?>
