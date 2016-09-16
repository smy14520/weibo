<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends CommonController {


    /*
     * 首页视图
     * */
    public function index()
    {


        $db = D('WeiboView');
        $uid = array(session('uid'));
        $gid = (int)I('get.gid');         //获取分组ID


        //判断是否传入分组信息，如果传入，按照分组分出已关注的人
        if($gid) {
            $where = array('gid' => $gid);
            $uid = '';                          //如果是分组，讲自身排除
        }
        else{
            $where = array('fans' => session('uid'));
        }

        //查出已关注的人的UID
        $result = M('follow')->field('follow')->where($where)->select();

        //将自己和已关注的人拼接成数组
        if($result)
        {
            foreach($result as $v)
            {
                $uid[] = $v['follow'];
            }
        }
        $where = array('uid' => array('IN',$uid));
        
        //数据分页
        $count = $db -> where($where) -> count();
        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
        //关联查询，将关注的人与自己的微博输出
        $result = $db->getAll($where,$limit);
        $this->weibo = $result;
        $this->page = $Page->show();        // 分页显示输出
        $this->display();
    }



    /* *
    * 微博发布处理
    * */
    public function sendWeibo()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }

        $data = array(
            'content' => I('post.content'),
            'time'    => time(),
            'uid'     => session('uid'),
        );



        if($wid = M('weibo')->data($data)->add())
        {
            if(!empty(I('post.max')))
            {
                $img = array(
                    'max' => I('post.max'),
                    'mini' => I('post.mini'),
                    'medium' => I('post.medium'),
                    'wid' => $wid,
                );
                M('picture')->data($img)->add();
            }
            $this->success('发布成功',U('index'));

            M('userinfo')->where(array('uid'=>session('uid')))->setInc('article');
            $this->_atmeHandel($data['content'],$wid);
        }
        else
        {
            $this->error('发布失败，请重试....');
        }
    }


    /*
     * @用户处理
     * */
    public function _atmeHandel($content,$wid)
    {
        $preg = '/@(\S+?)\s/is';
        preg_match_all($preg,$content,$arr);
        $db = M('userinfo');
        $atme = M('atme');
        if(!empty($arr[1]))
        {
            foreach($arr[1] as $k=>$v)
            {
                $uid = $db->where(array('username'=>$v))->getField('uid');
                if($uid)
                {
                    $data = array(
                        'wid' => $wid,
                        'uid' => $uid
                    );
                    $atme->data($data)->add();
                    set_msg($uid,3);
                }
            }
        }
    }


    /*
     * 转发微博
     * */
    public function turn()
    {
        if(!IS_POST)
        {
            E('页面不存在');
        }
        $id = (int)I('post.id');           // 被转发的微博的ID
        $content = I('post.content');      // 转发时的内容
        $uid = session('uid');             // 用户ID
        $tid = I('post.tid');              // 判断是否多次转发

        $data = array(                     // 转发后的微博内容
          'content' => $content,
          'isturn'  => $tid ? $tid : $id,
          'time'    => time(),
          'uid'     => $uid,
        );

        $db = M('weibo');
        if($db->data($data)->add())
        {
            if(isset($_POST['becomment']))          //判断是否勾选同时评论
            {
                $data = array(                       //写入评论表
                    'content' => $content,
                    'time'    => time(),
                    'uid'     => $uid,
                    'wid'     => $id,
                );
                M('comment')->data($data)->save();
                $wuid = M('weibo')->where(array('id'=>$id))-getField('uid');   //取出该条微博的ID
                set_msg($wuid,1);
                $db->where(array('id'=>$id))->setInc('comment');           //给微博评论加1

            }
            $db->where(array('id'=>$id))->setInc('turnnum');                //给微博转发+1
            M('userinfo')->where(array('uid' => $uid))->setInc('article');  //用户微博+1
            if($tid)
            {
                $db->where(array('id'=>$tid))->setInc('turnnum');           //给微博tid的转发+1
            }
            $this->success('微博转发成功');
        }
        else{
            $this->error('微博转发失败，请重试...');
        }
    }


    /*
     * 收藏微博
     * */
    public function keep()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }

        $wid = I('post.wid');
        $uid = session('uid');

        $db = M('keep');

        //检查是否收藏过该微博
        $where = array('wid' => $wid,'uid' => $uid);
        if($db->where($where)->getField('id'))
        {
            echo -1;
            exit;
        }

        //需要插入的数据
        $data = array('uid' => $uid,'wid' => $wid,'time' => time());

        if($db->data($data)->add())
        {
            echo 1;
            //收藏成功 为微博收藏+1
            M('weibo')->where(array('wid'=>$wid))->setInc('keep');
        }else
        {
            echo 0;
        }


    }



    /**
     * 评论
     */
    Public function comment () {
        if (!IS_AJAX) {
            halt('页面不存在');
        }
        //提取评论数据
        $data = array(
            'content' => I('post.content'),
            'time' => time(),
            'uid' => session('uid'),
            'wid' => (int)I('post.wid')
        );



        if (M('comment')->data($data)->add()) {
            //读取评论用户信息
            $field = array('username', 'headimgs' => 'face', 'uid');
            $where = array('uid' => $data['uid']);
            $user = M('userinfo')->where($where)->field($field)->find();

            //被评论微博的发布者用户名
            $uid = I('post.uid');
            set_msg($uid,1);
            $username = M('userinfo')->where(array('uid' => $uid))->getField('username');

            $db = M('weibo');
            //评论数+1
            $db->where(array('id' => $data['wid']))->setInc('comment');

            //评论同时转发时处理
            if ($_POST['isturn']) {
                //读取转发微博ID与内容
                $field = array('id', 'content', 'isturn');
                $weibo = $db->field($field)->find($data['wid']);
                $content = $weibo['isturn'] ? $data['content'] . ' // @' . $username . ' : ' . $weibo['content'] : $data['content'];

                //同时转发到微博的数据
                $cons = array(
                    'content' => $content,
                    'isturn' => $weibo['isturn'] ? $weibo['isturn'] : $data['wid'],
                    'time' => $data['time'],
                    'uid' => $data['uid']
                );

                if ($db->data($cons)->add()) {
                    $db->where(array('id' => $weibo['id']))->setInc('turnnum');
                }
                echo 1;
                exit();
            }

            //组合评论样式字符串返回
            $str = '';
            $str .= '<dl class="comment_content">';
            $str .= '<dt><a href="' . U('/' . $data['uid']) . '">';
            $str .= '<img src="';
            $str .= __ROOT__;
            if ($user['face']) {
                $str .= '/Uploads/Face/' . $user['face'];
            } else {
                $str .= '/Public/Images/noface.gif';
            }
            $str .= '" alt="' . $user['username'] . '" width="30" height="30"/>';
            $str .= '</a></dt><dd>';
            $str .= '<a href="' . U('/' . $data['uid']) . '" class="comment_name">';
            $str .= $user['username'] . '</a> : ' . replace_weibo($data['content']);
            $str .= '&nbsp;&nbsp;( ' . time_format($data['time']) . ' )';
            $str .= '<div class="reply">';
            $str .= '<a href="">回复</a>';
            $str .= '</div></dd></dl>';
            echo $str;

        } else {
            echo 'false';
        }
    }


    /*
     * 异步获取评论内容
     *
     * */
    public function getComment()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }

        $wid = I('post.wid');          //接收微博ID
        $db = D('CommentView');        //建立评论视图模型

        $where = array('wid' => $wid);
        $page = I('post.page') < 2 ? 1 : I('post.page');
        $num = 8 ;                                              //每页的页数
        $count = D('CommentView')->where($where)->count();

        //数据可以分的总页数
        $total = ceil($count / $num);
        $limit = $page < 2 ? '0' . ',' . $num: $num * ($page -1) .',' . $num ;

        $result = $db->where($where)->limit($limit)->order('time desc')->select();

        if($result)
        {
            $str = '';
            foreach($result as $v)
            {

                $str .= '<dl class="comment_content">';
                $str .= '<dt><a href="' . U('/' . $v['uid']) . '">';
                $str .= '<img src="';
                $str .= __ROOT__;
                if ($v['face']) {
                    $str .= '/Uploads/Face/' . $v['face'];
                } else {
                    $str .= '/Public/Images/noface.gif';
                }
                $str .= '" alt="' . $v['username'] . '" width="30" height="30"/>';
                $str .= '</a></dt><dd>';
                $str .= '<a href="' . U('/' . $v['uid']) . '" class="comment_name">';
                $str .= $v['username'] . '</a> : ' . replace_weibo($v['content']);
                $str .= '&nbsp;&nbsp;( ' . time_format($v['time']) . ' )';
                $str .= '<div class="reply">';
                $str .= '<a href="">回复</a>';
                $str .= '</div></dd></dl>';
            }

            //写入分页内容
            if ($total > 1) {
                $str .= '<dl class="comment-page">';

                switch ($page) {
                    case $page > 1 && $page < $total :
                        $str .= '<dd page="' . ($page - 1) . '" wid="' . $wid . '">上一页</dd>';
                        $str .= '<dd page="' . ($page + 1) . '" wid="' . $wid . '">下一页</dd>';
                        break;

                    case $page < $total :
                        $str .= '<dd page="' . ($page + 1) . '" wid="' . $wid . '">下一页</dd>';
                        break;

                    case $page == $total :
                        $str .= '<dd page="' . ($page - 1) . '" wid="' . $wid . '">上一页</dd>';
                        break;
                }
                $str .= '</dl>';
            }
            echo $str;
        }
        else{
        }

    }



    /*
     * 异步删除微博
     * */
    public function delWeibo()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }

        $wid = I('post.wid');

        //判断是否是当前登录用户进行删除操作
        $id = M('weibo')->where(array('id'=>$wid))->field('uid')->select();


        if($id[0]['uid'] == session('uid'))
        {
            //取出微博图片,并删除
            $db = M('picture');
            $img = $db->where(array('wid'=>$wid))->find();

            //如果微博有图片，删除图片
            if($img)
            {
            @unlink('./Uploads/Pic/'.$img['mini']);
            @unlink('./Uploads/Pic/'.$img['max']);
            @unlink('./Uploads/Pic/'.$img['medium']);
            $db->where('wid='.$wid)->delete();
            }

            //删除微博并给微博数减1
            M('weibo')->where('id='.$wid)->delete();
            M('userinfo')->where('uid='.session('uid'))->setDec('article');
            echo 1;
        }
        else
        {
            echo 0;
        }

    }



    /*
     * 退出登录
     * */
    public function loginOut()
    {
        //卸载session
        session_unset();

        //删除用于自动登陆的cookie
        @setcookie('auto','',time()-3600);

        //跳转到登录页面
        redirect(U('login/index'),2,'成功退出，正在转到登录页面...');
    }




    
}