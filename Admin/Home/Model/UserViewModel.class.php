<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
 * 微博用户视图模型
 * */
Class UserViewModel extends ViewModel{
    protected $viewFields = array(
        'user' => array(
            'id','lock'=>'_lock','registime',
            '_type'=>'LEFT'
        ),
        'userinfo' =>array(
          'username','headimgs' => 'face' ,'follow','fans','article',
            '_on' =>'user.id = userinfo.uid'
        ),

    );
}
