<?php
class block_managebatches extends block_base {
    public function init() {
        $this->title = get_string('pluginname', 'block_managebatches');
    }
  public function get_content() {
    if ($this->content !== null) {
      return $this->content;
    }
    global $CFG;
    $this->content         =  new stdClass;
    $this->content->text=array();
    $icon = html_writer::empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/navigationitem.png'));
    $this->content->text[]=html_writer::tag('a',$icon.get_string('addbatch','block_managebatches'),array('href' =>$CFG->wwwroot.'/admin/index.php?cache=1'));
    $this->content->text[]=html_writer::empty_tag('br');
    $this->content->text[]=html_writer::tag('a',$icon.get_string('assignbatch','block_managebatches'), array('href' =>$CFG->wwwroot.'/admin/index.php?cache=1'));
    $this->content->text[]=html_writer::empty_tag('br');
    $this->content->text[]=html_writer::tag('a',$icon.get_string('enrolbatch','block_managebatches'), array('href' => $CFG->wwwroot.'/admin/index.php?cache=1')); 
    $this->content->text[]=html_writer::empty_tag('br');
    $this->content->text[]=html_writer::tag('a',$icon.get_string('reports','block_managebatches') , array('href' => $CFG->wwwroot.'/admin/index.php?cache=1')); 
    
    $this->content->text=implode('',$this->content->text);                                                                                                                                                       
    return $this->content;
  }
}