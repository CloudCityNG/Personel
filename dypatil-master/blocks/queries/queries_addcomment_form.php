<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $PAGE, $OUTPUT, $USER, $DB;
require_once($CFG->dirroot.'/lib/formslib.php');
//$PAGE->set_url('/blocks/queries/queries_addcomment_form.php');
//$PAGE->set_context(context_system::instance());
//$PAGE->set_title('Comment form');
////$PAGE->navbar->add('Queries form');
//$PAGE->set_pagelayout('admin');
$queryid = optional_param('queryid',null,PARAM_INT);
class queries_addcomment_form extends moodleform {
    //Add elements to form
    public function definition() {
        $mform = $this->_form;
        //$mform->addElement('html', html_writer:: start_tag('div',array('id'=>'test-popup','class'=>'white-popup mfp-hide')));
        
        $mform->addElement('header', get_string('addacomment','block_queries'),'create a form');
        $mform->addElement('hidden','queryid',$this->_customdata['queryid']);
        $mform->addElement('text','summery',get_string('summery','block_queries'));
        $mform->setType('summery',PARAM_RAW);
        $mform->addRule('summery', 'empty', 'required', null,'client');
        
        $mform->addElement('textarea','comment',get_string('comment','block_queries'));
        $mform->setType('comment',PARAM_RAW);
        $mform->addRule('comment', 'empty', 'required', null, 'client');  
        
        $this->add_action_buttons(TRUE,'submit');
        
        //$mform->addElement('html', html_writer:: end_tag('div'));
    }
}
//echo $OUTPUT->header();
//
    //$mform = new queries_addcomment_form();
    //if($formdata2 = $mform->get_data()) {
    //   $commentrecord =new stdclass();
    //   $commentrecord->queryid = $queryid;
    //   $commentrecord->responduser = $USER->id;
    //   $commentrecord->summery = $formdata2->summery;
    //   $commentrecord->comment = $formdata2->comment;
    //   $commentrecord->postedtime = time();
    //   $DB->insert_record('query_response',$commentrecord);
    //}
    //else{
    //   $mform->display();
    //}
//echo $OUTPUT->footer();
?>




