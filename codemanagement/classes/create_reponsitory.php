<?php
defined('MOODLE_INTERNAL') || die();
require_once 'create_reponsitory_form.php';
class mod_codemanagement_classes_create_responsitory{
    protected  $installfromzipform=null;
    protected  $id;//courses module id
    protected  $c;//module id
    
    public  function setId($id){
        $this->id=$id;
    }
    public function setC($c){
        $this->c=$c;
    }
    
    protected function __construct() {
        
    }
    
    public static function instance(){
        return new static();
    }
    
    public function index_url(array $params = null) {
        return new moodle_url('/mod/codemanagement/view.php', $params);
    }
    
    public function get_installfromzip_form() {
        if (!is_null($this->installfromzipform)) {
            return $this->installfromzipform;
        }
        
        $action = $this->index_url(array('id'=>$this->id,'c'=>$this->c));
        $customdata = array('installer' => $this);
        
        $this->installfromzipform = new mod_codemanagement_classes_create_responsitory_form($action, $customdata);
        
        return $this->installfromzipform;
    }
}