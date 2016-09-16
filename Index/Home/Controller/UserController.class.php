<?php
/*
 * 用户个人页面
 */
namespace Home\Controller;
use Think\Controller;
class UserController extends CommonController
{
    /*
     * 用户个人页视图
     * */
    public function index()
    {
        $id = I('get.id');
        $db = D('WeiboView');
        $where = array('uid' => $id);

        $userinfo = M('userinfo')->where($where)->field('turnname,headimgm,headimgs,style',ture)->find();
        if(!$userinfo)
        {
            E('该用户不存在');
        }

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





        if(S('follow_'.$id)) {
            $follow = S('follow_'.$id);

        }else{
            //取出关注的人
            $where = array('fans' => $id);
            $follow = M('follow')->where($where)->field('follow')->select();
            foreach($follow as $k=>$v)
            {
                $follow[$k] = $v['follow'];
            }
            $field = array('username','headimgs'=>'face','uid');
            if($follow)
            {
            $where = array('uid' =>array('IN',$follow));
            $follow = M('userinfo')->where($where)->field($field)->limit(8)->select();
            }
            else{
                $follow = '';
            }
            S('follow_'.$id,$follow,3600);

        }

        if(S('fans'.$id)) {
            $fans = S('fans_'.$id);

        }else{
            //取出关注的人
            $where = array('follow' => $id);
            $fans = M('follow')->where($where)->field('fans')->select();
            foreach($fans as $k=>$v)
            {
                $fans[$k] = $v['fans'];
            }
            $field = array('username','headimgs'=>'face','uid');
            if($fans)
            {
            $where = array('uid' =>array('IN',$fans));
            $fans = M('userinfo')->where($where)->field($field)->limit(8)->select();
            }else
            {
                $fans = '';
            }
            S('fans_'.$id,$fans,3600);

        }

        $this->weibo = $result;
        $this->page = $Page->show();        // 分页显示输出
        $this->userinfo = $userinfo;
        $this->follow = $fans;
        $this->follow = $follow;

        $this->display();

    }


    /*
     * 用户关注与粉丝列表
     * */
    public function followList()
    {
        $uid  = I('get.uid');
        $type = I('get.type');
        
        $type = $type == 1 ? 'fans' : 'follow';

        $where =

        //数据分页
        $db = M('follow');
        $count = $db -> where($type.'='.$uid) -> count();
        $Page  = new \Think\Page($count,10);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $field = $type == 'fans'?'follow':'fans';
        $uids = M('follow')->field($field)->where($type.'='.$uid)->limit($limit)->select();
        if($uids)
        {
            foreach($uids as $k=>$v)
            {
                $uids[$k] = $v[$field];
            }
            $where = array('uid' => array('IN',$uids));

            $field = array('headimgs'=>'face','username','sex','location','follow','fans','article','uid');
            $users = M('userinfo')->where($where)->field($field)->select();
            $users = $this->_getMutual($users);

            $this->users = $users;
            $this->type  = $type;
            $this->count = $count;
            $this->uid   =  $uid;

        }
        $this->page = $Page->show();        // 分页显示输出
        $this->display();
    }



    /*
     * 异步移除关注与粉丝
     *
     * */
    public function delFollow()
    {
        if(!IS_AJAX)
        {
            E('页面不存在');
        }
        $type = I('post.type');
        $uid = I('post.uid');
        $retype = $type == 'fans'?'follow':$type;

        $where = array($type.'='.session('uid'),$retype . '=' . $uid);

        if(M('follow')->where($where)->delete())
        {
            $db = M('userinfo');
            $db -> where(array('uid'=>session('uid')))->setDec($retype);
            $db -> where(array('uid'=>$uid))->setDec($type);
            echo 1;
        }
        else
        {
            echo 0;
        }
    }


    /*
     * 用户收藏
     *
     * */
    public function keep()
    {

        $uid  = session('uid');
        $count = M('keep')->where(array('uid' => $uid))->count();

        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $where = array('keep.uid' => $uid);
        $weibo = D('KeepView')->getAll($where, $limit);
        // p($weibo);die;
        $this->weibo = $weibo;
        $this->page = $Page->show();

        $this->display('weiboList');


    }


    /**
     * 异步取消收藏
     */
    Public function cancelKeep () {
        if (!IS_AJAX) {
            E('页面不存在');
        }

        $kid = I('post.kid');
        $wid = I('post.wid');

        if (M('keep')->delete($kid)) {
            M('weibo')->where(array('id' => $wid))->setDec('keep');
            echo 1;
        } else {
            echo 0;
        }
    }


    /*
     *
     * 私信列表
     * */
    public function letter()
    {
        $uid = session('uid');

        //set_msg($uid, 2, true);


        set_msg($uid,2,ture);
        $count = M('letter')->where(array('uid' => $uid))->count();
        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $where = array('letter.uid' => $uid);
        $letter = D('LetterView')->where($where)->order('time DESC')->limit($limit)->select();

        $this->letter = $letter;
        $this->count = $count;
        $this->page = $Page->show();
        $this->display();

    }


    /**
     * 私信发送表单处理
     */
    Public function letterSend () {
        if (!IS_POST) {
            E('页面不存在');
        }
        $name = I('post.name');
        $where = array('username' => $name);
        $uid = M('userinfo')->where($where)->getField('uid');

        if (!$uid) {
            $this->error('用户不存在');
        }

        $data = array(
            'from' => session('uid'),
            'content' => I('post.content'),
            'time' => time(),
            'uid' => $uid
        );

        if (M('letter')->data($data)->add()) {

            set_msg($uid, 2);

            $this->success('私信已发送', U('letter'));
        } else {
            $this->error('发送失败请重试...');
        }
    }


    /**
     * 异步删除私信
     */
    Public function delLetter () {
        if (!IS_AJAX) {
            E('页面不存在');
        }

        $lid = I('post.lid');

        if (M('letter')->delete($lid)) {
            echo 1;
        } else {
            echo 0;
        }
    }




    /**
     * 评论列表
     */
    Public function comment () {
        set_msg(session('uid'), 1, true);


        $where = array('uid' => session('uid'));
        $count = M('comment')->where($where)->count();
        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $comment = D('CommentView')->where($where)->limit($limit)->order('time DESC')->select();
        $this->count = $count;
        $this->page = $Page->show();
        $this->comment = $comment;
        $this->display();
    }




    /**
     * 评论回复
     */
    Public function reply () {
        if (!IS_AJAX) {
            E('页面不存在');
        }
        $wid =I('post.wid');

        $data = array(
            'content' => I('post.content'),
            'time' => time(),
            'uid' => session('uid'),
            'wid' => $wid
        );

        if (M('comment')->data($data)->add()) {
            M('weibo')->where(array('id' => $wid))->setInc('comment');
            echo 1;
        } else {
            echo 0;
        }
    }



    /**
     * 删除评论
     */
    Public function delComment () {
        if (!IS_AJAX) {
            E('页面不存在');
        }
        $cid = I('post.cid');
        $wid = I('post.wid');

        if (M('comment')->delete($cid)) {
            M('weibo')->where(array('id' => $wid))->setDec('comment');
            echo 1;
        } else {
            echo 0;
        }
    }


    /*
     * @提到我的
     *
     * */
    public function atme()
    {


        $wids = M('atme')->where('uid='.session('uid'))->Field('wid')->select();

        set_msg(session('uid'),3,true);

        if($wids)
        {
            foreach($wids as $k=>$v)
            {
                $wids[$k] = $v['wid'];
            }

            $count = count($wids);
            $Page  = new \Think\Page($count,20);      //实例化分页
            $limit = $Page->firstRow.','.$Page->listRows;
            $Page -> setConfig('header','共%TOTAL_ROW%条');
            $Page -> setConfig('first','首页');
            $Page -> setConfig('last','共%TOTAL_PAGE%页');
            $Page -> setConfig('prev','上一页');
            $Page -> setConfig('next','下一页');
            $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
            $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

            $where = array('id'=>array('IN',$wids));
            $weibo = D('WeiboView')->getAll($where,$limit);
            $this->weibo = $weibo;
            $this->$Page = $Page->show();
        }

        $this->display('weiboList');
    }

    /*
     * 空方法获取传递过来的用户名
     * */
    public function _empty($name)
    {

        $this->_getUrl($name);
    }






    /*
     * 处理用户名空ID，获取用户ID，跳转到用户主页
     * */
    public function _getUrl($name)
    {
        $name = htmlspecialchars($name);
        $where = array('username' => $name);
        $uid = M('userinfo')->where($where)->getField('uid');

        if(!$uid)
        {
            redirect(U('Index/index'));
        }
        else
        {
            redirect(U('/'.$uid));

        }


    }


}