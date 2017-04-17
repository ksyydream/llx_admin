<?php



class XiuAction extends CommonAction {
    public function userxiu(){
        $xiumodel = D('Xiuuser');
        import('ORG.Util.Page'); // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['a.uid'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $map['a.closed'] = 0;
        $map['a.flag']=1;
        $count = $xiumodel->alias('a')->where($map)->count(); // 查询满足要求的总记录数
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $xiumodel->alias('a')->field('a.*,b.nickname')->where($map)
            ->join('bao_users b on a.uid = b.user_id','LEFT')->order(array('a.id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
    foreach ($list as $k => $val) {
            $xiuuserf = D('Xiuuserfile');
            $files=$xiuuserf->where(array('master_id' => $val['id']))
                ->order(array('id' => 'asc'))
                ->select();
            $list[$k]['files']=array();           
            foreach ($files as $a => $v){
                if(file_exists(BASE_PATH.'/attachs/'.$v['path'])){
                    $list[$k]['files'][]=array('path'=>$v['path'],'flag'=>$v['flag']);
                }
            }
        }
        
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    }
    public function shopxiu(){
        $xiumodel = D('Xiuuser');
        import('ORG.Util.Page'); // 导入分页类
        if ($keyword = $this->_param('keyword', 'htmlspecialchars')) {
            $map['a.shop_id'] = array('LIKE', '%' . $keyword . '%');
            $this->assign('keyword', $keyword);
        }
        $map['a.closed'] = 0;
        $map['a.flag']=2;
        $count = $xiumodel->alias('a')->where($map)->count(); // 查询满足要求的总记录数
        echo $xiumodel->getLastSql();
        $Page = new Page($count, 10); // 实例化分页类 传入总记录数和每页显示的记录数
        $show = $Page->show(); // 分页显示输出

        $list = $xiumodel->alias('a')->field('a.*,b.shop_name,b.shop_id')->where($map)
            ->join('bao_shop b on a.shop_id = b.shop_id','LEFT')->order(array('a.id' => 'desc'))->limit($Page->firstRow . ',' . $Page->listRows)->select();
        
    foreach ($list as $k => $val) {
            $xiuuserf = D('Xiuuserfile');
            $files=$xiuuserf->where(array('master_id' => $val['id']))
                ->order(array('id' => 'asc'))
                ->select();
            $list[$k]['files']=array();           
            foreach ($files as $a => $v){
                if(file_exists(BASE_PATH.'/attachs/'.$v['path'])){
                    $list[$k]['files'][]=array('path'=>$v['path'],'flag'=>$v['flag']);
                }
            }
        }
        
        $this->assign('list', $list); // 赋值数据集
        $this->assign('page', $show); // 赋值分页输出
        $this->display(); // 输出模板
    
    }
    
    public function delete($id=0){
        $xiumodel = D('Xiuuser');
        $xiuuserf = D('Xiuuserfile');
        if (is_numeric($id) && ($id = (int) $id)) {
            $xiumodel->where(array('id' => $id))->delete();
            $this->baoSuccess('删除成功！', U('xiu/userxiu'));}
        $this->baoError('请选择要删除的秀');
    }
    
    
    
}
