<?php

//
//
// This software is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// This Moodle block is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *
 * @since 2.0
 * @package blocks
 * @copyright 2012 Georg Maißer und David Bogner http://www.edulabs.org
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * The simple navigation tree block class
 *
 * Used to produce  University Structure block
 *
 * @package blocks
 * @copyright 2012 hemalatha arun <hemalatha@eabyas.in>
* @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
*/
class block_universitystructure extends block_base {

	/** @var int */
	public static $navcount;
	/** @var string */
	public $blockname = null;
	/** @var bool */
	protected $contentgenerated = false;
	/** @var bool|null */
	protected $docked = null;

	function init() {
		global $CFG;
		$this->blockname = get_class($this);
		$systemcontext =  context_system::instance();
	        $this->title = get_string('mytask', 'block_universitystructure');
                if(has_capability('local/collegestructure:manage', $systemcontext)){
                $this->title = get_string('pluginname', 'block_universitystructure');
                }			
	}

	/**
	 * All multiple instances of this block
	 * @return bool Returns true
	 */
	function instance_allow_multiple() {
		return true;
	}

	/**
	 * Set the applicable formats for this block to all
	 * @return array
	 */
	function applicable_formats() {
		return array('all' => true);
	}
	
	function specialization() {
		        $systemcontext =  context_system::instance();
			$title_string= format_string(get_string('mytask', 'block_universitystructure'));
                        if(has_capability('local/collegestructure:manage', $systemcontext)){
			$title_string = format_string(get_string('pluginname', 'block_universitystructure'));
			}
		$this->title = isset($this->config->sn_blocktitle) ? format_string($this->config->sn_blocktitle) : $title_string;
		if($this->title == ''){		
			$this->title = format_string(get_string('pluginname', 'block_universitystructure'));
			
		}
	}
	/**
	 * Allow the user to configure a block instance
	 * @return bool Returns true
	 */
	function instance_allow_config() {
		return true;
	}

	/**
	 * The navigation block cannot be hidden by default as it is integral to
	 * the navigation of Moodle.
	 *
	 * @return false
	 */
	function  instance_can_be_hidden() {
		return true;
	}

	function instance_can_be_docked() {
		        return (!empty($this->title) && parent::instance_can_be_docked());

		//return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
	}

        	function get_required_javascript() {
		global $CFG;
		parent::get_required_javascript();
		user_preference_allow_ajax_update('docked_block_instance_'.$this->instance->id, PARAM_INT);
		$limit = 20;
		if (!empty($CFG->navcourselimit)) {
			$limit = $CFG->navcourselimit;
		}
		$expansionlimit = 0;
		if (!empty($this->config->expansionlimit)) {
			$expansionlimit = $this->config->expansionlimit;
		}
		$arguments = array(
				'id'             => $this->instance->id,
				'instance'       => $this->instance->id,
				'candock'        => $this->instance_can_be_docked(),
				'courselimit'    => $limit,
				'expansionlimit' => $expansionlimit
		);
		$this->page->requires->string_for_js('viewallcourses', 'moodle');
		$this->page->requires->yui_module(array('core_dock', 'moodle-block_navigation-navigation'), 'M.block_navigation.init_add_tree', array($arguments));
	}

	function universitystructure_collect_items ($myclass, $myid, $myname, $mydepth, $mytype, $mypath, $myicon, $myvisibility) {
		$item = array('myclass'=>$myclass, 'myid'=>$myid, 'myname'=>$myname, 'mydepth'=>$mydepth, 'mytype'=>$mytype, 'mypath'=>$mypath, 'myicon'=>$myicon, 'myvisibility'=>$myvisibility);

		return $item;
	}	
          

        
	function checking_capabilities($pluginname, $extracapabilities = null){		
           global $CFG, $OUTPUT,$DB, $USER;         
           $systemcontext =  context_system::instance();
           $cap = array('local/'.$pluginname .':manage',
			'local/'. $pluginname .':delete',
			'local/'. $pluginname .':create',
			'local/'. $pluginname .':update',
			'local/'. $pluginname .':visible',
			'local/'. $pluginname .':view');
	   if( $extracapabilities){
		$cap =$cap + $extracapabilities;
	   }
           if (has_any_capability($cap, $systemcontext)) {
		return true;
	   }
	   else
	        return false;
		
	}
	

	function get_content() {

	    global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	    $systemcontext =  context_system::instance();
		if(isloggedin())
	    $usercontext = context_user::instance($USER->id);
		$myopentag = '';
		$startcategories = array();
		$categories = array();

		if($this->content !== NULL) {
			return $this->content;
		}
		
		$this->content = new stdClass;
		$this->content->items = array();
		$this->content->icons = array();
		$this->content->footer = '';
		
		//some variables
		$content = array();
		$items = array();
		$active_module_course = null;
		$is_active = false;
		$mybranch = array();
		$icon ='';
		$topnodelevel = 0;
		$sn_home=null;
		
		$this->page->navigation->initialise();
		$navigation = clone($this->page->navigation);

		$renderer = $this->page->get_renderer('block_universitystructure');
		// $o =$renderer->get_menulist();
	       //     print_object($o);
                 if(isloggedin()){
		$this->content =  new stdClass;		
		$this->content->text   = $renderer->get_universitystructure_menulist();
		//$this->content->text   = $renderer->universitystructure_tree($items);
		// Set content generated to true so that we know it has been done
		$this->contentgenerated = true;
		return $this->content;
		 }
	}


}
