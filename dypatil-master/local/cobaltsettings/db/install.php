<?php

defined('MOODLE_INTERNAL') || die;

function xmldb_local_cobaltsettings_install() {
    global $CFG, $OUTPUT, $DB;
    $defaultcategories = array("Academic levels", "Student levels");
    foreach ($defaultcategories as $cate) {
        $DB->insert_record('local_cobalt_entity', array('name' => $cate));
    }
    $category_types = array(
        array(1, 'Under Graduation'),
        array(1, 'Graduation'), array(2, 'Freshmen'), array(2, 'Sophomores'), array(2, 'Juniors'),
        array(2, 'Seniors'));
    foreach ($category_types as $types) {
        $DB->insert_record('local_cobalt_subentities', array('entityid' => $types[0], 'name' => $types[1]));
    }
}

?>
