<?php
require_once("Crud.php");
/**
 * Created by PhpStorm.
 * User: localuser1
 * Date: 2018/11/30
 * Time: 下午4:54
 */
class dacp extends Crud
{
    # Your Table name
    protected $table;

    # Primary Key of the Table
    protected $pk	 = 'id';


    public function setTableName($table)
    {
        $this->table = $table;
    }


}