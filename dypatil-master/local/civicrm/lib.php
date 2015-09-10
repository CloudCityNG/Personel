<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * General plugin functions.
 *
 * @package    local
 * @subpackage Civicrm
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\module as module;

/*
 * Function to add the data into the database
 */

function civicrm_add_instance($data) {
    global $DB;
    $data->id = $DB->insert_record('local_civicrm', $data);
    return $data->id;
}

/**
 * Update the Faculty into the database
 */
function civicrm_update_instance($tool) {
    global $DB;
    $DB->update_record('local_civicrm', $tool);
}

/**
 * Delete the faculty
 */
function civicrm_delete_instance($tool) {
    global $DB;
    $DB->delete_records('local_civicrm', array('id' => $tool));
}

function local_civicrm_cron() {
    global $DB, $CFG;
    echo "Start pushing Civicrm contacts";
    $civicrmhost = $DB->get_records('local_civicrm');
    foreach ($civicrmhost as $civicrm) {
        $key = $civicrm->civikeys;
        $api_key = $civicrm->civiapikeys;
        $host = $civicrm->civihost;
    }
    $usermode = "moocuser";
    push_to_civicrmhost($key, $api_key, $host, $usermode);
    $usermode = "pguser";
    push_to_civicrmhost($key, $api_key, $host, $usermode);
    echo "End pushing Civicrm contacts";
}

function push_to_civicrmhost($civikey, $api_key, $host, $usermode) {
    global $DB, $CFG;
    if ($usermode == "moocuser")
        $sql = "SELECT u.id,u.firstname,u.lastname,u.email FROM {user} u LEFT OUTER JOIN {local_civicrm_users} lcu ON lcu.moocuser =u.id LEFT OUTER JOIN {local_users} lc ON lc.userid=u.id where lcu.moocuser is NULL AND lc.userid is NULL AND u.id>2";
    if ($usermode == "pguser")
        $sql = "SELECT la.id,la.firstname,la.lastname,la.email,la.status,(select p.fullname from {local_program} p where p.id=la.programid) as program FROM {local_admission} la LEFT OUTER JOIN {local_civicrm_users} lcu ON lcu.pguser =la.id where lcu.pguser is NULL";

    $users = $DB->get_records_sql($sql);

    foreach ($users as $userlist) {
        $request = "";
        $param["entity"] = "contact";
        $param["action"] = "create";
        $param["key"] = $civikey;
        $param["api_key"] = $api_key;
        $param["contact_type"] = "Individual";
        $param["first_name"] = "abcdefg";
        $param["last_name"] = "totalrecall";
        $param["email_greeting_display"] = "totalrecall@eabyas.in";

        foreach ($param as $key => $val) {
            //we have to urlencode the values
            $request.= $key . "=" . urlencode($val);
            //append the ampersand (&) sign after each paramter/value pair
            $request.= "&";
        }
        $request = substr($request, 0, strlen($request) - 1);
        //this is the url of the gateway's interface
        $url = "$host/site/sites/all/modules/civicrm/extern/rest.php";
        //initialize curl handle

        $ch = curl_init("$host/sites/all/modules/civicrm/extern/rest.php");
        curl_setopt($ch, CURLOPT_URL, $url); //set the url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return as a variable
        curl_setopt($ch, CURLOPT_POST, 1); //set POST method
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables

        $response = curl_exec($ch); //run the whole process and return the response
        if ($response) {
            $user = new stdClass();

            if ($usermode == "moocuser")
                $user->moocuser = $userlist->id;
            if ($usermode == "pguser")
                $user->pguser = $userlist->id;
            $DB->insert_record('local_civicrm_users', $user);
            echo "Success fully Pushed user" . '&nbsp;' . $userlist->firstname;
        }


        $contactxml = simplexml_load_file("$host/sites/all/modules/civicrm/extern/rest.php?q=civicrm/contact/get&key=$civikey&api_key=$api_key&last_name=$userlist->lastname");

        foreach ($contactxml->children() as $contact) {
            $civicrmuserid = $contact->id;
            if ($usermode == "moocuser") {
                $civicrmuser = $DB->get_record("local_civicrm_users", array('moocuser' => $userlist->id));
                $tagid = 2;
                $groupid = 2;
                $activity_type_id = 2;
            }
            if ($usermode == "pguser") {
                $civicrmuser = $DB->get_record("local_civicrm_users", array('pguser' => $userlist->id));
                $tagid = 3;
                $groupid = 3;
                $activity_type_id = 3;
                $programname = $userlist->program;
                $status = $userlist->status;
            }
            $contactuid = new stdClass();
            $contactuid->id = $civicrmuser->id;
            $contactuid->crmuserid = $contact->id;
            $sql = "UPDATE {local_civicrm_users} SET crmuserid = {$contact->id} WHERE id = {$civicrmuser->id}";
            $DB->execute($sql);
            push_to_email($civicrmuserid, $civikey, $api_key, $host, $userlist->email);
            push_to_event_tags($civicrmuserid, $civikey, $api_key, $host, $tagid);
            push_to_groups($civicrmuserid, $civikey, $api_key, $host, $groupid);
            push_to_activity($civicrmuserid, $civikey, $api_key, $host, $activity_type_id, $programname, $status);
        }
    }
}

function push_to_email($civicrmuserid, $civikey, $apikey, $host, $email) {
    $request = "";
    $param["entity"] = "Email";
    $param["action"] = "create";
    $param["key"] = $civikey;
    $param["api_key"] = $apikey;
    $param["contact_id"] = $activityid;
    $param["location_type_id"] = 1;
    $param["email"] = $email;
    foreach ($param as $key => $val) {
        //we have to urlencode the values
        $request.= $key . "=" . urlencode($val);
        //append the ampersand (&) sign after each paramter/value pair
        $request.= "&";
    }
    $request = substr($request, 0, strlen($request) - 1);
    //this is the url of the gateway's interface

    $url = "$host/sites/all/modules/civicrm/extern/rest.php";
    //initialize curl handle

    $ch = curl_init("$host/sites/all/modules/civicrm/extern/rest.php");
    curl_setopt($ch, CURLOPT_URL, $url); //set the url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return as a variable
    curl_setopt($ch, CURLOPT_POST, 1); //set POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
    $response = curl_exec($ch); //run the whole process and return the response

    if ($response) {
        echo "Success fully added the Email";
    }
    curl_close($ch);
}

function push_to_event_tags($civicrmuserid, $civikey, $apikey, $host, $tagid) {

    $request = "";
    $param["entity"] = "EntityTag";
    $param["action"] = "create";
    $param["key"] = $civikey;
    $param["api_key"] = $apikey;
    $param["entity_table"] = "civicrm_contact";
    $param["entity_id"] = $civicrmuserid;
    $param["tag_id"] = $tagid;
    $param["debug"] = 1;
    foreach ($param as $key => $val) {
        //we have to urlencode the values
        $request.= $key . "=" . urlencode($val);
        //append the ampersand (&) sign after each paramter/value pair
        $request.= "&";
    }
    $request = substr($request, 0, strlen($request) - 1);
    //this is the url of the gateway's interface

    $url = "$host/sites/all/modules/civicrm/extern/rest.php";
    //initialize curl handle
    $ch = curl_init("$host/sites/all/modules/civicrm/extern/rest.php");
    curl_setopt($ch, CURLOPT_URL, $url); //set the url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return as a variable
    curl_setopt($ch, CURLOPT_POST, 1); //set POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
    $response = curl_exec($ch); //run the whole process and return the response

    if ($response) {
        echo "Success fully Pushed Tagss";
    }
    curl_close($ch);
}

function push_to_groups($civicrmuserid, $civikey, $apikey, $host, $groupid) {

    $request = "";
    $param["entity"] = "GroupContact";
    $param["action"] = "create";
    $param["key"] = $civikey;
    $param["api_key"] = $apikey;
    $param["group_id"] = $groupid;
    $param["contact_id"] = $civicrmuserid;
    $param["statu"] = "Added";
    $param["debug"] = 1;
    foreach ($param as $key => $val) {
        //we have to urlencode the values
        $request.= $key . "=" . urlencode($val);
        //append the ampersand (&) sign after each paramter/value pair
        $request.= "&";
    }
    $request = substr($request, 0, strlen($request) - 1);

    //this is the url of the gateway's interface

    $url = "$host/sites/all/modules/civicrm/extern/rest.php";
    //initialize curl handle

    $ch = curl_init("$host/sites/all/modules/civicrm/extern/rest.php");
    curl_setopt($ch, CURLOPT_URL, $url); //set the url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return as a variable
    curl_setopt($ch, CURLOPT_POST, 1); //set POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
    $response = curl_exec($ch); //run the whole process and return the response

    if ($response) {
        echo "Success fully Pushed to Groups";
    }
    curl_close($ch);
}

function push_to_activity($civicrmuserid, $civikey, $apikey, $host, $activityid, $program, $status) {

    $request = "";
    $param["entity"] = "Activity";
    $param["action"] = "create";
    $param["key"] = $civikey;
    $param["api_key"] = $apikey;
    $param["activity_type_id"] = $activityid;
    $param["source_contact_id"] = $civicrmuserid;
    $param["statu"] = "Added";
    $param["debug"] = 1;
    $today = date('Y-m-d');
    if ($status == 1)
        $param["subject"] = "Accepted onto PGC program \"$program\"";
    if ($status == 2)
        $param["subject"] = "Unsuccessfull PGC apllication for program ";

    $param["activity_date_time"] = $today;


    foreach ($param as $key => $val) {
        //we have to urlencode the values
        $request.= $key . "=" . urlencode($val);
        //append the ampersand (&) sign after each paramter/value pair
        $request.= "&";
    }
    $request = substr($request, 0, strlen($request) - 1);

    //this is the url of the gateway's interface

    $url = "$host/sites/all/modules/civicrm/extern/rest.php";
    //initialize curl handle

    $ch = curl_init("$host/sites/all/modules/civicrm/extern/rest.php");
    curl_setopt($ch, CURLOPT_URL, $url); //set the url
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //return as a variable
    curl_setopt($ch, CURLOPT_POST, 1); //set POST method
    curl_setopt($ch, CURLOPT_POSTFIELDS, $request); //set the POST variables
    $response = curl_exec($ch); //run the whole process and return the response

    if ($response) {
        echo "Success fully added the activity";
    }
    curl_close($ch);
}
