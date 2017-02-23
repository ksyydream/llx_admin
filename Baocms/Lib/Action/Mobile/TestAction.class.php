<?php
class TestAction extends CommonAction{
    
    public function index(){
        $this->display();
    }
        public function ck(){
            $data=$this->_post('data');
            //$data = $this->checkFields($this->_post('data', false), $this->create_fields);
            $data['area_code'] = (int) $data['area_code'];
            if ($data['area_code']){
                echo 1;
            }else {echo 2;}
        }
}