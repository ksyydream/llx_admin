<?php



class YhkAction extends CommonAction {

    public function index() { 
        $user = D('Users')->find($this->uid);
        $yhk = (array)json_decode($user['yhk']);
        $yhk_n = array();
        $zp = (array)json_decode($user['zp']);
        $zp_n = array();
        foreach($zp as $k=>$v){
            $zp_n[$k] = (array)$v;
        }
        foreach($yhk as $k=>$v){
            $shop = D('Shop')->find($k);
            if($shop){
                if(isset($zp_n[$k])){
                    $yhk_n[] = array(
                        'shop_id'=>$shop['shop_id'],
                        'yhk'=>$v,
                        'shop_name'=>$shop['shop_name'],
                        'logo'=>$shop['logo'],
                        'zp'=>$zp_n[$k]
                    );
                }else{
                    $yhk_n[] = array(
                        'shop_id'=>$shop['shop_id'],
                        'yhk'=>$v,
                        'shop_name'=>$shop['shop_name'],
                        'logo'=>$shop['logo']
                    );
                }

            }
        }
        $this->assign('yhk', $yhk_n);
        $this->display(); // 输出模板
    }

    public function share($shop_id){
        $uid = $this->uid;
        $this->assign('uid', $uid);
        $this->assign('shop_id', $shop_id);
        $this->display(); // 输出模板
    }
    

	
	
    
}