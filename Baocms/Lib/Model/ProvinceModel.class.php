<?php
class ProvinceModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'province';
    protected $token = 'province';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token)
    {
        $this->token = $token;
    }
 
}