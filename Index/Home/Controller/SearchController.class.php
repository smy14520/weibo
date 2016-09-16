<?php
/*
 * 搜索
 */
namespace Home\Controller;
use Think\Controller;
class SearchController extends CommonController
{

    /* *
     * 搜索找人
     * */
    public function sechUser()
    {
        $keyword = $this->_getkeyword();
        $this->keyword = $keyword;
        if($keyword)
        {
            //搜索含有关键字的用户
            $where = array(
                'username' => array('LIKE','%' . $keyword . '%'),
                'uid' => array('NEQ',session('uid'))
            );


            $field = array('username','sex','location','intro','headimgm','follow','fans','article','uid');
            $db = M('userinfo');
            $count  = $db->where($where)->count('id');
            $Page   = new \Think\Page($count,20);                // 实例化分页类
            $Page->setConfig('prev','上一页');
            $Page->setConfig('next','下一页');
            $show   = $Page->show();                             // 分页类的显示
            $result = $db->where($where)->field($field)->order('uid')->limit($Page->firstRow.','.$Page->listRows)->select();

            //判断是否已经关注
            $result = $this->_getMutual($result);


            $this->assign('result',$result);// 赋值数据集
            $this->assign('page',$show);// 赋值分页输出


        }
        $this->display();


    }

    /*
     * 查找微博关键字
     *
     * */
    public function saechWeibo()
    {
        $this->display();
    }


    /**
     * 搜索微博
     */
    Public function sechWeibo () {
        $keyword = $this->_getKeyword();

        if ($keyword) {
            //检索含有关键字的微博
            $where = array('content' => array('LIKE', '%' . $keyword . '%'));

            $db = D('WeiboView');

            //导入分页类

            $count = M('weibo')->where($where)->count('id');
            $Page  = new \Think\Page($count,20);      //实例化分页
            $limit = $Page->firstRow.','.$Page->listRows;
            $Page -> setConfig('header','共%TOTAL_ROW%条');
            $Page -> setConfig('first','首页');
            $Page -> setConfig('last','共%TOTAL_PAGE%页');
            $Page -> setConfig('prev','上一页');
            $Page -> setConfig('next','下一页');
            $Page -> setConfig('link','indexpagenumb');//pagenumb 会替换成页码
            $Page -> setConfig('theme','%HEADER% %FIRST% %UP_PAGE% %LINK_PAGE% %DOWN_PAGE% %END%');
            $weibo = $db->getAll($where, $limit);

            $this->weibo = $weibo ? $weibo : false;
            //页码
            $this->page = $Page->show();
        }

        $this->keyword = $keyword;
        $this->display();
    }


    /* *
     * 返回搜索关键字
     * */
    private function _getkeyword()
    {
        return $_GET['keyword'] == '搜索微博、找人' ? NULL : I('get.keyword');

    }



    /* *
     * 返回关注结果
     * @param  [array] $result [需要处理的结果集]
     * @return [array] [处理完成后的结果集]
     * */
//    private function _getMutual($result)
//    {
//        if(!$result)  return false;
//        $db = M('follow');
//
//        //循环结果集，为结果集加上关注信息
//        $uid = session('uid');
//        $db  = M('follow');
//        foreach($result as $k => $v)
//        {
//            $sql = '(select follow from wb_follow where follow=' . $uid . ' and fans = ' . $v['uid'] . ')
//                    union
//                    (select follow from wb_follow where follow=' . $v['uid'] . ' and fans = ' . $uid . ')';
//            $res = $db->query($sql);
//            if(count($res) == 2)
//            {
//                $result[$k]['mutual'] = 2;           //mutual =2 表示互相关注
//
//            }
//            elseif(count($res) == 1 && $res[0]['follow'] != $uid) // 判断取出的关注表中的follow，是否是我关注了他
//            {
//                $result[$k]['mutual'] = 1;           //mutual =1 表示我关注了他
//            }
//            else
//            {
//                $result[$k]['mutual'] = 0;           //mutual =0 表示未关注
//            }
//        }
//        return $result;
//    }
}