<?php



class XlkAction extends CommonAction {
    private $create_fields = array('shop_id', 'title', 'yhq', 'valid_time', 'flag','card_count');
    private $edit_fields = array('valid_time', 'flag');

    public function getmlist(){
        $xlxmodel = D('Xlkmaster');
        import('ORG.Util.Page'); // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['a.title'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $count = $xlxmodel->alias('a')
            ->join('bao_shop c on c.shop_id = a.shop_id','LEFT')
            ->where($map)->count(); // 查询满足要求的总记录数
        //die(var_dump($count));
        //$Page = new Page($count, 2,U('Admin/Xlk/getmlist','','')); // 实例化分页类 传入总记录数和每页显示的记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        //die(var_dump($Page));
        $list = $xlxmodel->alias('a')->field('a.*,c.shop_name')
            ->join('bao_shop c on c.shop_id = a.shop_id','LEFT')
            ->where($map)
            ->order(array('a.id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        foreach ($list as $k => $val) {
            $zengpins = D('Xlkzp')->where(array('master_id'=>$val['id']))->select();
            foreach ($zengpins as $a => $v){
                $list[$k]['zp'][]=array('desc'=>$v['desc'],'qty'=>$v['qty']);
            }
        }
        
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }

    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Xlkmaster');
            $master_id = $obj->add($data);
            if ($master_id) {
                $arr_desc = $this->_post('desc');
                $arr_qty = $this->_post('qty');
                if($arr_desc){
                    if(is_array($arr_desc)){
                        foreach($arr_desc as $key => $val) {
                            if($val){
                                $zp_arr = array(
                                    'desc' => $val,
                                    'qty' => $arr_qty[$key]?$arr_qty[$key]:0,
                                    'master_id'=>$master_id
                                );
                                D('Xlkzp')->add($zp_arr);
                            }

                        }
                    }
                }
                for ($x=1; $x<=$data['card_count']; $x++) {
                    D("Xlkdetail")->add(array(
                       'master_id'=>$master_id,
                        'xlh'=>$this->get_xlh()
                    ));
                }
                $this->baoSuccess('添加成功', U('Xlk/getmlist'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->display();
        }
    }

    private function createCheck() {

        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['shop_id'] = (int) $data['shop_id'];
        if (empty($data['shop_id'])) {
            $this->baoError('商家不能为空');
        }
        $shop = D('Shop')->find($data['shop_id']);
        if (empty($shop)) {
            $this->baoError('请选择正确的商家');
        }

        $data['title'] = htmlspecialchars($data['title']);
        if (empty($data['title'])) {
            $this->baoError('标题不能为空');
        } $data['photo'] = htmlspecialchars($data['photo']);
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['card_count'] = (int) $data['card_count'];
        //$data['valid_time'] = $this->_post('valid_time');
        $data['yhq'] = (int) $data['yhq'];
        if($data['card_count'] <= 0){
            $this->baoError('数量需要大于0');
        }
        if($data['yhq'] <= 0){
            $this->baoError('优惠券需要大于0');
        }
        return $data;
    }

    public function get_xlh($length=24){
        $time = time();
        $id_len = strlen((string)$time);

        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;
        for($i=0;$i<5;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        $str .= $time;
        for($i=0;$i<$length-$id_len-5;$i++){
            $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }
        $check = D("xiudetail")->where(array('xlh'=>$str))->find();
        if(!$check){
            return $str;
        }else{
            return $this->get_xlh();
        }

    }

    public function edit(){
        $xlkdmodel = D('Xlkdetail');
        import('ORG.Util.Page'); // 导入分页类
        if ($master_id = $this->_param('id')) {
            $map['a.master_id'] = $master_id;
            $this->assign('id', $master_id);
        }
        $count = $xlkdmodel->alias('a')
            ->join('bao_users c on c.user_id = a.uid','LEFT')
            ->where($map)->count();
        $Page = new Page($count, 100); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出
        $list = $xlkdmodel->alias('a')->field('a.*,c.nickname,c.account')
            ->join('bao_users c on c.user_id = a.uid','LEFT')
            ->where($map)
            ->order(array('a.id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();

        $master = D('Xlkmaster')->alias('a')->field('a.*,c.shop_name')
            ->join('bao_shop c on c.shop_id = a.shop_id','LEFT')
            ->where(array('id'=>$master_id))
            ->find();
        $zengpins = D('Xlkzp')->where(array('master_id'=>$master_id))->select();
        foreach ($zengpins as $a => $v){
            $master['zp'][]=array('desc'=>$v['desc'],'qty'=>$v['qty']);
        }
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->assign('master',$master);
        $this->display(); // 输出模板
    }

    public function xlk_save(){
        //die(var_dump($this->_post()));
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
       // die(var_dump($data));
        D('Xlkmaster')->where(array('id'=>$this->_post('id')))->save($data);
        $this->baoSuccess('修改成功', U('Xlk/getmlist'));
    }

    public function load_excel(){//导出Excel
        $master_id=$_GET['id'];
        $map = array('a.master_id'=>$master_id);
        $list = D('Xlkdetail')->alias('a')->field('a.*,c.nickname,c.account')
            ->join('bao_users c on c.user_id = a.uid','LEFT')
            ->where($map)
            ->select();
        $master = D('Xlkmaster')->alias('a')->field('a.*,c.shop_name')
            ->join('bao_shop c on c.shop_id = a.shop_id','LEFT')
            ->where(array('id'=>$master_id))
            ->find();
        $zengpins = D('Xlkzp')->where(array('master_id'=>$master_id))->select();
        $str_zp = '';
        foreach ($zengpins as $a => $v){
            $str_zp.=$v['desc'].":".$v['qty']." ";
           // $master['zp'][]=array('desc'=>$v['desc'],'qty'=>$v['qty']);
        }
        $title[0]=array('拉拉秀优惠卡序列号报表--'.$master['title']);
        $title[1]=array('优惠券:'.$master['yhq'].' 有效期:'.$master['valid_time'].' 序列卡数量:'.$master['card_count'].' 赠品:'.$str_zp);
        $title[2]=array('序号','序列号','使用时间','使用账户编号','使用账号昵称','账号手机号码','状态'); //设置要导出excel的表头
        $i=1;
        foreach($list as $key => $v){
            $row[$i]['i'] = $i;
            $row[$i]['xlh'] = $v['xlh'];
            $row[$i]['used_time'] = $v['used_time'];
            $row[$i]['uid'] = $v['uid'];
            $row[$i]['nickname'] = $v['nickname'];
            $row[$i]['account'] = $v['account'];
            if($v['flag']==1){
                $row[$i]['flag'] = '可以使用';
            }
            if($v['flag']==2){
                $row[$i]['flag'] = '已被使用';
            }
            if($v['flag']==-1){
                $row[$i]['flag'] = '作废';
            }
            $i++;
        }
        $this->exportExcel($row, time(), $title);

    }

    function exportExcel($data, $savefile = null, $title = null, $sheetname = 'sheet1') {
        import("ORG.PHPExcel.PHPExcel");
        //若没有指定文件名则为当前时间戳
        if (is_null($savefile)) {
            $savefile = time();
        }
        //若指字了excel表头，则把表单追加到正文内容前面去
        if (is_array($title)) {
            array_unshift($data, $title[2]);
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
}
