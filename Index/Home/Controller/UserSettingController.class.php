<?php
/*
 * 用户基本信息
 */
namespace Home\Controller;
use Think\Controller;
class UserSettingController extends CommonController {

    /*
     * 用户基本信息设置首页
     * */
    public function index()
    {
        $where=array('uid' => session('uid'));
        $field=array('username','realname','sex','location','constellation','intro','headimgb');
        $user=M('userinfo')->field($field)->where($where)->find();
        $this->assign('user',$user);
        $this->display();
    }

    /*
     * 修改用户基本信息
     * */
    public function editBasic()
    {
        if(!IS_POST)
        {
            $this->error('页面错误');
        }
        $data = array(
            'username' => I('post.nickname'),
            'realname' => I('post.truename'),
            'sex'      => (int)I('post.sex'),
            'location' => I('post.province') . ' ' . I('post.city'),
            'constellation' => I('post.night'),
            'intro'    => I('post.intro')
        );
        $where = array(
            'uid' => session('uid'),
        );

        if(M('userinfo')->where($where)->save($data))
        {
            $this->success('修改成功');
        }
        else
        {
            $this->error('修改失败');
        }

    }



    /*
     * 修改用户头像
     * */
    public function editFace()
    {
        if (!IS_POST)
        {
            E('页面不存在');
        }
        $db = M('userinfo');
        $where = array('uid' => session('uid'));
        $field = array('headimgs','headimgm','headimgb');
        $old = $db -> where($where)->field($field)->find();
        if($db->where($where)->save($_POST))
        {
            if(!empty($old['headimgb']))
            {
                @unlink('./Upload/face/' . $old['headimgb']);
                @unlink('./Upload/face/' . $old['headimgs']);
                @unlink('./Upload/face/' . $old['headimgm']);
            }
            $this->error('修改成功');
        }
        else
        {
            $this->error('修改失败');
        }

    }



    /*
     * 修改密码
     * */
    public function editPwd()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }

        $db = M('user');
        $where = array('id' => session('uid'));
        $old = $db->where($where)->getField('password');



        //判断旧密码是否正确
        if(md5(I('post.old')) != $old)
        {
            $this->error('旧密码错误');
        }

        if(I('post.new') != I('post.newed'))
        {
            $this->error('两次密码不一致');
        }

        $newpwd = md5(I('post.new'));
        $data = array(
          'password' => $newpwd

        );
        if($db->where($where)->save($data))
        {
            $this->success('修改成功');
        }
        else
        {
            $this->error('修改失败');
        }

    }
  

}
