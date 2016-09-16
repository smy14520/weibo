<?php
namespace Home\Controller;
use Think\Controller;
class WeiboController extends Controller {
    /**
     *  原作微博列表
     * @return [type] [description]
     */
    Public function index () {

        $where = array('isturn' => 0);
        $count = M('weibo')->where($where)->count();
        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $weibo = D('WeiboView')->where($where)->limit($limit)->order('time DESC')->select();
        $this->weibo = $weibo;
        $this->page = $Page->show();
        $this->display();
    }

    /**
     * 删除微博
     */
    Public function delWeibo () {
        $id = I('get.id');
        $uid = I('get.uid');

        //删除微博
        if (D('WeiboRelation')->relation(true)->delete($id)) {
            //用户发布微博数-1
            M('userinfo')->where(array('uid' => $uid))->setDec('weibo');
            $this->success('删除成功', U('index'));
        } else {
            $this->error('删除失败，请重试...');
        }
    }

    /**
     * 转发微博列表
     */
    Public function turn () {

        $where = array('isturn' => array('GT', 0));
        $count = M('weibo')->where($where)->count();
        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $db = D('WeiboView');
        unset($db->viewFields['picture']);
        $turn = $db->where($where)->limit($limit)->order('time DESC')->select();

        $this->turn = $turn;
        $this->page = $Page->show();
        $this->display();
    }

    /**
     * 微博检索
     */
    Public function sechWeibo () {
        if (isset($_GET['sech'])) {
            $where = array('content' => array('LIKE', '%' . I('get.sech') . '%'));
            $weibo = D('WeiboView')->where($where)->order('time DESC')->select();

            $this->weibo = $weibo ? $weibo : false;
        }
        $this->display();
    }

    /**
     * 评论列表
     */
    Public function comment () {

        $count = M('comment')->count();
        $Page  = new \Think\Page($count,20);      //实例化分页
        $limit = $Page->firstRow.','.$Page->listRows;
        $Page -> setConfig('header','共%TOTAL_ROW%条');
        $Page -> setConfig('first','首页');
        $Page -> setConfig('last','共%TOTAL_PAGE%页');
        $Page -> setConfig('prev','上一页');
        $Page -> setConfig('next','下一页');
        $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
        $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');

        $comment = D('CommentView')->limit($limit)->order('time DESC')->select();
        $this->comment = $comment;
        $this->page = $Page->show();
        $this->display();
    }

    /**
     * 删除评论
     */
    Public function delComment () {
        $id = I('get.id');
        $wid = I('get.wid');

        if (M('comment')->delete($id)) {
            M('weibo')->where(array('id' => $wid))->setDec('comment');
            $this->success('删除成功', $_SERVER['HTTP_REFERER']);
        } else {
            $this->error('删除失败，请重试...');
        }
    }

    /**
     * 评论检索
     */
    Public function sechComment () {
        if (isset($_GET['sech'])) {
            $where = array('content' => array('LIKE', '%' . I('get.sech') . '%'));
            $comment = D('CommentView')->where($where)->order('time DESC')->select();
            $this->comment = $comment ? $comment : false;
        }
        $this->display();
    }
}