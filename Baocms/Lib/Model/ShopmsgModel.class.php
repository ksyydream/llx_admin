<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/1/12
 * Time: 16:48
 */

class ShopmsgModel extends CommonModel
{
    protected $pk = 'msg_id';
    protected $tableName = 'shopmsg';
    
    protected $types = array(
        'gift'      => '红包礼物',
        'movie'     => '官方动态',
        'message'   => '个人消息',
        'coupon'    => '抢购优惠',
    );

    public function getType(){
        return $this->types;
    }

}