<?php



class PresentAction extends CommonAction {
    private $create_fields = array('name','price', 'img','status','get_price');
    private $edit_fields = array('name','price', 'img','status','get_price');
    public function index(){
        $Present = D('Present');
        $list = $Present->select();
        $this->assign('list', $list); // 赋值数据集
        $this->display(); // 输出模板
        
    }
    
    public function create() {
        if ($this->isPost()) {
            $data = $this->createCheck();
            $obj = D('Present');
            if ($obj->add($data)) {
                $this->baoSuccess('添加成功', U('present/index'));
            }
            $this->baoError('操作失败！');
        } else {
            $this->assign('parent_id',$parent_id);
            $this->display();
        }
    }
    
    private function createCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->create_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('礼物名称不能为空');
        }
        $data['img'] = htmlspecialchars($data['img']);
        if (empty($data['img'])) {
            $this->baoError('请上传礼物图片');
        }
        $data['price'] = (int) $data['price'];
        if (empty($data['price'])) {
            $this->baoError('请填写礼物价格');
        }
        $data['status']=1;
        return $data;
    }
    public function edit($id = 0) {
        if ($id = (int) $id) {
            $obj = D('Present');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要编辑的商家分类');
            }
            if ($this->isPost()) {
                $data = $this->editCheck();
                $data['id'] = $id;
                if (false !== $obj->save($data)) {
                    $this->baoSuccess('操作成功', U('present/index'));
                }
                $this->baoError('操作失败');
            } else {
                $this->assign('detail', $detail);
                $this->display();
            }
        } else {
            $this->baoError('请选择要编辑的礼物');
        }
    }
    
    private function editCheck() {
        $data = $this->checkFields($this->_post('data', false), $this->edit_fields);
        $data['name'] = htmlspecialchars($data['name']);
        if (empty($data['name'])) {
            $this->baoError('礼物名称不能为空');
        }
        $data['img'] = htmlspecialchars($data['img']);
        if (empty($data['img'])) {
            $this->baoError('请上传礼物图片');
        }
        $data['price'] = (int) $data['price'];
        if (empty($data['price'])) {
            $this->baoError('请填写礼物价格');
        }
        $data['status']=1;
        return $data;
    }
    public function close($id = 0) {
            if($id = (int) $id){
            $obj = D('Present');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要停用的礼物');
            }
                if($obj->save(array('id' => $id, 'status' => -1))){
                    $this->baoSuccess('操作成功', U('present/index'));}
                $this->baoError('操作失败');
            }
    }
    public function open($id = 0) {
     if($id = (int) $id){
            $obj = D('Present');
            if (!$detail = $obj->find($id)) {
                $this->baoError('请选择要停用的礼物');
            }
                if($obj->save(array('id' => $id, 'status' => 1))){
                    $this->baoSuccess('操作成功', U('present/index'));}
                $this->baoError('操作失败');
            }
    
    }
    
    public function delete($id = 0)
    {
        if ($id = (int) $id) {
            $obj = D('Present');
            $obj->where(array('id' => $id))->delete();
            $this->baoSuccess('删除成功！', U('present/index'));
        } 
    }
}
