<?php

class FansAction extends CommonAction {

	public function index() {
		$fans = D('Shopfavorites'); //实例化fans模型
		import('ORG.Util.Page'); // 导入分页类
		$map = array('shop_id' => $this->shop_id); //查询条件	
		if ($keyword = $this->_post('keyword', 'htmlspecialchars')) {
			$maps['nickname|mobile'] = trim($keyword);
			$Users = D('Users');
			$user = $Users->where($maps)->find();
			if (!empty($user)) {
				$map['user_id'] = $user['user_id'];
			}
			$this->assign('keyword', $keyword);
		}
		$count = $fans->where($map)->count(); // 查询满足要求的总记录数 
		$Page = new Page($count, 3); // 实例化分页类 传入总记录数和每页显示的记录数
		$show = $Page->show(); // 分页显示输出
		$list = $fans->where($map)->order(array('favorites_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
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

		$sql = "SELECT a.*,b.uid FROM `bao_user_parent` a LEFT JOIN `bao_connect` b ON a.openid=b.open_id WHERE parent like '{$match_key}'";
		$rs = D('Users')->query($sql);
		$rs_n = array();
		$users_map = array();
		foreach($rs as $k=>$v){
			$parent = (array)json_decode($v['parent']);
			$parent_n = array();
			foreach($parent as $kk=>$vv){
				$parent_n[$kk] = $vv;
			}
			if($v['uid']){
				$rs_n[$v['uid']] = $parent_n;
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
		$this->display(); // 输出模板
	}
}
