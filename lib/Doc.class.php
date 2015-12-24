<?php
/**
 * Created by PhpStorm.
 * User: xa
 * Date: 2015/12/26
 * Time: 10:39
 */
//demo
/**
 * @title 获取用户加入的家族 1神族 2魔族 0未加入
 * @explain 用户需要事先登录 获取在线用户id作为输入参数
 * @params type=string key=user_id default=1 explain=用户id
 * @params type=string key=type default=1  explain=类型
 * @return type=json demo={"is_in":0,"is_login":0}
 * @key-is_in 用户加入的家族 0未加入 1神族 2魔族
 * @key-is_login 用户是否已登录 1已登录 0未登录
 */

/**
 * @title ios-app评论接口
 * @explain ios专用
 * @author xa
 * @create 2015-12-20
 * @change 2015-12-25
 * @changeby asd
 * @sdfvsdf sdfgdfghsf
 */
class Doc{
    private $objRe;
    private $class;
    //构造函数 参数为类名称或者对象
    public function __construct($class)
    {
        $classname='';
        if(is_object($class)){
            $classname=get_class($class);
        }else if(is_string($class)){
            $classname=$class;
        }else{
            exit("class param error!");
        }
        $this->objRe=new \ReflectionClass($classname);//此类的方法被原封不动的继承 可以直接调用
        $this->class=$classname;
    }

    /**
     * @获取类的原始注释
     */
    public function getClassDoc(){
        return $this->getDocComment();
    }
    /***
     * @获取函数注释
     * @参数为函数名称
     * @返回值为函数原始注释
     */
    public function getMethodDoc($func_name){
        return $this->getMethod($func_name)->getDocComment();
    }
    /****
     * @判断方法是否是父类的方法
     * @return true 是父类的方法 false非父类方法
     */
    public function isParentMethod($func_name){
        return ($this->getMethod($func_name)->class==$this->class);
    }


    /**
     * @获取所有的注释键
     * @如以下注释
     * @title ios-app评论接口
     * @explain ios专用
     * @author xa
     * @create 2015-12-20
     * @change 2015-12-25
     * @changeby zhaobin
     * @sdfvsdf sdfgdfghsf
     * @返回值为 array("title","explain","author","create","change","changeby","sdfvsdf");
     */
    public function getKeys($doc){
        $docs=explode("\n",$doc);
        $keys=array();
        foreach($docs as $v){
            if(strpos($v,"@")===false)continue;
            $line=explode("@",$v);
            $line=$line[1];//implode("@",$line);
            $temp=explode(" ",$line);
            if(empty($temp[0]))continue;
            $keys[]=$temp[0];
        }
        return $keys;
    }
    /**
     * @文档格式化
     * @return array
     */
    public function docFormat($doc){
        $keys=$this->getKeys($doc);
        $format=array();
        $func="getDocByKey";
        $params=array();//array($doc);
        foreach($keys as $key){
            switch($key){
                case (strpos($key,"key-")===0):
                        $func   ="getDocReturnKeys";
                        $temp   =explode("-",$key);
                        $key    =$temp[0];
                        $params =array($doc);
                        $format[$key][]=call_user_func_array(array($this,$func),$params);
                        continue;
                    break;
                case "return":
                        $func="getDocReturns";
                        $params=array($doc);
                    break;
                case "params":
                        $func="getDocParams";
                        $params=array($doc);
                        $format[$key][]=call_user_func_array(array($this,$func),$params);
                        continue;
                    break;
                default:
                        $params=array($doc,$key);
                    break;
            }
            $format[$key]=call_user_func_array(array($this,$func),$params);

        }
        return $format;
    }
    public function __call($fun,$arg){
        if(method_exists($this->objRe,$fun)){
            return call_user_func_array(array($this->objRe,$fun),$arg);
        }
        $_fun="getDoc".ucfirst($fun);
        if(method_exists($this,$fun)){
            return call_user_func_array(array($this,$_fun),$arg);
        }else{
            return call_user_func_array(array($this,"getDocByKey"),array($arg[0],$fun));
        }
    }

    /**
     * @获取自定义注释字段
     * @return mixed array or string
     */
    public function getDocByKey($doc,$key){
        $docs=explode("\n",$doc);
        $retruns=array();
        foreach($docs as $v){
            if(strpos($v,"@")===false)continue;
            $line=explode("@",$v);
            unset($line[0]);
            $line=implode("@",$line);
            if(strpos($line,$key)===0){
                $retruns[]=trim(preg_replace("/".$key."/","",$line,1));
            }
        }
        if(empty($retruns)){
            return "";
        }
        if(count($retruns)==1){
            return $retruns[0];
        }
        return $retruns;
    }

    /**
     * @获取返回值字段-支持多维数组字段构建 如 a-b-c-d a-b-d-f 自动构建为多维数组
     * @如：
     * @key-is_in 用户加入的家族 0未加入 1神族 2魔族
     * @key-is_login 用户是否已登录 1已登录 0未登录
     * @return array 解析后结果-array("is_in"=>"用户加入的家族 0未加入 1神族 2魔族","is_login"=>"用户是否已登录 1已登录 0未登录");
     */
    public function getDocReturnKeys($doc){
        $docs=explode("\n",$doc);
        $keys=array();

        $result=array();
        foreach($docs as $v){
            if(strpos($v,"@")===false)continue;
            $line=explode("@",$v);
            unset($line[0]);
            $line=implode("@",$line);
            if(strpos($line,"key")===0){

                //$key= trim(preg_replace("/key/","",$line,1));
                $t=explode(" ",$line);
                $k=explode("-",$t[0]);
                unset($t[0]);
                unset($k[0]);

                $tt=array();
                while($_k=array_shift($k)){
                    $tt[]="[\"".$_k."\"]";
                }
                eval("\$keys".implode("",$tt)."=\"".trim(implode("",$t))."\";");
            }
        }
        return $keys;
    }


    /**
     * @获取返回值
     * @注释定义 @return type=json demo={"is_in":0,"is_login":0}
     * @type 代表返回类型 demo代表返回实例
     * @return array("type"=>"json", "demo"=>'{"is_in":0,"is_login":0}"');
     */
    public function getDocReturns($doc){
        //* @return type=json demo={"is_in":0,"is_login":0}
        $docs=explode("\n",$doc);
        $returns=array();
        foreach($docs as $v){
            if(strpos($v,"@")===false)continue;
            $line=explode("@",$v);
            unset($line[0]);
            $line=implode("@",$line);
            if(strpos($line,"return")===0){
                $return=trim(preg_replace("/return/","",$line,1));
                $temp=explode(" ",$return);
                foreach($temp as $rv){
                    $t=explode("=",$rv);
                    $returns[$t[0]]=$t[1];
                }
            }
        }
        return $returns;
    }
    /**
     * @获取参数列表
     * @注释定义 @params type=string key=user_id default=1 explain=用户id
     * @返回值 array("type"=>"string","key"=>"user_id","default"=>1,"explain"=>"用户id");
     */
    public  function getDocParams($doc){
        $docs=explode("\n",$doc);
        $params=array();
        foreach($docs as $v){
            if(strpos($v,"@")===false)continue;
            $line=explode("@",$v);
            unset($line[0]);
            $line=implode("@",$line);
            if(strpos($line,"params")===0||strpos($line,"param")===0){
                //type=string key=user_id default=1 explain=用户id
                $param=trim(preg_replace("/params/","",$line,1));
                $param=trim(preg_replace("/param/","",$param,1));
                $param=explode(" ",$param);
                $temp=array();
                foreach($param as $pv){
                    $pt=explode("=",$pv);
                    $key=$pt[0];
                    if(empty($key))continue;
                    unset($pt[0]);
                    $value=implode("=",$pt);
                    $temp[$key]=$value;
                }
                $params[]=$temp;
                continue;
            }
           /* if(strpos($line,"param")===0){
                //type=string key=user_id default=1 explain=用户id
                $param=trim(preg_replace("/param/","",$line,1));
                $param=explode(" ",$param);
                $temp=array();
                foreach($param as $pv){
                    $pt=explode("=",$pv);
                    $key=$pt[0];
                    if(empty($key))continue;
                    unset($pt[0]);
                    $value=implode("=",$pt);
                    $temp[$key]=$value;
                }
                $params[]=$temp;
            }*/
        }
        return $params;
    }
}