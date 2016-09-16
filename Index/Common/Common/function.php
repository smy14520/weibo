<?php


/*
 * 异位或加密字符串
 *
 * @param [string] $str  需要加密的字符串
 * @param [int]    $type [0:加密,1:解密]
 * return [string] 返回加密后或解密后的字符串
 */
function encryption($str,$type=0)
{
    $key=md5(C('ENCTYPTION_KEY'));
    if(!$type)
    {
        $str=str_replace('=','',base64_encode($str ^ $key));
        return $str;
    }
    $str=base64_decode($str)^$key;
    return $str;

}


/* *
 *@param [type] $time [要格式化的时间戳]
 *retrun 返回处理后的时间戳
 * */
function time_format ($time)
{
    $now = time();
    //今天零时零分
    $today = strtotime(date('y-m-d',$now));

    //计算与当前时间相差的数值
    $diff = $now - $time;

    switch($time)
    {
        case $diff < 60:
            $str = $diff . '秒前';
            break;
        case $diff < 3600;
            $str = floor($diff/60) . '分钟前';
            break;
        case $diff <3600*8:
            $str = floor($diff/3600) . '小时前';
            break;
        case $time > $today:
            $str = '今天&nbsp&nbsp' . date('H:i',$time);
            break;
        default :
            $str = date('Y-m-d H:i:s',$time);


    }
    return $str;
}


/* *
 *微博内容处理(将URL添加上A标枪)，将表情替换
 * @param [string] $content [需要处理的内容]
 * @return [string] [处理后的数据]
 * */
function  replace_weibo ($content)
{
    //给URL添加a标签
    $preg = '/(?:http:\/\/|https:\/\/)?([a-z]+\.{1}[\w]+\.{1}[\w\/]*\??[\w=\&\+\%]*)/is';
    $content = preg_replace($preg,'<a href ="http://\\1" target="_blank">\\1</a>',$content);


    //给用户@加URL链接
    $preg = '/@(\S+)\s/is';
    $content = preg_replace($preg,'<a href ="' . __APP__ . '/User/\\1" target="_blank">@\\1</a>',$content);



    //提取微博内容中所有表情文字
    $preg = '/\[(\S+?)\]/is';
    preg_match_all($preg,$content,$arr);
    //引入表情数组
    $phiz = include './Public/Data/phiz.php';
    if($arr[1])
    {
        foreach($arr[1] as $k => $v)
        {
            //在表情数组中将数组键查找出来，存在说明有该表情，进行表情替换
            $name = array_search($v,$phiz);
            if($name)
            {
                $content = str_replace($arr[0][$k],'<img src="'.__ROOT__.'/Public/Images/phiz/'.$name.'.gif" title
                ="'. $v . '"
                />',$content);
            }
        }
    }
    return $content;
}



/*
 * 往内存写入推送消息
 * [int]  $uid [用户ID号]
 * [int]  $type [1:评论 2:私信 3:@用户]
 * [bool] $clear [是否清零 false 表示不清0]
 * */
function set_msg ($uid,$type,$clear=false)
{
    switch ($type)
    {
        case 1:
            $name = 'comment';
            break;
        case 2:
            $name = 'letter';
            break;
        case 3:
            $name = 'atme';
            break;
    }

    if($clear)
    {
        $data = S('usermsg' . $uid);
        $data[$name]['status'] = 0;
        $data[$name]['total'] = 0;
        S('usermsg' . $uid,$data,0);

        return;
    }

    if(S('usermsg' . $uid))
    {
        $data[$name]['status'] = 1;
        $data[$name]['total'] ++;
        S('usermsg' . $uid,$data,0);
    }else{
        $data = array(
            'comment' =>array('status'=>0,'total'=>0),
            'letter' =>array('status'=>0,'total'=>0),
            'atme' =>array('status'=>0,'total'=>0)
        );
        $data[$name]['total']++;
        $data[$name]['status'] = 1;
        S('usermsg' . $uid,$data,0);
    }

}


