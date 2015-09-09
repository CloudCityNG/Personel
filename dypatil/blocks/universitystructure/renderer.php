<?php
/**

 * Used to render the navigation items in the simple_nav block

 *

 */

require_once($CFG->dirroot . '/blocks/universitystructure/renderhelpers.php');

class block_universitystructure_renderer extends plugin_renderer_base {   

    
    
     public function universitystructure_tree($items) {
        $depth = 0;
        $type = 0;
	$nohome=0;
	 $lengthof_items =sizeof($items);
	//print_object($items);
        foreach ($items as $key=>$item) {
        	
        	//category and courses open <ul> tags. if they are empty, we have to make sure they're closed again.

			
			// We have to check if the category before was empty
        	if ($depth>=$item['mydepth'] && $type == 'u_root' && $item['mytype'] == 'u_root') {
        		$content[] = '</li></ul>';
        	}
			
			
        	//this is to know if we have to close the tags for the branch. We calculate the difference between the old and new branch and add the close tags accordingly
        	if ($depth>$item['mydepth']) {
        		// this is necessary to take care of the "startingpoint"-case, which suppresses the output of a category sometimes
        		if (substr($item['myclass'], -14, 14) == ' startingpoint') {
					$mydifference = $depth-$item['mydepth']-1;
				}
				else {
        			$mydifference = $depth-$item['mydepth'];
        		}
        		$content[] = str_repeat('</li></ul>',$mydifference).'</li>';
		
        	}
        	
				 // comparing is last element is root or leaf based on that .we have to close the tag accordingly
        	

        	
        	// Every time we the last item was a module and the new isn't, we can close <ul> as well as <li>
        	elseif ($item['mytype'] <> 'module' && $type == 'module') {
        		$content[] = '</li></ul>';
        	}
        	
        	
        	// if depth stays the same, we can just close the <li> tag
        	elseif ($depth==$item['mydepth']) {
        		$content[] = '</li>';
        	}
        	
        	//if the old item was course and the new is module, we have to open ul
        	if ($item['mytype'] == 'module' && ($type == 'u_leaf' || $type == 'invisiblecourse')) {
        		$content[] = '<ul>';
        	}
        	
		if( (($lengthof_items-1)== $items[$key]) && $type=="u_root" ){
                    $content[]='</li>';
                }
                else{
                    if((($lengthof_items-1)== $items[$key]) && $type=="u_leaf" )
                    $content='</li></ul></li>';
                    
                }
        	//print out html code for the item
        		$content[] = $this->universitystructure_sn_print_item($item['myclass'], $item['myid'], $item['myname'], $item['mydepth'], $item['mytype'], $item['mypath'], $item['myicon'], $item['myvisibility']);
        	// keep the information for the next loop
        	$depth = $item['mydepth'];
        	$myid = $item['myid'];
        	$type = $item['mytype'];

		
        }
	   
		$content[] = '</li></ul></li>';
		$content[] = '</ul></li></ul>'; 
		 $output=array_shift($content);
		$content = implode($content);

        return $content;
    }

    
      protected function universitystructure_sn_print_item($myclass, $myid, $myname, $mydepth, $mytype, $mypath, $myicon, $myvisibility) {
		global $CFG, $OUTPUT;
		$icon = '';
		$baseurl =$CFG->wwwroot;
		$mystartclass = "";
		if (! empty($this->config->space)) {
    		$space_symbol = $this->config->space;
		}		
		//if we don't want to show the first node, we use the class "startingpoint" as an indicator to totally skip it
		if (substr($myclass, -14, 14) == ' startingpoint') {
				return null;	
		}		
		// we only want the active branch to be open, all the other ones whould be collapsed
		$mycollapsed ='';
		// myclass only has a value when it's active		
		if (!$myclass) {
			$mycollapsed =' collapsed';
		}
		else {
			$mycollapsed ='';
		}
		
		 //sometimes, we don't show categories by simple setting their name to "". If this is the case, we want them not to be collapsed.
             //Here is a simple way to do so:
               //If the Name is empty, we also set the class, which controls the collapsed/uncollapsed status, to "".
           if (empty($myname)) {
        	$mycollapsed = "";        }
		

		// is it a category
		if ($mytype == 'u_root') {			
			//$myname = "";
//			$myclass_ul_open = '';
//			$myclass_li = 'type_category depth_'.$mydepth.''.$mycollapsed.' contains_branch'.$mystartclass;
//			if ($myvisibility==0)
//			$myclass_p = 'tree_item branch'.$myclass;
//			else
//			$myclass_p = 'tree_item leaf hasicon'.$myclass;
//                        $myopentag='';
//			if ($myvisibility == 0 ) {
//				$myclass_a = 'class="u_root_text"';
//                        ///it opens ul tags only when visibility is zero means ,its has child only        
//                                $myopentag = '<ul>';
//
//			}
//			else {
//				if($mypath && $myclass)			    
//			$myclass_a = 'id="us1_active"';
//			else
//			$myclass_a = '';
//			
//			}
			$myclass_ul_open = '';
			$myclass_li = 'type_category depth_'.$mydepth.''.$mycollapsed.' contains_branch'.$mystartclass;
			$myclass_p = 'tree_item branch'.$myclass;
			$myopentag = '<ul>';
			$myclass_a = '';
			if ($myvisibility == 0) {
				$myclass_a = 'class="usdimmed_text"';
			}
			else {
				$myclass_a = '';
			}

		}
		// is it a course
		elseif ($mytype == 'u_leaf') {
			// We don't want course-nodes to be open, even when they are active so:
			//$mycollapsed =' collapsed';
			//$myclass_ul_open = '';
			//$myclass_li = 'type_course depth_'.$mydepth.''.$mycollapsed.' contains_branch';;
			//$myclass_p = 'tree_item leaf  hasicon'.$myclass;
			//if($myclass)
			//$myclass_a = 'id="us1_active"';
			//else
			//$myclass_a = ' ';
			//$myopentag = '';
			
			$myurl =$CFG->wwwroot.'/course/view.php?id='.$myid;

			$myclass_ul_open = '';
			$myclass_li = 'type_course depth_'.$mydepth.''.$mycollapsed.' contains_branch';;
			
			if ($myid == 'nomodule') 
			$myclass_p = 'tree_item nomodule hasicon'.$myclass;
			else
			$myclass_p = 'tree_item branch hasicon'.$myclass;
			
			$myopentag = '';
			
			if ($myvisibility == 0) {
				$myclass_a = 'class="usdimmed_text"';
			}
			else {
				$myclass_a = '';
			}
			//if ($myvisibility == 0) {
			//	$myclass_a = 'class="u_root_text"';
			//}
			//else {
				//$myclass_a = '';
			//}		

		}
		
		// or invisible home node
		elseif ($mytype == 'nohome') {
			$myurl =$CFG->wwwroot;
			$myclass_ul_open = '<ul class="block_tree list">';
			$myclass_li = 'type_unknown depth_1 contains_branch simple_invisible';
			$myclass_p = 'tree_item branch '.$myclass.' navigation_node';
			$myopentag = '';
			$myclass_a = '';
		}
	       
	        	elseif ($mytype == 'module') {
			$myurl =$CFG->wwwroot.'/mod/'.$myicon.'/view.php?id='.$myid;
			$myclass_ul_open = '';
			$myclass_li = 'contains_branch item_with_icon';		
			$myclass_p = 'tree_item leaf hasicon'.$myclass;
			$myopentag = '';
			$myclass_a = '';
			
			if ($myvisibility == 0) {
				$myclass_a = 'class="dimmed_text"';
			}
			else {
				$myclass_a = '';
			}
			}
	        //---used to differentiate icons for the root  and leaf nodes
		    
		if(!empty($myicon)) {
		if($CFG->theme=='colms' || $CFG->theme=='slp')
		$icon = '<img id= "us1_block" class="smallicon navicon"  src="'.$baseurl.'/theme/'.$CFG->theme.'/'.$myicon.'.png">';
		}
		else  {
		$icon='<img  id= "us1_block_branchicon"  class="smallicon navicon" src="'.$baseurl.'/pix/i/navigationitem.png">';
		}
		
		
		// used to  remove href (refresh) attributes from the root node 
		if ($myvisibility == 0 ||  ($mytype=='u_leaf' && $myvisibility !=2) || $mytype=='u_root' ) {
                $myitem = $myclass_ul_open.'<li class="'.$myclass_li.'"><p class="'.$myclass_p.'" ><span '.$myclass_a.' >'.$icon.''.$myname.'</span>'.$myopentag;
		}
		else{
		    if($myid == 'add/edit')
		    $icon='<img    class="smallicon navicon" src= '.$OUTPUT->pix_url('t/edit').'>';
		$myitem = $myclass_ul_open.'<li class="'.$myclass_li.'"><p class="'.$myclass_p.'" ><a '.$myclass_a.' href="'.$mypath.'">'.$icon.''.$myname.'</a></p>'.$myopentag;
		
		}
		return $myitem;
	}
	
	    
    public function get_universitystructure_menulist() {
	global $CFG, $USER;
	 $systemcontext = context_system::instance();
	
	 $universityb =new block_universitystructure_menu(); 
	 $menulist = array_merge($universityb->settings_menu(),$universityb->gradesettings_menu(), $universityb->department_menu(),$universityb->program_menu(),$universityb->semester_menu(),$universityb->resourcemgt_menu(),$universityb->manageuser_menu() );
         $menulist=array_merge($universityb->nohome_menu() ,$menulist,$universityb->global_submenu());
	  if(has_capability('local/clclasses:submitgrades', $systemcontext)  &&  !is_siteadmin() )
	       $menulist=array_merge($universityb->nohome_menu() ,$universityb->instructor_menu(),$universityb->global_submenu());
	else if(has_capability('local/clclasses:approveclclasses', $systemcontext) && !is_siteadmin() )
          $menulist=array_merge($universityb->nohome_menu() ,$universityb->mentor_menu(),$universityb->global_submenu());
	 else {
	      if(isloggedin()){
	          $context = context_user::instance($USER->id);
	       if(has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin())
          $menulist=array_merge($universityb->nohome_menu() ,$universityb->student_menu(),$universityb->global_submenu());
	    } 
	  }
	  
	 
	//$response1 = $universityb->gradesettings_menu();
	//
	//$response += $universityb->department_menu();
	//
	//$response += $universityb->program_menu();
	//print_object($response);
	 $output = $this->universitystructure_tree($menulist);
	 return $output;

    }
	
   }
