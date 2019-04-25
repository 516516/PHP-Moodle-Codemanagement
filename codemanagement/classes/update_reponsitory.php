<?php
defined('MOODLE_INTERNAL') || die();
require_once 'update_responsitory_form.php';
class mod_codemanagement_classes_update_responsitory{
    protected  $installfromzipform=null;
    protected  $id;//courses module id
    protected  $c;//module id
    protected  $responsitoryid;
    protected  $re_name;
    protected  $re_description;
    
    public  function setId($id){
        $this->id=$id;
    }
    
    public function setC($c){
        $this->c=$c;
    }
    
    public function setResponsitory($responsitoryid){
        $this->responsitoryid=$responsitoryid;
    }
    
    public function setReName($re_name){
        $this->re_name=$re_name;
    }
    public function getReName(){
        return $this->re_name;
    }
    
    public function setReDescrition($re_description){
        $this->re_description=$re_description;
    }
    public function getReDescrition(){
        return $this->re_description;
    }
    
    protected function __construct() {
        
    }
    
    public static function instance(){
        return new static();
    }
    
    public function index_url(array $params = null) {
        return new moodle_url('/mod/codemanagement/updateresponsitory.php', $params);
    }
    
    public function get_installfromzip_form() {
        if (!is_null($this->installfromzipform)) {
            return $this->installfromzipform;
        }
        
        $action = $this->index_url(array('id'=>$this->id,'c'=>$this->c,'reponsitoryid'=>$this->responsitoryid));
        
        
        $customdata = array('installer' => $this);
        
        $this->installfromzipform = new mod_codemanagement_classes_update_responsitory_form($action, $customdata);
        
        return $this->installfromzipform;
    }
}