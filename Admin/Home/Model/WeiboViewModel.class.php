<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
 * 微博用户视图模型
 * */
Class WeiboViewModel extends ViewModel{
    protected $viewFields = array(
        'weibo' => array(
            'id', 'content', 'isturn', 'time', 'turnnum', 'keep', 'comment',
            '_type' => 'LEFT'
        ),
        'picture' => array(
            'max' => 'pic', '_on' => 'weibo.id = picture.wid',
            '_type' => 'LEFT'
        ),
        'userinfo' => array(
            'uid', 'username', '_on' => 'weibo.uid = userinfo.uid'
        )
    );
}
