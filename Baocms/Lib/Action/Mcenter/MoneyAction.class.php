<?php
class MoneyAction extends CommonAction
{
    public function index()
    {
        //余额充值
        $this->assign('payment', D('Payment')->getPayments(true));
        $this->display();
    }


    public function sendsms()
    {
        $mobile = $this->_post('mobile');
        if (isMobile($mobile)) {
            session('mobile', $mobile);
            $randstring = session('cash_code', 100);
            if (empty($randstring)) {
                $randstring = rand_string(6, 1);
                session('cash_code', $randstring);
            }
            //大鱼短信
            if ($this->_CONFIG['sms']['dxapi'] == 'dy') {
                D('Sms')->DySms($this->_CONFIG['site']['sitename'], 'sms_yzm', $mobile, array(
                    'sitename' => $this->_CONFIG['site']['sitename'],
                    'code' => $randstring
                ));
            } else {
                D('Sms')->sendSms('sms_code', $mobile, array('code' => $randstring));
            }

        }
    }

    public function cashlogs()
    {
        $map = array('user_id' => $this->uid, 'type' => user);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $Userscash = D('Userscash');
        import('ORG.Util.Page'); // 导入分页类
        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 16); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display();


    }



    public function detail()
    {
        $map = array('user_id' => $this->uid);
        if (($bg_date = $this->_param('bg_date', 'htmlspecialchars')) && ($end_date = $this->_param('end_date', 'htmlspecialchars'))) {
            $bg_time = strtotime($bg_date);
            $end_time = strtotime($end_date);
            $map['create_time'] = array(array('ELT', $end_time), array('EGT', $bg_time));
            $this->assign('bg_date', $bg_date);
            $this->assign('end_date', $end_date);
        } else {
            if ($bg_date = $this->_param('bg_date', 'htmlspecialchars')) {
                $bg_time = strtotime($bg_date);
                $this->assign('bg_date', $bg_date);
                $map['create_time'] = array('EGT', $bg_time);
            }
            if ($end_date = $this->_param('end_date', 'htmlspecialchars')) {
                $end_time = strtotime($end_date);
                $this->assign('end_date', $end_date);
                $map['create_time'] = array('ELT', $end_time);
            }
        }
        $Usermoneylogs = D('Usergoldlogs');
        import('ORG.Util.Page');
        $count = $Usermoneylogs->where($map)->count();
        $Page = new Page($count, 16);
        $show = $Page->show();


        $var = C('VAR_PAGE') ? C('VAR_PAGE') : 'p';
        $p = $_GET[$var];
        if ($Page->totalPages < $p) {
            die('0');
        }


        $list = $Usermoneylogs->where($map)->order(array('log_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $this->assign('list', $list);
        $this->assign('page', $show);
        $this->display();
    }


    public function cash()
    {
        $user_id = $this->uid;
        $userscash = D('Userscash')->where(array('user_id' => $user_id))->find();;
        $shop = D('Shop')->where(array('user_id' => $user_id))->find();
        if ($shop == '') {
            $cash_money = $this->_CONFIG['cash']['user'];
            $cash_money_big = $this->_CONFIG['cash']['user_big'];
        } elseif ($shop['is_renzheng'] == 0) {
            $cash_money = $this->_CONFIG['cash']['shop'];
            $cash_money_big = $this->_CONFIG['cash']['shop_big'];
        } elseif ($shop['is_renzheng'] == 1) {
            $cash_money = $this->_CONFIG['cash']['renzheng_shop'];
            $cash_money_big = $this->_CONFIG['cash']['renzheng_shop_big'];
        } else {
            $cash_money = $this->_CONFIG['cash']['user'];
            $cash_money_big = $this->_CONFIG['cash']['user_big'];
        }

        //对比手机号码，验证码
//        $shop = D('Shop')->where(array('shop_id' => $this->shop_id))->find();
        $users = D('Users')->where(array('user_id' => $user_id))->find();
        $s_mobile = session('mobile');
        $cash_code = session('cash_code');//获取life_code

        if (IS_POST) {
            $gold = (int)($_POST['gold'] * 100);
            if ($gold <= 0) {
                $this->fengmiMsg('提现金额不合法');
            }
            if ($gold < $cash_money * 100) {
                $this->fengmiMsg('提现金额小于最低提现额度');
            }
            if ($gold > $cash_money_big * 100) {
                $this->fengmiMsg('您单笔最多能提现' . $cash_money_big . '元');
            }

            if ($gold > $this->member['gold'] || $this->member['gold'] == 0) {
                $this->fengmiMsg('资金不足，无法提现');
            }
            if (!$data['bank_name'] = htmlspecialchars($_POST['bank_name'])) {
                $this->fengmiMsg('开户行不能为空');
            }
            if (!$data['bank_num'] = htmlspecialchars($_POST['bank_num'])) {
                $this->fengmiMsg('银行账号不能为空');
            }

            if (!$data['bank_realname'] = htmlspecialchars($_POST['bank_realname'])) {
                $this->fengmiMsg('开户姓名不能为空');
            }

            //获取手机，验证码
            $yzm = $this->_post('yzm');
            $s_mobile = session('mobile');
            $cash_code = session('cash_code');

            if (empty($yzm)) {
                $this->fengmiMsg('请输入短信验证码');
            }

//            if ($users['mobile'] != $s_mobile) {
//                $this->fengmiMsg('手机号码和收取验证码的手机号不一致！');
//            }
//            if ($yzm != $cash_code) {
//                $this->fengmiMsg('短信验证码不正确');
//            }

            $data['bank_branch'] = htmlspecialchars($_POST['bank_branch']);
            $data['user_id'] = $this->uid;

            $arr = array();
            $arr['user_id'] = $this->uid;
//            $arr['shop_id'] = $this->shop_id;//提现商家
//            $arr['city_id'] = $shop['city_id'];
//            $arr['area_id'] = $shop['area_id'];
            $arr['money'] = $gold;
            $arr['type'] = user;
            $arr['addtime'] = NOW_TIME;
            $arr['account'] = $this->member['account'];
            $arr['bank_name'] = $data['bank_name'];
            $arr['bank_num'] = $data['bank_num'];
            $arr['bank_realname'] = $data['bank_realname'];
            $arr['bank_branch'] = $data['bank_branch'];

            D("Userscash")->add($arr);

            D('Usersex')->save($data);
            D('Users')->Money($this->uid, -$gold, '用户申请提现，扣款');
            $this->fengmiMsg('申请提现成功', U('money/detail'));
        } else {
            $this->assign('info', D('Usersex')->getUserex($this->uid));
            $this->assign('gold', $this->member['gold']);
            $this->assign('cash_money', $cash_money);
            $this->assign('cash_money_big', $cash_money_big);
            $this->assign('userscash', $userscash);
            $this->display();
        }
    }









    public function moneypay()
    {
        //后期优化
        $money = (int) ($this->_post('money') * 100);
        $code = $this->_post('code', 'htmlspecialchars');
        if ($money <= 0) {
            $this->error('请填写正确的充值金额！');
        }
        if ($money > 1000000) {
            $this->error('每次充值金额不能大于1万');
        }
        $payment = D('Payment')->checkPayment($code);
        if (empty($payment)) {
            $this->error('该支付方式不存在');
        }
        $logs = array('user_id' => $this->uid, 'type' => 'money', 'code' => $code, 'order_id' => 0, 'need_pay' => $money, 'create_time' => NOW_TIME, 'create_ip' => get_client_ip());
        $logs['log_id'] = D('Paymentlogs')->add($logs);
        $this->assign('button', D('Payment')->getCode($logs));
        $this->assign('money', $money);
        $this->assign('logs', $logs);
        $this->display();
    }
    public function recharge()
    {
        //代金券充值
        if ($this->isPost()) {
            $card_key = $this->_post('card_key', htmlspecialchars);
            if (!D('Lock')->lock($this->uid)) {
                //上锁
                $this->fengmiMsg('服务器繁忙，1分钟后再试');
            }
            if (empty($card_key)) {
                D('Lock')->unlock();
                $this->fengmiMsg('充值卡号不能为空');
            }
            if (!($detail = D('Rechargecard')->where(array('card_key' => $card_key))->find())) {
                D('Lock')->unlock();
                $this->fengmiMsg('该充值卡不存在');
            }
            if ($detail['is_used'] == 1) {
                D('Lock')->unlock();
                $this->fengmiMsg('该充值卡已经使用过了');
            }
            $member = D('Users')->find($this->uid);
            $member['money'] += $detail['value'];
            if (D('Users')->save(array('user_id' => $this->uid, 'money' => $member['money']))) {
                D('Usermoneylogs')->add(array('user_id' => $this->uid, 'money' => +$detail['value'], 'create_time' => NOW_TIME, 'create_ip' => get_client_ip(), 'intro' => '代金券充值' . $detail['card_id']));
                $res = D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'is_used' => 1));
                if (!empty($res)) {
                    D('Rechargecard')->save(array('card_id' => $detail['card_id'], 'user_id' => $this->uid, 'used_time' => NOW_TIME));
                }
                $this->fengmiMsg('充值成功！', U('money/recharge'));
            }
            D('Lock')->unlock();
        } else {
            $this->display();
        }
    }
}