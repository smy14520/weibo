<?php
namespace Home\Controller;
use Think\Controller;
class CommonController extends Controller {

    function _initialize()
    {




        /*
         * 自动登陆判断
         * */
        if(isset($_COOKIE['auto']) && !isset($_SESSION['uid']))
        {
            $value=encryption($_COOKIE['auto'],1);
            $arr=explode('|',$value);
            $ip=get_client_ip();

            //判断cookie中的ip是否与上次登录的IP一致
            if($arr[1] == $ip)
            {
                $account = $arr[0];
                $where=array('account' => $account);
                $user = M('user')->where($where)->field(array('id','lock'))->find();
                if($user && !$user['lock'])
                {
                    session('uid',$user['id']);
                }
            }
        }

        //判断用户是否登录
        if(!isset($_SESSION['uid']))
        {
            redirect(U('login/index'));
        }
    }


    /*
   * 头像上传
   * */
    public function uploadFace()
    {
        if (!IS_OOST)
        {
            E('页面不存在');

        }

       $width  = array(180,100,50);         // 图片缩略图宽度
       $height = array(180,100,50);         // 图片缩略图高度
       $upload = $this->_upload('face/',$width,$height);
       echo json_encode($upload);

    }


    /* *
     * 微博图片上传
     * */
    public function uploadPic()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }

        $width  = array(800,300,120);
        $height  = array(800,300,120);
        $upload = $this->_upload('Pic/',$width,$height);        // 处理图片缩略图 返回缩略图信息
        echo json_encode($upload);                              // JSON缩略图信息，在JS中使用



    }


    /*
     * 处理图片文件上传
     * @param [string] $path [保存图片的路径]
     * @param [array]  $width [缩略图宽度,例(180,100,50)从大到小]
     * @param [array]  $height [缩略图宽度,例(180,100,50)从大到小]
     * return 返回缩略图的地址以及信息
     * */
    public function _upload($path,$width,$height)
    {
        $dpath=C('UPLOAD_PATH') . $path;                         //图片保存的目录

        $config = array(
        'maxSize'    =>   C('UPLOAD_MAX_SIZE'),                  // 设置附件上传大小
        'savePath'   =>   $path,                                 // 图片保存路径
        'rootPath'   =>   C('UPLOAD_PATH'),                      //保存的根目录
        'saveName'   =>   'uniqid',                              // 上传文件的命名规则
        'replace'    =>   true,                                  // 同名文件是否覆盖
        'exts'       =>   array('jpg', 'gif', 'png', 'jpeg'),    // 设置附件上传类型
        'autoSub'    =>   true,                                  // 自动子目录
        'subName'    =>   array('date','Y-m-d'),                 // 设置子目录格式
    );


        //生成缩略图
        $obj   =  new \Think\Upload($config);                      // 实例化上传类
        $info  =  $obj -> upload();                                // 上传文件
        $image = new \Think\Image();                               // 创建图片处理类
        $date  = date('Y-m-d');                                    // 写入日期


        if (!$info)
        {
            return array('status' => 0, 'msg' => $info->getError());

        }
        else
        {
            //将上传的图片循环出来处理，生成需要的缩略图
            foreach ($info as $file)
            {
                $imgurl =  $dpath . $date . '/' . $file['savename'];                                                  // 将上传的原始图片的URL写出
                $image  -> open($imgurl);                                                                             // 使用图片类打开需要处理的原始图片
                $image  -> thumb($width[0], $height[0]) -> save($dpath . $date . '/max_'  . $file['savename']);       // 生成缩略图
                $image  -> thumb($width[1], $height[1]) -> save($dpath . $date . '/mid_'  . $file['savename']);       // 生成缩略图
                $image  -> thumb($width[2], $height[2]) -> save($dpath . $date . '/mini_' . $file['savename']);       // 生成缩略图
                unlink ($imgurl);                                                                                     // 删除原始图片

                return array(
                    'status' => 1,
                    'path'   => array(
                        'max'    => $date . '/max_' . $file['savename'],
                        'medium' => $date . '/mid_' . $file['savename'],
                        'mini'   => $date . '/mini_' . $file['savename'],
                    )
                );
            }
        }
    }



    /*
     * 异步轮询推送消息
     *
     * */
    public function getMsg()
    {
//        if(!IS_AJAX)
//        {
//            E('页面不存在');
//        }

        $uid = session('uid');
        $data = S('usermsg' . $uid);
        if($data['comment']['status'])
        {
            echo json_encode(array('status'=>1,'total'=>$data['comment']['total'],'type'=>1));

            exit();
        }
        if($data['letter']['status'])
        {
            echo json_encode(array('status'=>1,'total'=>$data['letter']['total'],'type'=>2));
            exit();
        }
        if($data['atme']['status'])
        {
            echo json_encode(array('status'=>1,'total'=>$data['atme']['total'],'type'=>3));

            exit();
        }
        echo json_encode(array('status'=>0));
          
    }


    /* *
     * 异步创建分组
     * */
    public function addGroup()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }

        $data = array(
          'name' => I('post.name'),
          'uid'  => session('uid')
        );

        if(M('group')->data($data)->add())
        {
            echo json_encode(array('status' => 1,'msg' => '创建成功'));
        }
        else
        {
            echo json_encode(array('status' => 0,'msg' => '创建失败,请重试'));
        }
    }


    /* *
     *添加关注
     * */
    public function addFollow()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }

        $data = array(
          'follow' => (int)I('post.follow'),
          'fans'   => session('uid'),
          'gid'    => (int)I('post.gid')
        );
        if(M('follow')->data($data)->add())
        {
            $db = M('userinfo');
            $db -> where(array('uid' => $data['follow']))->setInc('fans');
            $db -> where(array('uid' => $data['fans']))->setInc('follow');
            echo  json_encode(array('status' => 1,'msg' => '关注成功'));
        }
        else
        {
            echo  json_encode(array('status' => 0,'msg' => '关注失败'));
        }
    }



    /*
     * 异步改变模板风格
     *
     * */
    public function editStyle()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }

        $style = I('post.style');
        $where = ('uid='.session('uid'));
        if(M('userinfo')->where($where)->save(array('style'=>$style)))
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }


    /* *
 * 返回关注结果
 * @param  [array] $result [需要处理的结果集]
 * @return [array] [处理完成后的结果集]
 * */
    public function _getMutual($result)
    {
        if(!$result)  return false;
        $db = M('follow');

        //循环结果集，为结果集加上关注信息
        $uid = session('uid');
        $db  = M('follow');
        foreach($result as $k => $v)
        {
            $sql = '(select follow from wb_follow where follow=' . $uid . ' and fans = ' . $v['uid'] . ')
                    union
                    (select follow from wb_follow where follow=' . $v['uid'] . ' and fans = ' . $uid . ')';
            $res = $db->query($sql);
            if(count($res) == 2)
            {
                $result[$k]['mutual'] = 2;           //mutual =2 表示互相关注

            }
            elseif(count($res) == 1 && $res[0]['follow'] != $uid) // 判断取出的关注表中的follow，是否是我关注了他
            {
                $result[$k]['mutual'] = 1;           //mutual =1 表示我关注了他
            }
            else
            {
                $result[$k]['mutual'] = 0;           //mutual =0 表示未关注
            }
        }
        return $result;
    }

}