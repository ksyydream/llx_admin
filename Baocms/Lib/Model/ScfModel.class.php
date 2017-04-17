<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 17/2/6
 * Time: 下午1:29
 */

class ScfModel extends CommonModel{
    protected $pk   = 'scf_id';
    protected $tableName =  'user_scf';

    protected $id = 0;

    public function get_list($app_uid,$page=1){
        //$Model = new Model();
        $map = array(
            'a.uid' => $app_uid,
            'b.closed'=>0,
            'c.closed'=>0
            );
        $items = $this->alias('a')->field("a.scf_id,b.*,c.score,c.shop_name")
            ->join('bao_shop_fd b on b.fd_id = a.fd_id','LEFT')
            ->join('bao_shop c on c.shop_id = b.shop_id','LEFT')
            ->where($map)
            ->order(array('a.scf_id' => 'desc'))
            ->page($page.",10")
            ->select();
        return $items;
    }
}