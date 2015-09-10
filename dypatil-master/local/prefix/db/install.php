<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_prefix_install() {
    global $CFG, $OUTPUT, $DB;
    $defaultaudit_names = array("Student ID", "Batch", "Course", "Class");
    foreach ($defaultaudit_names as $entityname) {
        $DB->insert_record('local_create_entity', array('entity_name' => $entityname));
    }
}

?>