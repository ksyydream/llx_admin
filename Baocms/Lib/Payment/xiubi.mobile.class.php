<?php



class xiubi{//秀币支付
    
    public function  getCode($logs,$setting=array()){
        
        return '<input type="button" class="payment" onclick="window.open(\''.U('member/pay/pay',array('logs_id'=>$logs['logs_id'])).'\')" value=" 立刻支付 " />';
    }

    public function respond(){
        
    }
    
}