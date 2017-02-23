<?php


class ShopAction extends CommonAction
{

    public function index()
    {
        $this->display();
    }

    public function logo()
    {
        if ($this->isPost()) {
            $logo = $this->_post('logo', 'htmlspecialchars');
            if (empty($logo)) {
                $this->baoError('请上传商铺LOGO');
            }
            if (!isImage($logo)) {
                $this->baoError('商铺LOGO格式不正确');
            }
            $data = array('shop_id' => $this->shop_id, 'logo' => $logo);
            if (D('Shop')->save($data)) {
                $this->baoSuccess('上传LOGO成功！', U('shop/logo'));
            }
            $this->baoError('更新LOGO失败');
        } else {
            $this->display();
        }
    }

    public function image()
    {
        if ($this->isPost()) {
            $photo = $this->_post('photo', 'htmlspecialchars');
            if (empty($photo)) {
                $this->baoError('请上传商铺形象照');
            }
            if (!isImage($photo)) {
                $this->baoError('商铺形象照格式不正确');
            }
            $data = array('shop_id' => $this->shop_id, 'photo' => $photo);
            if (false !== D('Shop')->save($data)) {
                $this->baoSuccess('上传形象照成功！', U('shop/image'));
            }
            $this->baoError('更新形象照失败');
        } else {
            $this->display();
        }
    }


    public function about()
    {
        if ($this->isPost()) {
            $data = $this->checkFields($this->_post('data', false), array('addr', 'contact', 'near', 'business_time', 'delivery_time'));
            $data['addr'] = htmlspecialchars($data['addr']);
            if (empty($data['addr'])) {
                $this->baoError('店铺地址不能为空');
            }
            $data['contact'] = htmlspecialchars($data['contact']);
            $data['near'] = htmlspecialchars($data['near']);
            $data['business_time'] = htmlspecialchars($data['business_time']);
            $data['shop_id'] = $this->shop_id;
            $data['delivery_time'] = (int)$data['delivery_time'];
            $details = $this->_post('details', 'SecurityEditorHtml');
            if ($words = D('Sensitive')->checkWords($details)) {
                $this->baoError('商家介绍含有敏感词：' . $words);
            }
            $ex = array(
                'details' => $details,
                'near' => $data['near'],
                'business_time' => $data['business_time'],
                'delivery_time' => $data['delivery_time'],
            );
            unset($data['business_time'], $data['near'], $data['delivery_time']);
            if (false !== D('Shop')->save($data)) {
                D('Shopdetails')->upDetails($this->shop_id, $ex);
                $this->baoSuccess('操作成功', U('shop/about'));
            }
            $this->baoError('操作失败');
        } else {

            $this->assign('ex', D('Shopdetails')->find($this->shop_id));
            $this->display();
        }
    }

    //其他设置
    public function service()
    {
        $obj = D('Shop');
        if (!$detail = $obj->find($this->shop_id)) {
            $this->baoError('请选择要编辑的商家');
        }
        if ($detail['shop_id'] != $this->shop_id) {
            $this->baoError('请不要非法操作');
        }

        if ($this->isPost()) {
//            $data = $this->checkFields($this->_post('data', false), array('apiKey', 'mKey', 'partner', 'machine_code','service'));
//            $data['apiKey'] = htmlspecialchars($data['apiKey']);
//            $data['mKey'] = htmlspecialchars($data['mKey']);
//            $data['partner'] = htmlspecialchars($data['partner']);
//			$data['machine_code'] = htmlspecialchars($data['machine_code']);
//			$data['service'] = ($data['service']);
            $data['yhk1'] = $this->_post('yhk1');
            $data['yhk2'] = $this->_post('yhk2');
            $data['fx_1'] = $this->_post('fx1');
            $data['fx_2'] = $this->_post('fx2');
            $data['fx_3'] = $this->_post('fx3');
            if ($data['fx_2'] == 0 && $data['fx_3']>0) {
                $this->baoError('请不要非法操作1');
            }
            if (($data['fx_1']+$data['fx_2']+$data['fx_3'])>100) {
                $this->baoError('请不要非法操作2');
            }
            if ($data['yhk1'] > 100 || $data['yhk1'] < 0 || $data['yhk2'] > 100 || $data['yhk2'] < 0) {
                $this->baoError('请正确输入优惠券抵扣规则');
            }
            $data['shop_id'] = $this->shop_id;
            if (false !== $obj->save($data)) {
                $this->baoSuccess('更新成功', U('shop/service'));
            }
            $this->baoError('操作失败');
        } else {
            $this->assign('detail', $detail);
            $this->display();
        }
    }

    //购买短信
    public function sms()
    {

        $sms_shop_money = $this->_CONFIG['sms_shop']['sms_shop_money'];//单价
        $sms_shop_small = $this->_CONFIG['sms_shop']['sms_shop_small'];//最少购买多少条
        $sms_shop_big = $this->_CONFIG['sms_shop']['sms_shop_big'];//最大购买多少条
        $nums = D('Smsshop')->where(array('type' => shop, 'shop_id' => $this->shop_id))->find();

        if (IS_POST) {
            $num = (int)($_POST['num']);
            if ($num <= 0) {
                $this->baoError('购买数量不合法');
            }

            if ($num % 100 != 0) {
                $this->baoError('总需人次必须为100的倍数');
            }

            if ($num < $sms_shop_small) {
                $this->baoError('购买短信数量不得小于' . $sms_shop_small . '条');
            }

            if ($num > $sms_shop_big) {
                $this->baoError('购买短信数量不得大于' . $sms_shop_big . '条');
            }

            if ($nums['num'] >= 1000) {
                $this->baoError('您当前还有' . $nums['num'] . '条短信，用完再来买吧');
            }

            $money = $num * ($sms_shop_money * 100);//总金额

            if ($money > $this->member['money'] || $this->member['money'] == 0) {
                $this->baoError('你的余额不足，请先充值');
            }

            if (D('Users')->addMoney($this->uid, -$money, '商户购买短信：' . $num . '条')) {
                if (empty($nums)) {//如果以前没有购买过
                    $data['user_id'] = $this->uid;
                    $data['shop_id'] = $this->shop_id;
                    $data['type'] = shop;
                    $data['num'] = $num;
                    $data['create_time'] = NOW_TIME;
                    $data['create_ip'] = get_client_ip();
                    D('Smsshop')->add($data);
                } else {
                    D('Smsshop')->where(array('log_id' => $nums['log_id']))->setInc('num', $num);  // 增加短信
                }
                $this->baoSuccess('购买短信成功', U('shop/sms'));
            } else {
                $this->baoError('购买错误，没有付款成功！');
            }

        } else {
            $this->assign('sms_shop_money', $sms_shop_money);
            $this->assign('sms_shop_small', $sms_shop_small);
            $this->assign('sms_shop_big', $sms_shop_big);
            $this->assign('nums', $nums);
            $this->display();
        }
    }


}
