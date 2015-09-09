<?php

/**
 * script for downloading users
 */
// error_reporting(E_ERROR);
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('lib.php');
$format = optional_param('format', '', PARAM_ALPHA);

global $DB;

if ($format) {
    $fields = array(
        'firstname' => 'firstname',
        'lastname' => 'lastname',
        'email' => 'email',
        'schoolname' => 'schoolname',
        'roleid' => 'roleid'
    );

    switch ($format) {
        case 'xls' : user_download_xls($fields);
    }
    die;
}

function user_download_xls($fields) {
    $hier = new hierarchy();
    $schools = $hier->get_assignedschools();

    global $CFG, $DB;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename('Users.xls');
    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }

    $sheetrow = 1;
    $myuser = users::getInstance();

    $users = $DB->get_records_sql("SELECT u.id, u.username, u.email, u.firstname, u.lastname, u.city, u.country,
                                            u.lastaccess, u.confirmed, u.mnethostid, u.suspended FROM {user} u, {local_users} lu
                                            WHERE lu.userid = u.id ");

    foreach ($users as $user) {
        $rid = $myuser->get_rolename($user->id);
        $post = new stdclass();
        $post->firstname = $user->firstname;
        $post->lastname = $user->lastname;
        $post->email = $user->email;

        $post->schoolname = $myuser->get_schoolnames($user);
        $post->roleid = $myuser->get_rolename($user->id);
        $col = 0;
        foreach ($fields as $fieldname) {
            $worksheet[0]->write($sheetrow, $col, $post->$fieldname);
            $col++;
        }
        $sheetrow++;
    }

    $workbook->close();
    die;
}
