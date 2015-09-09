<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_academiccalendar_install() {
    global $CFG, $OUTPUT, $DB;
    $events = new stdClass();
    $defaultevents = array("Admission", "Registration", "Add and drop");
    foreach ($defaultevents as $devent) {
        $DB->insert_record('local_event_types', array('eventtypename' => $devent));
    }
}

?>