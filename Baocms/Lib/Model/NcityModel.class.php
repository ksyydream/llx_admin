<?php



class NcityModel extends CommonModel{
    protected $pk   = 'id';
    protected $tableName =  'ncity';
    protected $token = 'ncity';
    protected $orderby = array('orderby'=>'asc');
   
    public function setToken($token)
    {
        $this->token = $token;
    }
 
}