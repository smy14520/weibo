<?php
namespace Home\Model;
use Think\Model\RelationModel;

/*
 * 用户信息关联模型
 * */

Class UserRelationModel extends RelationModel
{
    //定义主表名称
    protected $tableName = 'user';

    //定义用户与用户信息关联关系属性
    protected $_link=array(
        'userinfo' => array(
            'mapping_type'  => self::HAS_ONE,
            'foreign_key'   => 'uid'

        )
    );

    /*
     * 自动插入方法
     * */
    public function insert($data=null)
    {
        $data = is_null($data) ? $_POST: $data;
        return $this->relation(true)->data($data)->add();
    }
}
