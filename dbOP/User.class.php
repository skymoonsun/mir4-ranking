<?php 
require_once("crud.class.php");

class User Extends Crud{
    protected $table = 'table_user';
    protected $pk	 = 'USER_ID';
}

?>