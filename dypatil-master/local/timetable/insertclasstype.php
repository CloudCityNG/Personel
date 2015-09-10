<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;

$userinputclasstypename = optional_param('userinputctname', '', PARAM_TEXT);
$userinputschoolid = optional_param('userinputsid', 0, PARAM_INT);
$cltypename = optional_param('cltypename', '', PARAM_TEXT);
if ($userinputclasstypename && $userinputschoolid) {

    $compare_scale_clause = $DB->sql_compare_text('classtype') . ' = ' . $DB->sql_compare_text(':cltype');
    if (!$DB->record_exists_sql("select * from {local_class_scheduletype} where  $compare_scale_clause and schoolid =$userinputschoolid ", array('cltype' => $userinputclasstypename))) {


        $temp = new stdclass();
        $temp->schoolid = $userinputschoolid;
        $temp->classtype = $userinputclasstypename;
        $temp->visible = 1;
        $temp->usermodified = $USER->id;
        $temp->timecreated = time();
        $temp->timemodified = time();
        $insertedrowid = $DB->insert_record('local_class_scheduletype', $temp);
        echo $insertedrowid;
    } else
        echo 'exists';
}
?>
