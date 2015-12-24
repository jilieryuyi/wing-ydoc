<?php
/**
 * 文档自动构建（注释自动转换为标准文档）
 * @author yuyi
 * @qq 297341015
 */
namespace Doc;
include_once('demo.php');
include_once('lib/Doc.class.php');

class DocCreater{

    public function printkeys($keys,&$index){
        $step=20;
        foreach($keys as $k=>$v){
            if(is_array($v)){
                echo "<div style='padding-left: ".($index*$step)."px'>".$k."：</div>";
                $index++;
                $this->printkeys($v,$index);
                $index--;
            }
            else{
                echo "<div style='padding-left: ".($index*$step)."px'>".$k."：".$v."</div>";
            }

        }
    }

    public function create($class) {
        ob_start();
        echo '
        <html>
        <head>
        <style>
        textarea{ width:500px; height:120px;}
        </style>
        <script src="/static/js/jquery.js"></script>
        <script src="/static/js/jquery.json-2.4.min.js"></script>';
                echo '<script>
            function ontest(dom,url){
                var parent=$(dom).parents(".api-item");
                var datas=\'\';
                parent.find("input.params").each(function(index,ele){
                    datas+=$(ele).attr("name")+"="+encodeURIComponent($(ele).val())+"&";
                });
                $.ajax({
                    type:"POST",
                    data:datas,
                    url:url+"&"+datas,
                        success:function(msg){
                        if(typeof msg=="object"){
                            msg=$.toJSON(msg);
                        }
                        parent.find(".result").val(msg);
                    },
                    error:function(e){
                        parent.find(".result").val(e.responseText);
                        console.log(e);
                    }
                });
            }
        </script>'."\n".'
        </head>'."\n".'
        <body>'."\n";


        $objDoc     =new \Doc($class);
        $methods    =$objDoc->getMethods();
        $classdocstr=$objDoc->getClassDoc();
        //快速格式化
        $classdoc   =$objDoc->docFormat($classdocstr);
        //var_dump($classdoc);
        //格式化的结果
        /*
         array(8) {
                  ["title"]=>
                  string(27) "这个是类的注释标题"
                  ["explain"]=>
                  string(21) "这个是类的说明"
                  ["author"]=>
                  string(21) "这个是类的作者"
                  ["create"]=>
                  string(38) "2015-12-20 这个是类的创建时间"
                  ["change"]=>
                  string(44) "2015-12-25 这个是类的最后修改时间"
                  ["changeby"]=>
                  string(33) "zb 这个是类的最后修改人"
                  ["email"]=>
                  string(42) "297341015@qq.com这个是创建者的email"
                  ["abc"]=>
                  string(27) "这个是自定义的注释"
                }
         * */
        //获取标题---其实方法就是对应key的名字 使用简单吧 先调用getClassDoc 获取到原生注释
        //再将对应的key作为方法名直接使用即可得到相应的注释
        //有可能返回的是数组哦，如果有两个以上相同的key的话
        echo "<h2>",$class,"</h2>";
        echo "<h2>",$objDoc->title($classdocstr),"</h2>";
        //$objDoc->abc($classdocstr);获取到自定义注释abc
        echo $classdoc["explain"],"<br/>";
        echo "作者：",$classdoc["author"],"<br/>";
        echo "创建时间：",$classdoc["create"],"<br/>";
        echo "最后修改：",$classdoc["change"],"<br/>";
        echo "最后修改人：",$classdoc["changeby"],"<br/>";
        echo "邮箱：",$classdoc["email"],"<br/>";

        //遍历所有的方法
        foreach($methods as $m){
            //判断是公有的方法 并且不是继承来的方法
            if($m->isPublic()&&$m->class==$class){
                echo "<div class=\"api-item\">\n";

                $doc=$m->getDocComment();
                echo "<h3>".$m->getName()."</h3>\n";
                echo "<h4>".$objDoc->title($doc)."</h4>\n";
                echo $objDoc->explain($doc),"<br/>";
                echo "协议：<br/>&nbsp;&nbsp;&nbsp;&nbsp;".
                    $objDoc->protocol($doc),"<br/><br/>";

                echo "参数<br/>";
                $params=$objDoc->getDocParams($doc);
                if(!is_array($params)||count($params)<=0){
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;无<br/>";
                }else{
                    foreach($params as $i=>$v){
                        echo "&nbsp;&nbsp;&nbsp;&nbsp;",($i+1),"、",$v["key"],"&nbsp;&nbsp;",$v["explain"]," ","  类型：",$v["type"],"  默认值：".$v["default"]," 实例:".$v["demo"];
                        echo "<br/>";
                    }
                }
                echo "<br/>";
                echo "<br/>";


                $returns=$objDoc->getDocReturns($doc);

                echo "返回值：<br/>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;类型：",$returns["type"],"<br/>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;返回实例：",$returns["demo"],"<br/><br/>";


                $keys=$objDoc->getDocReturnKeys($doc);

                echo "返回字段：<br/>";

                $keyindex=0;
                $this->printkeys($keys,$keyindex);
                echo "<br/><br/>";


                echo "测试：<br/>";
                foreach($params as $i=>$v){
                    echo "参数：",$v["explain"]," ",$v["key"]," <input class='params' name='".$v["key"]."' type='input' value='".$v["default"]."'/>";
                    echo "<br/>";
                }
                echo "返回值：<br/>";
                echo "<input onclick=\"ontest(this,'/test.php?c=Doc\\\\demo&f=".$m->getName()."')\" type='button' value='测试'/><br/>";
                echo "<textarea class='result'></textarea>";

                echo "<br/><br/>";
                echo "</div>\n";
            }


        }
        echo "</body>\n</html>";
        $content=ob_get_contents();
        ob_end_clean();


        echo $content;

    }
}


//测试支持
if(isset($_REQUEST["c"])&&isset($_REQUEST["f"])){
    $t=new $_REQUEST["c"]();
    echo call_user_func_array(array($t,$_REQUEST["f"]),array($_REQUEST["a"],$_REQUEST["b"]));
}else{
    //打印文档
    $doc=new DocCreater();
    $doc->create('Doc\\demo');
}