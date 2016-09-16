<?php
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {

    /*
     *首页
     * */
    public function index(){
        $this->display();
    }

    /*
     * 登录处理
     * */
    public function login()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }
        $verify = I('post.verify');
        if(!$this->check_verify($verify))
        {
            $this->error('验证码错误');
        }
        $name = I('post.uname');
        $pwd = md5(I('post.pwd'));
        $db = M('admin');
        $user = $db->where(array('username' => $name))->find();
        if(!$name || $user['password'] != $pwd)
        {
            $this->error('账号或密码错误');
        }

        if($user['lock'])
        {
            $this->error('账号被锁定');
        }

        $data = array(
            'id' => $user['id'],
            'logintime' => time(),
            'loginip'   => get_client_ip()
        );
        $db->save($data);
        session('uid',$user['id']);
        session('username',$user['username']);
        session('logintime',date('Y-m-d H:i',$user['logintime']));
        session('now',date('Y-m-d H:i',time()));
        session('loginip',$user['loginip']);
        $this->success('正在登录...',__APP__);
        
    }



    /*
 * 调用验证码
 * */
    public  function verify()
    {
        //验证码的配置
        $config=array(
            'fontSize'  => 60,     //验证码字体大小
            'length'    => 4,      //验证码长度
            'useNoise'  =>false,   //关闭噪点
            'useCurve'  =>false,   //关闭干扰
        );
        //引入验证码
        $Verify = new \Think\Verify($config);
        //生成验证码
        $Verify->entry();
    }

    /*
     * 验证码验证
     * */
    function check_verify($code, $id = '')
    {
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }

}