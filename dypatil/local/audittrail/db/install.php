<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_audittrail_install() {
    global $CFG, $OUTPUT, $DB;
    $defaultaudit_names = array("Grade change", "Score change", "Userprofile change", "Mentor change", "User delete");
    foreach ($defaultaudit_names as $auditname) {
        $DB->insert_record('local_audit_trail', array('auditname' => $auditname));
    }
}

?>