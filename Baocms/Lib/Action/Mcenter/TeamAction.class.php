<?php
class TeamAction extends CommonAction {

	public function index() {

	    $logs = D('Paymentlogs')->find('27344');
	    $Goods = D('Goods');
	    $Order_goods = D('Ordergoods');
	    $goods = $Order_goods->query("select b.* from ".$Order_goods->getTableName()." a left join ".$Goods->getTableName()." b on a.goods_id = b.goods_id where a.order_id = ".$logs['order_id'] );
        
	    foreach($goods as $v){
	        $Userparent = D('Userparent');
	        $openid = D('Connect')->getFieldByUid($logs['user_id'],'open_id');
	        echo D('Connect')->getLastSql();
	        echo $logs['user_id'];
	        $users = D('Users');
	        $userparent = $Userparent->where(array('openid'=>$openid))->find();
	        print_r($userparent);
	        $parent_o = json_decode($userparent['parent']);
	        $fx=D('shop')->where(array('shop_id'=>$v['shop_id']))->find();
	        $fx1=$fx['fx_1']/100;
	        $fx2=$fx['fx_2']/100;
	        $fx3=$fx['fx_3']/100;
	    foreach($parent_o as $k=>$val){
	        $parent[$k] = $val;
	    }
	    $uid1 = $parent[$v['shop_id']];
	    echo $v['shop_id'];
	    print_r($parent);
	    D('Users')->Money($uid1, $v['mall_price']*$fx1, '第一层提成获得');
	    $rstt=D('Users')->getLastSql();
	    D('Test')->add(array('a'=>$rstt));
	    echo D('Users')->getLastSql();
	    }
	    
	    
        $shop_id = $_POST['shop_id'];
        $shop_ids = array();
        $member = $this->member;
        $yhk = (array)json_decode($member['yhk']);
        
        foreach($yhk as $k=>$v){
            $shop_ids[] = $k;
        }
        $map_shop['shop_id'] = array('in',$shop_ids);
        $shops = D('Shop')->where($map_shop)->select();
        $this->assign('shops', $shops);
        
        
        if(!$shop_id){
            $this->assign('team1_info', array());
            $this->assign('team2_info', array());
            $this->assign('team3_info', array());
            $this->display(); // 输出模板
            die();
        }

        $uid = $this->uid;

        $team1 = $this->get_low_users(array($uid),$shop_id);
        if(!empty($team1)){
            $team1_info = $this->get_user_info($team1);
            $team2 = $this->get_low_users($team1,$shop_id);
        }else{
            $team2 = array();
            $team1_info = array();
        }
        if(!empty($team2)){
            $team2_info = $this->get_user_info($team2);
            $team3 = $this->get_low_users($team2,$shop_id);
        }else{
            $team2_info = array();
        }

        if(!empty($team3)){
            $team3_info = $this->get_user_info($team3);
        }else{
            $team3_info = array();
        }
print_r($uid);
        
        $this->assign('team1_info', $team1_info);
        $this->assign('team2_info', $team2_info);
        $this->assign('team3_info', $team3_info);
        $this->assign('shop_id', $shop_id);
		$this->display(); // 输出模板
		echo $data_n;
	}



    public function get_low_users($users,$shop_id){
        $Userparent = D('Userparent');
        $sql = "SELECT a.*,b.uid FROM `bao_user_parent` a left join `bao_connect` b on a.openid = b.open_id";

        $data = $Userparent->query($sql);
        $data_n = array();
        foreach($data as $k=>$v){
            $parent_old = (array)json_decode($v['parent']);
            $parent = array();
            foreach($parent_old as $key=>$val){
                $parent[$key] = $val;
            }
            if(isset($parent[$shop_id])){
                foreach($users as $vv){
                    if($parent[$shop_id] == $vv){
                        $data_n[] = $data[$k]['uid'];
                    }
                }
            }
        }
        return $data_n;
    }

    public function get_user_info($users){
        $map['user_id']  = array('in',$users);
        $data = D('Users')->where($map)->select();
        return $data;
    }
  
}