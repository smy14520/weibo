<?php
namespace Home\Controller;
use Think\Controller;
class SystemController extends Controller {

    /*
     * 网站信息
     * */
    Public function index () {

        $config = include './Index/Home/Conf/system.php';
        $this->configs = $config;
        $this->display();
    }

    /*
     *
     * 关键字视图
     * */
    Public function filter () {
        $config = include './Index/Home/Conf/filtrate.php';

        $this->filter = implode('|', $config['FILTER']);
        $this->display();
    }

    /*
     * 修改微博配置
     *
     * */
    public function runEdit()
    {
        $config['webname'] = I('post.webname');
        $config['copy'] = I('post.copy');
        $config['regis_on'] = I('post.regis_on');
        //$arr = explode('|',$filter);
        $data = array(
            'config'=>'wb_config',
            'var'=>json_encode($config),
        );

        $db = M('config');
        if($db->where(array('config'=>'wb_config'))->find())
        {
            $db->where('config="wb_config"')->save($data);
            //$this->success('修改关键字成功');
        }
        else{
            $db->data($data)->add();
            //$this->success('添加关键字成功');
        }

        $path = './Index/Home/Conf/system.php';

        $configs['wb_config'] = $config;
        $data = "<?php\r\nreturn " . var_export($config, true) . ";\r\n?>";

        if (file_put_contents($path, $data)) {
            $this->success('修改成功', U('index'));
        } else {
            $this->error('修改失败， 请修改' . $path . '的写入权限');
        }
    }


    /*
     *修改关键字
     * */
   public function runEditFilter()
   {
       $filter = I('post.filter');
       $arr = explode('|',$filter);
       $data = array(
           'config'=>'filter',
           'var'=>json_encode($arr),
       );

       $db = M('config');
       if($db->where(array('config'=>'filter'))->find())
       {
           $db->where('config="filter"')->save($data);
           //$this->success('修改关键字成功');
       }
       else{
           $db->data($data)->add();
           //$this->success('添加关键字成功');
       }

       $path = './Index/Home/Conf/filtrate.php';
       //$config = include $path;
       $configs['FILTER'] = $arr;


       $data = "<?php\r\nreturn " . var_export($configs, true) . ";\r\n?>";
       if(file_put_contents($path, $data))    //将关键字写入配置文件
       {
           $this->success('修改成功', U('filter'));
       }
       else
       {
           $this->error('修改失败， 请修改' . $path . '的写入权限');
       }
   }

}