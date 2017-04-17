<?php



class TuijianAction extends CommonAction {
    private $create_fields = array('photo','flag','url','sid','fid','gid','gg_id');
    
public function index() {
    $guanggao=D('Guanggao');
    $detail=D('Guanggao')->order('gg_id')->select();
    $data = $this->checkFields($this->_post('data', false), $this->create_fields);

    if ($this->isPost()) {
           if (empty($data['photo'])) {
               $this->baoError('请上传商铺LOGO');
           }
           if (!isImage($data['photo'])) {
               $this->baoError('商铺LOGO格式不正确');
           }
           if (empty($data['flag'])||$data['flag']==0) {
               $this->baoError('未选择连接分类');
           }
           if ($data['flag']==1&&empty($data['gid'])) {
               $this->baoError('未填写连接商品地址');
           }
           if ($data['flag']==2&&empty($data['fid'])) {
               $this->baoError('未填写连接分店');
           }
           if ($data['flag']==3&&empty($data['gid'])) {
               $this->baoError('未填写连接商品地址');
           }
           $fd=D('Shopfd')->where(array('fd_id'=>$data['fid']))->find();
           $data['sid']=$fd['shop_id'];
           if ($guanggao->where(array('gg_id'=>$data['gg_id']))->find()){
               $guanggao->where(array('gg_id'=>$data['gg_id']))->save($data);
           }else {
           $guanggao->add($data);}
    $this->baoSuccess('操作成功',U('Tuijian/index'));
       $this->display();  
    }else {
        $this->assign('detail',$detail);
        $this->display();
        }
    }
}
