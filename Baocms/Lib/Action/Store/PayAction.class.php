<?php


class PayAction extends CommonAction
{

    private $create_fields = array('shop_id', 'photo', 'name', 'zhucehao', 'addr', 'end_date', 'zuzhidaima', 'user_name', 'pic', 'mobile', 'audit');
    private $edit_fields = array('shop_id', 'photo', 'name', 'zhucehao', 'addr', 'end_date', 'zuzhidaima', 'user_name', 'pic', 'mobile', 'audit');


    public function index()
    {
//        $this->compute_yhk('15051691989', '', array('啤酒' => 10, '烤鸭' => 2));
        $Pay = D('Pay');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('shop_id' => $this->shop_id);
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['mobile'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $Pay->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Pay->where($map)->order(array('id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $v) {
            $list[$k]['zp'] = (array)json_decode($v['zp']);
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    //获取用户赠品
    public function get_zp($mobile)
    {
        if (!isMobile($mobile)) {
            echo -1;
            exit();
        }
        $Users = D('Users');
        $user = $Users->where(array('mobile' => $mobile))->find();
        echo '{"zp":' . $user['zp'] . ',"yhk":' . $user['yhk'] . '}';
    }

    public function create()
    {
        if ($this->_post()) {
            $Users = D('Users');
            $Pay = D('Pay');

            $post = $this->_post();
            $data = $post['data'];
            $desc = $post['desc'];
            $qty = $post['qty'];
            $zp = array();
            foreach ($desc as $k => $v) {
                if ($qty[$k] > 0) {
                    $zp[$v] = $qty[$k];
                }
            }
            if ($zp) {
                $data['zp'] = json_encode($zp);
            }
            $data['shop_id'] = $this->shop_id;
            $data['remark'] = htmlspecialchars($data['remark']);
            $data['status'] = 1;
            $data['create_time'] = NOW_TIME;

            $user_count = $Users->where(array('mobile' => $data['mobile']))->count();
            if ($user_count == 0) {
                $this->fengmiMsg('会员不存在');
            }
            $pay_count = $Pay->where(array('mobile' => $data['mobile'], 'status' => 1))->count();

            if ($pay_count > 0) {
                $this->fengmiMsg('该会员存在未付款的订单,无法新建支付');
            }

            if ($data['total'] <= 0) {
                $this->fengmiMsg('消费金额必须大于0');
            }

            if($data['yhk'] >= $data['total']){//满100抵扣100优惠券
                $data['status'] = 2;
                $data['pay_time'] = NOW_TIME;
            }
            if (false !== $Pay->add($data)) {
                $zp_array = array();
                foreach($desc as $k=>$v){
                    $zp_array[$v] = $qty[$k];
                }
                if($data['yhk'] >= $data['total']){
                    $this->compute_yhk($data['mobile'],$data['yhk'],$zp_array);
                }
                $this->fengmiMsg('操作成功', U('pay/index'));
            }
        } else {
            $this->assign('shop_id', $this->shop_id);
            $this->assign('yhk1', $this->yhk1);
            $this->assign('yhk2', $this->yhk2);
            $this->display();
        }
    }

    private function compute_yhk($mobile, $yhk = '', $zp = '')
    {
        $Users = D('Users');
        $Pay = D('Pay');
        $Yhk_log = D('Yhklog');
        $Zengpin_log = D('Zengpinlog');
        $user = $Users->where(array('mobile' => $mobile))->find();


        if ($yhk) {//优惠卡规则
            $yhk_limit_old = (array)json_decode($user['yhk']);
            $yhk_limit = array();
            foreach ($yhk_limit_old as $key => $val) {
                $yhk_limit[$key] = $val;
            }

            if (isset($yhk_limit[$this->shop_id]) && $yhk_limit[$this->shop_id] > 0) {
                if ($yhk_limit[$this->shop_id] < $yhk) {
                    $this->error('优惠券余额不足');
                } else {
                    $yhk_limit[$this->shop_id] = $yhk_limit[$this->shop_id] - $yhk;
                }
            }else{
                $yhk_surplus = $yhk;
                foreach($yhk_limit as $k=>$v){
                    if($yhk_surplus > 0){
                        if($v < $yhk_surplus){
                            $yhk_surplus = $yhk_surplus - $v;
                            $yhk_limit[$k] = 0;
                        }else{
                            $yhk_limit[$k] = $v - $yhk_surplus;
                            $yhk_surplus = 0;
                        }
                    }
                }
                if($yhk_surplus > 0){
                    $this->error('优惠券余额不足');
                }
            }

            $data = array(
                'mobile' => $mobile,
                'qty' => $yhk,
                'create_time' => NOW_TIME,
                'shop_id' => $this->shop_id,
                'type' => -1
            );
            $Yhk_log->add($data);
            $Users->where(array('mobile' => $mobile))->save(array('yhk' => json_encode($yhk_limit)));
        }

        if ($zp) {
            $zp_limit = json_decode($user['zp']);
            $key = (string)$this->shop_id;
            $data = array();
            if ($zp_limit->$key) {
                foreach ($zp as $k => $v) {
                    if ($zp_limit->$key->$k) {
                        if ($zp_limit->$key->$k < $v) {
                            $this->error('赠品数量不足');
                        } else {
                            $zp_limit->$key->$k = $zp_limit->$key->$k - $v;
                            $data[] = array(
                                'mobile' => $mobile,
                                'qty' => $v,
                                'create_time' => NOW_TIME,
                                'shop_id' => $this->shop_id,
                                'type' => -1,
                                'desc' => $k
                            );
                        }
                    }
                }

                foreach($data as $k=>$v){
                    $Zengpin_log->add($v);
                }
                $Users->where(array('mobile' => $mobile))->save(array('zp' => json_encode($zp_limit)));
            } else {
                $this->error('赠品有误,请查看');
            }
        }

    }


    public function edit($audit_id = 0)
    {
        if ($audit_id = (int)$audit_id) {
            $obj = D('Audit');
            if (!$detail = $obj->find($audit_id)) {
                $this->error('请选择要编辑的商家认证');
            }
            if ($detail['shop_id'] != $this->shop_id) {
                $this->error('请不要操作别人的认证');
            }
            if ($detail['closed'] != 0) {
                $this->error('该认证已被删除');
            }
            if ($this->isPost()) {
                $photo = $this->_post('photo', false);
                if (count($photo) == 0) {
                    $this->fengmiMsg('请上传营业执照，可以用手机拍照上传！');
                }
                if (!isImage($photo['0'])) {
                    $this->fengmiMsg('所上传的营业执照格式不正确！');
                }
                $pic = $this->_post('pic', false);
                if (count($pic) == 0) {
                    $this->fengmiMsg('请上传员工身份证，可以用手机拍照上传！');
                }
                if (!isImage($pic['0'])) {
                    $this->fengmiMsg('所上传员工身份证格式不正确！');
                }

                $data = $this->editCheck();
                $data['audit_id'] = $audit_id;
                $data['photo'] = $photo['0'];
                $data['pic'] = $pic['0'];
                if (false !== $obj->save($data)) {
                    $this->fengmiMsg('编辑操作成功', U('audit/index'));
                }
                $this->fengmiMsg('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->assign('shop', D('Shop')->find($detail['shop_id']));
                $this->display();
            }
        } else {
            $this->error('请选择要编辑的商家认证1');
        }
    }

    private function editCheck()
    {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['audit_id'] = (int)$data['audit_id'];
        $data['shop_id'] = $this->shop_id;

        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->fengmiMsg('企业名称不能为空');
        }


        $data['zhucehao'] = htmlspecialchars($data['zhucehao']);
        if (empty($data['zhucehao'])) {
            $this->fengmiMsg('营业执照注册号不能为空');
        }

        $data['addr'] = htmlspecialchars($data['addr']);
        if (empty($data['addr'])) {
            $this->fengmiMsg('营业地址不能为空');
        }

        $data['end_date'] = htmlspecialchars($data['end_date']);
        if (empty($data['end_date'])) {
            $this->fengmiMsg('到期时间不能为空');
        }
        if (!isDate($data['end_date'])) {
            $this->fengmiMsg('到期时间格式不正确');
        }

        $data['zuzhidaima'] = htmlspecialchars($data['zuzhidaima']);
        if (empty($data['zuzhidaima'])) {
            $this->fengmiMsg('组织机构代码证为空');
        }

        $data['user_name'] = htmlspecialchars($data['user_name']);
        if (empty($data['user_name'])) {
            $this->fengmiMsg('员工姓名为空');
        }


        $data['mobile'] = htmlspecialchars($data['mobile']);
        if (empty($data['mobile'])) {
            $this->fengmiMsg('员工手机不能为空');

        }
        if (!isMobile($data['mobile'])) {
            $this->fengmiMsg('员工手机格式不正确');

        }
        return $data;
    }


    public function delete($id = 0){
        if (empty($id)) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'访问错误！'));
        }
        $pay = D('Pay')->find($id);
        if (empty($pay) || $pay['status'] != 1) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'非法操作'));
        }
        if ($pay['shop_id'] != $this->shop_id ) {
            $this->ajaxReturn(array('status'=>'error','msg'=>'您没有权限访问！'));
        }
        $obj = D('Pay');
        $obj->where(array('id' => $id))->delete();
        $this->ajaxReturn(array('status'=>'success','msg'=>'删除成功', U('pay/index')));
    }


    public function pay($id)
    {
        $Pay = D('Pay');
        $Shop = D('Shop');
        $rs = $Pay->query("select a.*,shop_name from ".$Pay->getTableName()." a left join ".$Shop->getTableName()." b on a.shop_id = b.shop_id where a.id = ".$id);
        if(empty($rs)){
            $this->fengmiMsg('不存在该笔付款');
        }
        $detail = $rs[0];
        $detail['zp'] = (array)json_decode($rs[0]['zp']);
        $member = D('Users')->where(array('mobile'=>$detail['mobile']))->find();
        $this->assign('detail', $detail);
        $this->assign('integral', $member['integral']);
        $this->display(); // 输出模板
    }

    public function check_pay(){

        $id = $this->_post('id');
        $integral = $this->_post('integral');
        $Pay = D('Pay');
        $Users = D('Users');
        $rs = $Pay->where(array('id'=>$id))->find();
        if(empty($rs)){
            $this->fengmiMsg('不存在该笔付款');
        }
        if($rs['status'] != 1){
            $this->fengmiMsg('请勿重复支付');
        }
        $member = D('Users')->where(array('mobile'=>$rs['mobile']))->find();
//        if($rs['mobile'] != $member['mobile']){
//            $this->fengmiMsg('权限不足');
//        }
        if ($rs['shop_id'] != $this->shop_id ) {
            $this->fengmiMsg('权限不足');
        }

        if($integral < 0 || $member['integral'] < $integral || $integral > ($rs['total'] - $rs['yhk'])*100){
            $this->fengmiMsg('秀币输入错误');
        }

        $Shop = D('Shop');
        $shop = $Shop->where(array('shop_id'=>$this->shop_id))->find();

//        if($integral == ($rs['total'] - $rs['yhk'])*100){//全部用秀币抵扣,不涉及支付
        $zp = (array)json_decode($rs['zp']);
        $this->compute_yhk($member['mobile'],$rs['yhk'],$zp,$rs['shop_id']);
        $Pay->where(array('id'=>$id))->save(array('status'=>2,'integral'=>$integral,'pay_time'=>NOW_TIME,'is_offline'=>2));
        $Users->addIntegral($member['user_id'],-$integral,'优惠买单使用秀币');
        $Users->addIntegral($shop['user_id'],$integral,'客户优惠买单获得秀币');
        $this->fengmiMsg('支付成功',U('pay/index'));
        exit();

    }
}
