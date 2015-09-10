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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage  providing  global settings
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/cobaltsettings/lib.php');
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/cobaltsettings/view_categorylevel.php');
$PAGE->navbar->add(get_string('global_settings', 'local_cobaltsettings'), new moodle_url('/local/cobaltsettings/index.php'));
$PAGE->navbar->add(get_string('default_entity', 'local_cobaltsettings'));
echo $OUTPUT->header();
/* ---global settings heading--- */
echo $OUTPUT->heading(get_string('cobalt_entitysettings', 'local_cobaltsettings'));

try {
    /* ---adding tabs using global_settings_tabs function--- */
    $currenttab = 'default_entities';
    $global_ob = global_settings::getInstance();
    $global_ob->globalsettings_tabs($currenttab, 'entitysettings');

    /* ---description of the  table--- */
    echo get_string('default_entityinfo', 'local_cobaltsettings');
    /* ---checking if login user is registrar or admin--- */
    $school_id = $global_ob->check_loginuser_registrar_admin(true);

    $categories = $DB->get_records('local_cobalt_entity');
    $data = array();
    foreach ($categories as $cate) {
        $line = array();
        $entity = ' <div style="font-size:15px;"><b>' . $cate->name . '</b></div>';
        $c_types = $DB->get_records('local_cobalt_subentities', array('entityid' => $cate->id));
        $sub_entity = '';
        $sub_entity = '</br>';
        foreach ($c_types as $ct) {
            $sub_entity.='<div style="margin-left:20px; font-size:13px;">' . $ct->name . '</div>';
        }
        $line[] = $entity;
        $line[] = $sub_entity;

        $data[] = $line;
    }
    /* ---end of main foreach--- */

    $PAGE->requires->js('/local/cobaltsettings/js/pagi.js');
    $table = new html_table();
    $table->id = "setting";
    $table->head = array(
        get_string('Entity', 'local_cobaltsettings'),
        get_string('SubEntities', 'local_cobaltsettings'));
    $table->size = array('5%', '30%');
    $table->align = array('left', 'left', 'left', 'center');
    $table->width = '50%';
    $table->data = $data;
    echo html_writer::table($table);
}
/* ---end of try block--- */ catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>




