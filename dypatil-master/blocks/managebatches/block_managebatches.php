<?php
class block_managebatches extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_managebatches');
    }
    public function get_content() {
    global $CFG,$USER;    
    
    if ($this->content !== null) {
      return $this->content;
    }   
    if(!isloggedin()){
        return $this->content;
    }
    
    $this->content =  new stdClass;
    $systemcontext = context_system::instance();

    $usercontext = context_user::instance($USER->id);
      

    if(has_capability('local/gradeletter:manage', $usercontext) || is_siteadmin() ){		 
    $this->content->text=array();
    $icon = html_writer::empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/navigationitem.png'));
    $this->content->text[]=html_writer::tag('a',$icon.get_string('addbatch','block_managebatches'),array('href' =>$CFG->wwwroot.'/local/batches/index.php'));
    //$this->content->text[]=html_writer::empty_tag('br');
    $this->content->text[]=html_writer::tag('a',$icon.get_string('assignbatch','block_managebatches'), array('href' =>$CFG->wwwroot.'/local/batches/bulk_enroll.php?mode=new'));
    //$this->content->text[]=html_writer::empty_tag('br');
    $this->content->text[]=html_writer::tag('a',$icon.get_string('enrolbatch','block_managebatches'), array('href' => $CFG->wwwroot.'/local/batches/bulk_enroll.php?mode=exists')); 
    //$this->content->text[]=html_writer::empty_tag('br');
 //   $this->content->text[]=html_writer::tag('a',$icon.get_string('reports','block_managebatches') , array('href' => $CFG->wwwroot.'/admin/index.php?cache=1')); 
    
    $this->content->text=implode('',$this->content->text);
     return $this->content;
    }    
    
    else
     return $this->content;    
   
  }
}