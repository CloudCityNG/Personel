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
class block_aboutus extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_aboutus');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }
    
    function instance_allow_config() {
        return true;
    }

    function get_content() {
        global $CFG;
		if ($this->content !== NULL) {
            return $this->content;
        }
		if(isloggedin()){
			return $this->content;
		}
        // Prep the content 
        require_once('aboutus.php');
        $string = about_university();
        $this->content->text = $string;
        return $this->content;
        // Prepare the footer for this block
        // No footer to display
        $this->content->footer = '';
        // Return the content object
        return $this->content;
}

   
}
