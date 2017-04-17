<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 17/2/6
 * Time: 下午1:29
 */

class XlkdetailModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'xlk_detail';

    protected $id = 0;

    public function get_list($uid,$page=1){
        $list = $this->alias('a')->field('a.*,b.title,b.yhq,c.shop_name')
            ->join('bao_xlk_master b on a.master_id = b.id','LEFT')
            ->join('bao_shop c on b.shop_id = c.shop_id','LEFT')
            ->where(array('a.flag'=>2,'a.uid'=>$uid))
            ->order(array('a.used_time' => 'asc'))->page($page.',10')->select();
        foreach ($list as $k => $val) {
            $user_ids[$val['uid']] = $val['uid'];
            $users= D('Users')->itemsByIds($user_ids);
            $list[$k]['username']=$users[$val['user_id']]['nickname'];
            $list[$k]['rank_id']=$users[$val['user_id']]['rank_id'];
            $list[$k]['face']=$users[$val['user_id']]['face'];
            $zengpins = D('Xlkzp')->where(array('master_id'=>$val['master_id']))->select();
            foreach ($zengpins as $a => $v){
                $list[$k]['zp'][]=array('desc'=>$v['desc'],'qty'=>$v['qty']);
            }
        }
        return $list;
    }
}