<?php

namespace Doc;
/**
 * @title 这个是类的注释标题
 * @explain 这个是类的说明
 * @author xa 这个是类的作者
 * @create 2015-12-20 这个是类的创建时间
 * @change 2015-12-25 这个是类的最后修改时间
 * @changeby zb 这个是类的最后修改人
 * @email 297341015@qq.com这个是创建者的email
 * @abc 这个是自定义的注释
 */
class demo{

    /**
     * @title 这个是测试的方法
     * @protocol http request 协议
     * @param type=int key=a default=1 demo=1 explain=第一个求和数
     * @param type=int key=b default=1 demo=1 explain=第二个求和数
     * @return type=array demo=这个是返回值实例
     * @key-a-a1 灵活的多维返回字段支持-返回字段1
     * @key-a-a2-a3 灵活的多维返回字段支持-返回字段2
     * @key-b 灵活的多维返回字段支持-返回字段3
     */
    public function add(){
        $a=isset($_REQUEST["a"])?$_REQUEST["a"]:1;
        $b=isset($_REQUEST["b"])?$_REQUEST["b"]:1;
        $array=array(
            "a"=>array("a1"=>($a+1),"a2"=>array("a3"=>3)),
            "b"=>($b+1)
        );
        var_dump($array);
    }
}