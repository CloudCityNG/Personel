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
 * Blog Menu Block page.
 *
 * @package    block
 * @subpackage academic_status
 * @copyright  Sreenivas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The blog menu block class
 */
class block_academic_status extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_academic_status');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => true, 'my' => true, 'tag' => false);
    }

    function instance_allow_config() {
        return true;
    }
	
	function get_required_javascript() {
		$this->page->requires->jquery();
        $this->page->requires->js('/local/curriculum/js/toggle.js',true);
    }

    function get_content() {
        global $CFG, $USER;
        if ($this->content !== NULL) {
            return $this->content;
        }
		if(!isloggedin()){
			return $this->content;
		}
		if(is_siteadmin()){
			return $this->content;
		}
	
		//this block is for students
		$usercontext =  context_user::instance($USER->id);
	    if(!has_capability('local/clclasses:enrollclass', $usercontext)){ 
			return $this->content; 
		}
      
		$this->page->requires->css('/local/curriculum/css/styles.css');
        // Prep the content
        $this->content = new stdClass();
		require_once('academic_status.php');
		//$id=optional_param('id',5,PARAM_INT);
		//$events = get_semslist();
        $this->content->text = display_active_classes(); 
        return $this->content;
        // Prepare the footer for this block
        // No footer to display
        $this->content->footer = '';
		// Return the content object
        return $this->content;
    }

   
}
