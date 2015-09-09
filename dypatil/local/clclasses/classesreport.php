<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/clclasses/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/clclasses/classesreport.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_clclasses'));
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php'));
$PAGE->navbar->add(get_string('classreport', 'local_clclasses'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
$currenttab = 'view';
$hierarchy = new hierarchy();
$semclass = new schoolclasses();
$currenttab = "report";
$semclass->print_classestabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('classreport_desc', 'local_clclasses'));
}

$userlist = enrolled_user_list_status();
if (empty($userlist)) {
    echo get_string('nouseravail', 'local_clclasses');
} else {
    $data = array();
    foreach ($userlist as $user) {
        $result = array();
        $result[] = $user->firstname . ' ' . $user->lastname;
        $result[] = $user->email;
        $result[] = $user->classname;
        $result[] = $DB->get_field('local_semester', 'fullname', array('id' => $user->semesterid));
        if ($user->mentorapproval == 0) {
            $user->mentorapproval = 'Pending';
        } else {
            $user->mentorapproval = 'Approved';
        }
        $result[] = $user->mentorapproval;
        if ($user->registrarapproval == 0) {
            $user->registrarapproval = 'Pending';
        } else {
            $user->registrarapproval = 'Approved';
        }
        $result[] = $user->registrarapproval;
        $data[] = $result;
    }
    $PAGE->requires->js('/local/clclasses/js/classreport.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "classreport";
    $table->head = array(
        get_string('name', 'local_clclasses'),
        get_string('email', 'local_clclasses'),
        get_string('classesname', 'local_clclasses'),
        get_string('semester', 'local_semesters'),
        get_string('mentor', 'local_clclasses'),
        get_string('registrar', 'local_clclasses')
    );
    $table->size = array('20%', '20%', '20%', '20%', '19%');
    $table->align = array('left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>