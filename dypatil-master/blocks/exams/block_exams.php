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
 * @subpackage blog_menu
 * @copyright  2009 Nicolas Connault
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * The blog menu block class
 */
class block_exams extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_exams');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }
    //
    //function applicable_formats() {
    //    return array('all' => true, 'my' => false, 'tag' => false);
    //}

    function instance_allow_config() {
        return true;
    }

    function get_content() {
        global $CFG;
		if ($this->content !== NULL) {
            return $this->content;
        }
		if(!isloggedin()){
			return $this->content;
		}
		//if(is_siteadmin()){
		//	return $this->content;
		//}
		//if(has_capability('local_collegestructure:manage', context_system::instance())){
		//	return $this->content;
		//}

      
        // Prep the content 
        $this->content = new stdClass();
        	if(!isloggedin() || isguestuser() ){
              $this->content = '';
    }else{
        require_once('exams.php');
		$string=get_exams();
		$this->content->text = $string;
        return $this->content;
        // Prepare the footer for this block
        // No footer to display
        $this->content->footer = '';
		// Return the content object
        return $this->content;
    }
    }

   
}
