<?php

/**
* @method  print_adddroptabs
* @todo for printing tabs for status views
* @param string $current tab current tab name
* @return display tab view
* */

function statusview($currenttab) {
    global $OUTPUT;
    $tabs = array();
    $tabs[] = new tabobject('pending', new moodle_url('/local/approval/approval_id.php', array('mode' => 'pending')), get_string('pending', 'local_approval'));
    $tabs[] = new tabobject('approved', new moodle_url('/local/approval/approval_id.php', array('mode' => 'approved')), get_string('approved', 'local_approval'));
    $tabs[] = new tabobject('rejected', new moodle_url('/local/approval/approval_id.php', array('mode' => 'rejected')), get_string('rejected', 'local_approval'));
    echo $OUTPUT->tabtree($tabs, $currenttab);
}

/* ---Function for priinting tab--- 

function transfertabs($currenttab) {
    global $OUTPUT;
    $tabs = array();
    $tabs[] = new tabobject('pending', new moodle_url('/local/approval/approval_transfer.php', array('mode' => 'pending')), get_string('pending', 'local_approval'));
    $tabs[] = new tabobject('approved', new moodle_url('/local/approval/approval_transfer.php', array('mode' => 'approved')), get_string('approved', 'local_approval'));
    $tabs[] = new tabobject('rejected', new moodle_url('/local/approval/approval_transfer.php', array('mode' => 'rejected')), get_string('rejected', 'local_approval'));
    echo $OUTPUT->tabtree($tabs, $currenttab);
}
*/
?>