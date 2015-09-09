<?php
defined('MOODLE_INTERNAL') || die;

function xmldb_local_onlinepayment_install() {
    global $CFG, $OUTPUT, $DB;
    $data = new stdClass();
    $data->name = 'Value Added Tax';
    $data->display_name = 'Value Added Tax';
    $data->timecreated = time();
    $data->timemodified = time();
    $DB->insert_record('local_tax_type', $data);
}
?>