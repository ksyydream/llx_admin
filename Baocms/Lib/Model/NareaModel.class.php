<?php



class NareaModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'narea';
    protected $token = 'narea';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token)
    {
        $this->token = $token;
    }
 
}