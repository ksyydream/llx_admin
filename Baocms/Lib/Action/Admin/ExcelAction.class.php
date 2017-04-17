<?php



class ExcelAction extends CommonAction {

    /**方法**/
    function  index(){
        $this->display();
    }
    public function exportExcel(){
        $list = M("user")->field("id,username,password")->order("id DESC")->limit(50)->select(); 
$title = array('ID', '用户名', '密码'); //设置要导出excel的表头 
$this->expUser($list, '素材火', $title);
$this->display();
    }
    /**
     *
     * 导出Excel
     */
    public function expUser(){//导出Excel
       import("ORG.Yufan.Excel");
	$list = D('Userscash')->select();;
	$row=array();
	$row[0]=array('序号','银行','帐号','户主','金额','提现编号');
	$i=1;
	foreach($list as $key=>$v){
	    $row[$i]['i'] = $i;
	    $row[$i]['uid'] = $v['id'].'1';
	    $row[$i]['username'] = $list[$key]['bank_realname'].'2';
	    $row[$i]['money'] = $v['money'].'3';
	    $row[$i]['tid'] =1 ;
	    $row[$i]['tid'] =1 ;
	    $i++;
	}
	if (is_null($savefile)) {
	    $savefile = time();
	}
	$xls = new \Excel_XML('UTF-8', false, 'datalist');
	$xls->addArray($row);
	$xls->generateXML($savefile);

}
}