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
 * @package    local
 * @subpackage tag
 * @copyright  2014 Vinod Kumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once('../../config.php');
//require_once($CFG->dirroot.'/mod/wiki/locallib.php');
//require_once($CFG->dirroot.'/mod/sociopedia/locallib.php');

require_once('lib.php');

require_login();

$id = required_param('id', PARAM_INT);
$query = optional_param('searchquery', '', PARAM_RAW);
$tagid = optional_param('tagid', 0, PARAM_INT);
$tagtype = optional_param('tagtype', 'custom', PARAM_RAW);

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

$PAGE->set_url(new moodle_url('/local/tag/index.php'));
$PAGE->set_context(context_course::instance($course->id));
$PAGE->set_pagelayout('admin');
$PAGE->requires->css('/local/tags/css/style.css');
$systemcontext = context_system::instance();
$manage_link = '&nbsp;';

$PAGE->set_title(get_string('tags', 'tag'));
//$PAGE->set_heading($SITE->fullname.': '.$PAGE->title);
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('coursetagcloud', 'local_tags'));
customtag_search_box($id);
if ($query || $tagid) {
    echo "<div align='right'><a href='index.php?id=$course->id'>View all</a></div>";
}
if (!$tagid)
    customtag_print_search_results($id, $query);

if ($tagid) {
    $instances = $DB->get_records_select('tag_instance', 'tagid = ? GROUP BY itemtype', array($tagid));
    if ($instances) {
        foreach ($instances as $instance) {
            customtag_items_list($instance, $id);
        }
    }
}

echo $OUTPUT->footer();
