<?php
/*
 * 注册于登录控制器
 */
namespace Home\Controller;
use Think\Controller;
class LoginController extends Controller {


    /*
     * 登录页面
     * */
    public function index(){
        $this->display('login');
    }


    /*
     * 登录表单验证
     * */
    public function login()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }
        //获取账号和密码
        $account = I('post.account');
        $pwd = md5(I('post.pwd'));

        $where=array(
            'account' => $account
        );

        $user=M('user')->where($where)->find();
        if(!$user || $user['password'] != $pwd)
        {
            $this->error('用户名或密码不正确');
        }

        if($user['lock'])
        {
            $this->error('用户被锁定');
        }

        //下次自动登陆
        if(isset($_POST['auto']))
        {
            $ip=get_client_ip();
            $value=$account . '|' . $ip;
            $value=encryption($value);
            @setcookie('auto',$value,C('AUTO_LOGIN_TIME'),'/');
        }

        session('uid',$user['id']);

        redirect(U('Index/index'),3,'正在为你跳转');

    }


    /*
     * 注册页面
     * */

    public function register()
    {
        $this->display();
    }


    /*
     * 注册表单处理
     * */

    public function runRegis()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }
        $code=I('post.verify');
        if(!$this->check_verify(strtolower($code)))
        {
            $this->error('验证码错误');
        }
        if($_POST['pwd'] !=$_POST['pwded'])
        {
            $this->error('两次密码不一致');
        }

        //提取POST数据
        $pwd=md5(I('post.pwd'));
        $data=array(
            'account'    =>  I('post.account'),
            'password'   =>  $pwd,
            'registime'  =>  time(),
            'userinfo'   =>  array(
                'username' =>I('post.uname')
            )
        );



        $id=D('UserRelation')->insert($data);
        if($id)
        {
            //在插入成功后，将用户ID插入session
            session('uid',$id);
            redirect(__APP__,3,'注册成功，正在跳转');
        }
        else
        {
            $this->error('注册失败');
        }

    }


    /*
     * 获取验证码
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

    /**
     * 异步验证账号是否存在
     * */

    public function checkAccount()
    {

        if(!IS_AJAX)
        {
            E('页面不存在');
        }
        $account=I('post.account');

        $where=array('account'=>$account);
        if(M('user')->where($where)->getField('id'))
        {
            echo 'false';
        }
        else
        {
            echo 'true';
        }
    }




    /*
     * 异步验证昵称是否存在
     * */

    public function checkUname()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }
        $uname=I('post.uname');
        $where=array('username'=>$uname);
        if(M('userinfo')->where($where)->getField('id'))
        {
            echo 'false';
        }
        else
        {
            echo 'true';
        }
    }


    /*
     * 异步验证验证码
     * */

    public function checkVerify()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }
        $code=I('post.verify');
        //$verify = new \Think\Verify();

        if(!$this->check_verify(strtolower($code)))
        {
            echo 'false';
        }
        else
        {
            echo 'true';
        }
    }




    function check_verify($code, $id = '')
    {    $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }
}