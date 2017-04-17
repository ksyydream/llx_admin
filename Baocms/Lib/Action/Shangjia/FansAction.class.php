<?php

class FansAction extends CommonAction {

	public function index() {
	    import('ORG.Util.Page');
	    $fans = D('Scf');
	    //实例化fans模型
	    $map = array(
	        'b.shop_id' => $this->shop_id
	    );
	    //查询条件
	    if ($keyword = $this->_post('keyword', 'htmlspecialchars')) {
	        $map['c.nickname|c.mobile'] = array('like','%'.trim($keyword).'%');
	    }
	    
	    $count = $fans->alias('a')->group('a.uid')->field('a.*')
	    ->join('bao_shop_fd b on a.fd_id = b.fd_id','LEFT')
	    ->join('bao_users c on c.user_id = a.uid','LEFT')
	    ->where($map)->count();
	    // 查询满足要求的总记录数
	    $Page = new Page($count,15);
	    $show = $Page->show();
	    $list = $fans->alias('a')->group('a.uid')->field('a.*,b.shop_id,c.user_id,c.nickname username,c.account mobile,c.face')
	    ->join('bao_shop_fd b on a.fd_id = b.fd_id','LEFT')
	    ->join('bao_users c on c.user_id = a.uid','LEFT')
	    ->where($map)
	    ->order(array('a.scf_id' => 'desc'))
	    ->limit($Page->firstRow . ',' . $Page->listRows)
	    ->select();
	    
	    $user_ids = array();
		foreach ($list as $k => $val) {
			if ($val['user_id']) {
				$user_ids[$val['user_id']] = $val['user_id'];
			}
		}
		if ($user_ids) {
			$this->assign('users', D('Users')->itemsByIds($user_ids));
		}
		$this->assign('list', $list); // 赋值数据集
		$this->assign('page', $show); // 赋值分页输出
		$this->display(); // 输出模板
	}

	public function add($user_id=0) {
		$fans=D('Shopfavorites');
		$uid=(int)($user_id);
		$user = D('Users')->find($user_id);
		$shop=D('shop')->find($this->shop_id);
		if ($this->isPost()){
			$integral=(int)($_POST['integral']);
			if($integral <= 0){
				$this->baoError('请输入正确的积分');
			}
			if($this->member['integral'] < $integral){
				$this->baoError('您的账户积分不足');
			}
			D('Users')->addIntegral($this->uid,-$integral,'赠送会员积分');
			D('Users')->addIntegral($user_id,$integral,'获得商家赠送积分');
			$this->baoSuccess('赠送积分成功!',U('fans/add',array('user_id'=>$user_id)));
		} else {
			$this->assign('shop', $shop);
			$this->assign('jifen',$this->member['integral']);
			$this->assign('user', $user);
			$this->display();
		}
	}
	
	public function yhk_users(){
	    $match_key = '%"'.$this->shop_id.'":%';
	    $map['yhk'] = array('like',$match_key);
	     
	    if ($keyword = $this->_post('keyword', 'htmlspecialchars')) {
	        $map['nickname|mobile'] = array('like','%'.trim($keyword).'%');
	        $this->assign('keyword', $keyword);
	    }
	     
	    $users = D('Users')->where($map)->select();
	    foreach($users as $k=>$v){
	
	        $yhk = (array)json_decode($v['yhk']);
	        $yhk_n = array();
	        foreach($yhk as $kk=>$vv){
	            $yhk_n[$kk] = $vv;
	        }
	         
	        $zp = (array)json_decode($v['zp']);
	        $zp_n = array();
	        foreach($zp as $kk=>$vv){
	            $zp_n[$kk] = (array)$vv;
	        }
	         
	        $users[$k]['yhk'] = $yhk_n[$this->shop_id];
	        $users[$k]['zp'] = $zp_n[$this->shop_id];
	    }
	     
	    $sql = "SELECT a.*,b.user_id FROM `bao_user_parent` a LEFT JOIN `bao_users` b ON a.mobile = b.account WHERE a.parent like '{$match_key}'";
	    $rs = D('Users')->query($sql);
	    $rs_n = array();
	    $users_map = array();
	    foreach($rs as $k=>$v){
	        $parent = (array)json_decode($v['parent']);
	        $parent_n = array();
	        foreach($parent as $kk=>$vv){
	            $parent_n[$kk] = $vv;
	        }
	        if($v['user_id']){
	            $rs_n[$v['user_id']] = $parent_n;
	        }
	         
	    }
	     
	    foreach($users as $k=>$v){
	        $users_map[$v['user_id']] = array(
	            'nickname'=>$v['nickname'],
	            'mobile'=>$v['mobile'],
	        );
	    }
	    $this->assign('parent_users', $rs_n);
	    $this->assign('list', $users);
	    $this->assign('users_map', $users_map);
	    $this->assign('shop_id', $this->shop_id);
	    $this->display();
	}
	
}
