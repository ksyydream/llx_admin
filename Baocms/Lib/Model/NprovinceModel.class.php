<?php



class NprovinceModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'nprovince';
    protected $token = 'nprovince';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token)
    {
        $this->token = $token;
    }
 
}