<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$DB->delete_records('local_event_activities', array('id' => $_REQUEST['id']));
?>