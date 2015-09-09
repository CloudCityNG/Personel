<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
$shortname = $_REQUEST['sn'];

$compare_scale_clause = $DB->sql_compare_text('shortname') . ' = ' . $DB->sql_compare_text(':short');
$shortname_exist = $DB->get_records_sql("select * from {local_cobaltcourses} where  $compare_scale_clause", array('short' => $shortname));

if ($shortname_exist)
    echo "exist";
?>
