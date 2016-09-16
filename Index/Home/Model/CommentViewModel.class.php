<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
 * 评论视图信息模型
 * */

Class CommentViewModel extends ViewModel
{
    Protected $viewFields = array(
            'comment'=>array('id','content','time',
            '_type'=>'LEFT'
            ),
        'userinfo'=>array('username','headimgs'=>'face','uid',
        '_on'=>'comment.uid=userinfo.uid'
        )
    );


}
