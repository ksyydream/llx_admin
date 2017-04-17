<?php
/**
 * Created by PhpStorm.
 * User: yangyang
 * Date: 17/2/6
 * Time: 下午1:29
 */

class XlktimesModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'xlk_times';

    protected $id = 0;

    public function check_times($uid){
        $row = $this->where(array('uid'=>$uid,'cdate'=>date('Y-m-d')))->find();
        if($row){
            if($row['times'] > 100){
                return -1;
            }
        }
        return 1;
    }

    public function add_times($uid){
        $row = $this->where(array('uid'=>$uid,'cdate'=>date('Y-m-d')))->find();
        if($row){
            $this->where(array('uid'=>$uid,'cdate'=>date('Y-m-d')))->setInc('times',1);
        }else{
            $this->add(array(
               'times'=>1,
                'uid'=>$uid,
                'cdate'=>date('Y-m-d')
            ));
        }
    }

    public function delete_times($uid){
        $this->where(array('uid'=>$uid,'cdate'=>date('Y-m-d')))->delete();
        return 1;
    }
}