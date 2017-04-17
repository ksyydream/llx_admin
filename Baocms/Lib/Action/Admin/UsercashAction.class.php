<?php



class UsercashAction extends CommonAction {

    public function index() {
        $Userscash = D('Userscash');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('type'=>user);
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $row) {
            $ids[] = $row['user_id'];
        }
        $Usersex = D('Usersex');
        $map = array();
        $map['user_id'] = array('in', $ids);
        $ex = $Usersex->where($map)->select();
        $tmp = array();
        foreach ($ex as $row) {
            $tmp[$row['user_id']] = $row;
        }
        foreach ($list as $key => $row) {
            $list[$key]['bank_name'] =  empty($list[$key]['bank_name']) ? $tmp[$row['user_id']]['bank_name'] :$list[$key]['bank_name'];
            $list[$key]['bank_num'] =  empty($list[$key]['bank_num']) ? $tmp[$row['user_id']]['bank_num'] :$list[$key]['bank_num'];
            $list[$key]['bank_branch'] =  empty($list[$key]['bank_branch']) ? $tmp[$row['user_id']]['bank_branch'] :$list[$key]['bank_branch'];
            $list[$key]['bank_realname'] =  empty($list[$key]['bank_realname']) ? $tmp[$row['user_id']]['bank_realname'] :$list[$key]['bank_realname'];
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function expUser(){//导出Excel
        $type=$_GET['type'];
        $map = array('type'=>$type);
        $list = D('Userscash')->where($map)->select();
        $title=array();
        $bt=$type=='shop'?'商家':'用户';
        $title[0]=array('拉拉秀'.$bt.'提现报表');
        $title[1]=array('序号','银行','帐号','户主','金额','提现编号'); //设置要导出excel的表头
        $i=1;
        foreach($list as $key => $v){
            $row[$i]['i'] = $i;
            $row[$i]['bank_name'] =$list[$key]['bank_name'];
            $row[$i]['bank_num'] = $list[$key]['bank_num'];
            $row[$i]['bank_realname'] = $list[$key]['bank_realname'];
            $row[$i]['money'] = $v['money'] / 100;
            $row[$i]['tid'] = $v['cash_id'];
            $i++;
        }
        $this->exportExcel($row, time(), $title);
        
        
        /*import("ORG.Yufan.Excel");
        //$map = array('type'=>user);
        $list = D('Userscash')->where($map)->select();
        $row=array();
        $row[0]=array('序号','银行','帐号','户主','金额','提现编号');
        $i=1;
        foreach($list as $key => $v){
            $row[$i]['i'] = $i;
            $row[$i]['bank_name'] =$list[$key]['bank_name'];
            $row[$i]['bank_num'] = $list[$key]['bank_num'];
            $row[$i]['bank_realname'] = $list[$key]['bank_realname'];
            $row[$i]['money'] = $v['money'] / 100;
            $row[$i]['tid'] = 1;
            $i++;
        }
        //$this->baoError(var_dump($row));
        $xls = new \Excel_XML('UTF-8', false, 'datalist');
        $xls->getActiveSheet()->mergeCells('A1:D1');
        $xls->addArray($row);
        $xls->generateXML("yufan956932910");*/
    
    }
    
    function exportExcel($data, $savefile = null, $title = null, $sheetname = 'sheet1') {
        import("ORG.PHPExcel.PHPExcel");
        //若没有指定文件名则为当前时间戳
        if (is_null($savefile)) {
            $savefile = time();
        }
        //若指字了excel表头，则把表单追加到正文内容前面去
        if (is_array($title)) {
            array_unshift($data, $title[1]);
            array_unshift($data, $title[0]);
        }
        $objPHPExcel = new \PHPExcel();
        //Excel内容
        $head_num = count($data);
        foreach ($data as $k => $v) {
            $obj = $objPHPExcel->setActiveSheetIndex(0);
            $row = $k + 1; //行
            $nn = 0;
    
            foreach ($v as $vv) {
                $col = chr(65 + $nn); //列
                $obj->setCellValue($col . $row, $vv); //列,行,值
                $nn++;
            }
        }
        //设置列头标题
        for ($i = 0; $i < $head_num - 1; $i++) {
            $alpha = chr(65 + $i);
            //$objPHPExcel->getActiveSheet()->getColumnDimension($alpha)->setAutoSize(true); //单元宽度自适应
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setName("Candara");  //设置字体
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setSize(12);  //设置大小
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_BLACK); //设置颜色
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER); //水平居中
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER); //垂直居中
            $objPHPExcel->getActiveSheet()->getStyle($alpha . '1')->getFont()->setBold(true); //加粗
        }
        $objPHPExcel->getActiveSheet()->mergeCells('A1:F1');
        $objPHPExcel->getActiveSheet()->setTitle($sheetname); //题目
        $objPHPExcel->setActiveSheetIndex(0); //设置当前的sheet
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $savefile . '.xls"'); //文件名称
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007'); //Excel5 Excel2007
        $objWriter->save('php://output');
    }
    
    public function integral(){
        $Userscash = D('Userscash1');
        import('ORG.Util.Page'); // 导入分页类
        //$map = array('type'=>'shop');
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $row) {
            $ids[] = $row['user_id'];
        }
        $Usersex = D('Usersex');
        $map = array();
        $map['user_id'] = array('in', $ids);
        $ex = $Usersex->where($map)->select();
        $tmp = array();
        foreach ($ex as $row) {
            $tmp[$row['user_id']] = $row;
        }
        foreach ($list as $key => $row) {
            $list[$key]['bank_name'] =  empty($list[$key]['bank_name']) ? $tmp[$row['user_id']]['bank_name'] :$list[$key]['bank_name'];
            $list[$key]['bank_num'] =  empty($list[$key]['bank_num']) ? $tmp[$row['user_id']]['bank_num'] :$list[$key]['bank_num'];
            $list[$key]['bank_branch'] =  empty($list[$key]['bank_branch']) ? $tmp[$row['user_id']]['bank_branch'] :$list[$key]['bank_branch'];
            $list[$key]['bank_realname'] =  empty($list[$key]['bank_realname']) ? $tmp[$row['user_id']]['bank_realname'] :$list[$key]['bank_realname'];
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }


	public function gold() {
        $Userscash = D('Userscash');
        import('ORG.Util.Page'); // 导入分页类
        $map = array('type' => shop);
        if ($account = $this->_param('account', 'htmlspecialchars')) {
            $map['account'] = array('LIKE', '%' . $account . '%');
            $this->assign('account', $account);
        }
        $count = $Userscash->where($map)->count(); // 查询满足要求的总记录数 
        $Page = new Page($count, 25); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $Userscash->where($map)->order(array('cash_id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        $ids = array();
        foreach ($list as $row) {
            $ids[] = $row['user_id'];
        }
        $Usersex = D('Usersex');
        $map = array();
        $map['user_id'] = array('in', $ids);
        $ex = $Usersex->where($map)->select();
        $tmp = array();
        foreach ($ex as $row) {
            $tmp[$row['user_id']] = $row;
        }
        foreach ($list as $key => $row) {
            $list[$key]['bank_name'] =  empty($list[$key]['bank_name']) ? $tmp[$row['user_id']]['bank_name'] :$list[$key]['bank_name'];
            $list[$key]['bank_num'] =  empty($list[$key]['bank_num']) ? $tmp[$row['user_id']]['bank_num'] :$list[$key]['bank_num'];
            $list[$key]['bank_branch'] =  empty($list[$key]['bank_branch']) ? $tmp[$row['user_id']]['bank_branch'] :$list[$key]['bank_branch'];
            $list[$key]['bank_realname'] =  empty($list[$key]['bank_realname']) ? $tmp[$row['user_id']]['bank_realname'] :$list[$key]['bank_realname'];
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function audit($cash_id = 0, $status = 0) {
        if (!$status)
            $this->baoError('参数错误');
        $Userscash = D('Userscash');
        if (is_numeric($cash_id) && ($cash_id = (int) $cash_id)) {
            $data = $Userscash->find($cash_id);
            if ($data['status'] == 0) {
                $arr = array();
                $arr['cash_id'] = $cash_id;
                $arr['status'] = $status;
                $Userscash->save($arr);

                //微信通知
                $this->remainMoneyNotify($data['user_id'],$data['money']);
                $this->baoSuccess('操作成功！', U('usercash/index'));
            }
            else
            {
                $this->baoError('请不要重复操作');
            }
        } else {
            $cash_id = $this->_post('cash_id', FALSE);
            if (!is_array($cash_id)) {
                $this->baoError('请选择要审核的提现');
            }
            foreach ($cash_id as $id) {
                $data = $Userscash->find($id);
                if ($data['status'] > 0) {
                    continue;
                }
                $arr = array();
                $arr['cash_id'] = $id;
                $arr['status'] = $status;
                $Userscash->save($arr);
                //微信通知
                $this->remainMoneyNotify($data['user_id'],$data['money']);
                //如果是拒绝则返还钱
            }
            $this->baoSuccess('操作成功！', U('usercash/index'));
        }
    }

    public function audit_integral($cash_id = 0, $status = 0) {
        if (!$status)
            $this->baoError('参数错误');
        $Userscash = D('Userscash1');
        if (is_numeric($cash_id) && ($cash_id = (int) $cash_id)) {
            $data = $Userscash->find($cash_id);
            if ($data['status'] == 0) {
                $arr = array();
                $arr['cash_id'] = $cash_id;
                $arr['status'] = $status;
                $Userscash->save($arr);

                //微信通知
                $this->remainMoneyNotify($data['user_id'],$data['money']);
                $this->baoSuccess('操作成功！', U('usercash/integral'));
            }
            else
            {
                $this->baoError('请不要重复操作');
            }
        } else {
            $cash_id = $this->_post('cash_id', FALSE);
            if (!is_array($cash_id)) {
                $this->baoError('请选择要审核的提现');
            }
            foreach ($cash_id as $id) {
                $data = $Userscash->find($id);
                if ($data['status'] > 0) {
                    continue;
                }
                $arr = array();
                $arr['cash_id'] = $id;
                $arr['status'] = $status;
                $Userscash->save($arr);
                //微信通知
                $this->remainMoneyNotify($data['user_id'],$data['money']);
                //如果是拒绝则返还钱
            }
            $this->baoSuccess('操作成功！', U('usercash/integral'));
        }
    }


	//商户提现
	 public function audit_gold($cash_id = 0, $status = 0) {
        if (!$status)
            $this->baoError('参数错误');
        $Userscash = D('Userscash');
        if ($cash_id = (int) $cash_id){
            $data = $Userscash->find($cash_id);
            if ($data['status'] == 0) {
                $arr = array();
                $arr['cash_id'] = $cash_id;
                $arr['status'] = $status;
                $Userscash->save($arr);
                //微信通知
                $this->remainMoneyNotify($data['user_id'],$data['money']);
                $this->baoSuccess('操作成功！', U('usercash/gold'));
            }
            else{
                $this->baoError('操作失败');
            }
       
        }
    }

	//拒绝用户提现
    public function jujue() {
        $status = (int) $_POST['status'];
        $cash_id = (int)$_POST['cash_id'];
        $value = $this->_param('value', 'htmlspecialchars');
        if(empty($value)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'拒绝理由请填写'));
		}
        if(empty($cash_id)|| !$detail = D('Userscash')->find($cash_id)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'参数错误'));
        }
        $money = $detail['money'];
        if($status == 2){
            D('Users')->addMoney($detail['user_id'], $money, '提现拒绝，退款');
            D('Userscash')->save(array('cash_id'=>$cash_id,'status'=>$status,'reason'=>$value));
			//微信通知
            $this->remainMoneyNotify($data['user_id'],$data['money']);
            $this->ajaxReturn(array('status'=>'success','msg'=>'拒绝退款操作成功','url'=>U('usercash/index')));
        }
    }

    public function jujue_integral() {
        $status = (int) $_POST['status'];
        $cash_id = (int)$_POST['cash_id'];
        $value = $this->_param('value', 'htmlspecialchars');
        if(empty($value)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'拒绝理由请填写'));
        }
        if(empty($cash_id)|| !$detail = D('Userscash1')->find($cash_id)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'参数错误'));
        }
        $money = $detail['money'];
        if($status == 2){
            D('Users')->addIntegral($detail['user_id'], $money, '提现拒绝，退秀币');
            D('Userscash1')->save(array('cash_id'=>$cash_id,'status'=>$status,'reason'=>$value));
            //微信通知
            $this->remainMoneyNotify($data['user_id'],$data['money']);
            $this->ajaxReturn(array('status'=>'success','msg'=>'拒绝退款操作成功','url'=>U('usercash/integral')));
        }
    }

	//拒绝商家提现
	 public function jujue_gold() {
        $status = (int) $_POST['status'];
        $cash_id = (int)$_POST['cash_id'];
        $value = $this->_param('value', 'htmlspecialchars');
        if(empty($value)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'拒绝理由请填写'));
		}
        if(empty($cash_id)|| !$detail = D('Userscash')->find($cash_id)){
            $this->ajaxReturn(array('status'=>'error','msg'=>'参数错误'));
        }
        $money = $detail['money'];
        if($status == 2){
            D('Users')->Money($detail['user_id'], $money, '提现拒绝，退款');
            D('Userscash')->save(array('cash_id'=>$cash_id,'status'=>$status,'reason'=>$value));
			//微信通知
            $this->remainMoneyNotify($data['user_id'],$data['money']);
            $this->ajaxReturn(array('status'=>'success','msg'=>'拒绝退款操作成功','url'=>U('usercash/gold')));
        }
    }
	
	
    

    //微信余额通知
    private function remainMoneyNotify($uid,$money)
    {
        //余额变动,微信通知
        $openid    = D('Connect')->getFieldByUid($uid,'open_id'); 
        $order_id  = $order['order_id'];
        $uname = D('User')->getFieldByUser_id($uid,'nickname');
        $words     = "您申请的提现金额{$money}请求已通过,请注意您的账户变动！";
        if($openid){
            $template_id = D('Weixintmpl')->getFieldByTmpl_id(4,'template_id');//余额变动模板
            $tmpl_data =  array(
                'touser'      => $openid,//用户微信openid
                'url'         => 'http://'.$_SERVER['HTTP_HOST'].'/mcenter',//相对应的订单详情页地址
                'template_id' => $template_id,
                'topcolor'    => '#2FBDAA',
                'data'        => array(
                    'first'=>array('value'=>'尊敬的用户,{$uname},您的账户余额有变动！' ,'color'=>'#2FBDAA'),   
                    'keynote1'=>array('value'=> $user_name, 'color'=>'#2FBDAA'),//用户名
                    'keynote2'=>array('value'=> $words, 'color'=>'#2FBDAA'),//详情
                    'remark'  =>array('value'=>'详情请登录您的用户中心了解', 'color'=>'#2FBDAA')
                )
            );
            D('Weixin')->tmplmesg($tmpl_data);
        }
    }


   
}
