<?php
namespace Home\Model;
use Think\Model\ViewModel;

/*
 * 微博视图模型
 * */
class WeiboViewModel extends ViewModel
{
    //定义视图表关联关系.
    Protected $viewFields = array(
        'weibo' =>array(
            'id','content','isturn','time','keep','turnnum','comment','uid',
            '_type' => 'LEFT'
        ),
        'userinfo'  => array(
            'username','headimgs' =>'face',
            '_on'   => 'weibo.uid = userinfo.uid',
            '_type' => 'LEFT'
        ),
        'picture'   => array(
            'mini','medium','max',
            '_on'   => 'weibo.id = picture.wid'
        )

    );

    /*
     * 返回查询所有记录
     * @param [string] $where [查询条件]
     * */
    public function getAll($where,$limit)
    {
        $result = $this->where($where)->limit($limit)->order('time desc')->select();
        foreach($result as $k=>$v)
        {
            if($v['isturn'])
            {
                $tmp = $this->find($v['isturn']);
                $result[$k]['isturn'] = $tmp ? $tmp : -1;
            }
        }
        return $result;
    }

}