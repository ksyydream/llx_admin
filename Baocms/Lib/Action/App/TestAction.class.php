<?php
class TestAction extends CommonAction{
    
    public function test() {
        $model='dianping';
        print_r($this->_CONFIG['attachs']);die;
    }
}