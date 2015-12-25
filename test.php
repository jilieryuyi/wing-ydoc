<?php
/**
 * 文档自动构建（注释自动转换为标准文档）
 * @author yuyi
 * @qq 297341015
 */
namespace Doc;
include_once('demo.php');
include_once('lib/Doc.class.php');


if (isset($_REQUEST["c"]) && isset($_REQUEST["f"])) {
    //路由测试支持
    $t = new $_REQUEST["c"]();
    echo call_user_func_array(array($t, $_REQUEST["f"]), array($_REQUEST["a"], $_REQUEST["b"]));
    exit;
}
//打印多维返回字段
function printkeys($keys, &$index)
{
    $step = 20;
    foreach ($keys as $k => $v) {
        if (is_array($v)) {
            echo "<div style='padding-left: " . ($index * $step) . "px'>" . $k . "=></div>";
            $index++;
            printkeys($v, $index);
            $index--;
        } else {
            echo "<div style='padding-left: " . ($index * $step) . "px'>" . $k . "=>" . $v . "</div>";
        }

    }
}


$class = "Doc\\demo";
$objDoc = new \Doc($class);
$methods = $objDoc->getMethods();
$classdocstr = $objDoc->getClassDoc();
//快速格式化
$classdoc = $objDoc->docFormat($classdocstr);
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
//$objDoc->abc($classdocstr);获取到自定义注释abc
?>
<html>
<head>
    <style>
        textarea {
            width: 500px;
            height: 120px;
        }

        pre {
            background-color: transparent;
            border: 1px solid #eaeaea;
            line-height: 1.2;
            margin-bottom: 1.6em;
            max-width: 100%;
            overflow: auto;
            padding: 0.8em;
            white-space: pre;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 14px;
        }

        h2 {

        }

        body {
            background: #ddd;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 3% 5%;
        }

        .block {
            background: #f8f8f8f8;
            background-color: transparent;
            border: 1px solid #eaeaea;
            padding: 0.8em;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 2.2;
            margin: 20px 0;
        }

        .tag {
            display: inline-block;
            width: 100px;
            text-align: right;
            padding-right: 8px;
        }

        .params div {
            border-bottom: #666;
        }

        .params div label {
            display: inline-block;
            text-align: center;
            padding: 0 5px;
        }

        .index {
            width: 36px;
        }

        .type {
            width: 50px;
        }

        .key {
            width: 50px;
        }

        .default {
            width: 80px;
        }

        .demo {
            width: 50px;
        }

        .explain {
            width: 300px;
        }
    </style>
    <script src="/static/js/jquery.js"></script>
    <script src="/static/js/jquery.json-2.4.min.js"></script>
    <script>
        function ontest(dom, url) {
            var parent = $(dom).parents(".block");
            var datas = '';
            parent.find("input.params").each(function (index, ele) {
                datas += $(ele).attr("name") + "=" + encodeURIComponent($(ele).val()) + "&";
            });
            $.ajax({
                type: "POST",
                data: datas,
                url: url + "&" + datas,
                success: function (msg) {
                    if (typeof msg == "object") {
                        msg = $.toJSON(msg);
                    }
                    parent.find(".result").val(msg);
                },
                error: function (e) {
                    parent.find(".result").val(e.responseText);
                    console.log(e);
                }
            });
        }
    </script>
</head>
<body>
<div class="container">
    <h2><?php echo $class; ?></h2>

    <h2><?php echo $objDoc->title($classdocstr); ?></h2>

    <div><?php echo $classdoc["explain"]; ?></div>

    <div class="block">
        <div><span class="tag">作者：</span><?php echo $classdoc["author"]; ?></div>
        <div><span class="tag">创建时间：</span><?php echo $classdoc["create"]; ?></div>
        <div><span class="tag">最后修改：</span><?php echo $classdoc["change"]; ?></div>
        <div><span class="tag">最后修改人：</span><?php echo $classdoc["changeby"]; ?></div>
        <div><span class="tag">邮箱：</span><?php echo $classdoc["email"]; ?></div>
    </div>
    <?php
    //遍历所有的方法
    foreach ($methods as $m) {
        //判断是公有的方法 并且不是继承来的方法
        if ($m->isPublic() && $m->class == $class) {
            $doc = $m->getDocComment();
            $params = $objDoc->getDocParams($doc);
            $returns = $objDoc->getDocReturns($doc);
            $keys = $objDoc->getDocReturnKeys($doc);

            ?>


            <h3><?php echo $m->getName(); ?></h3>
            <h4><?php echo $objDoc->title($doc); ?></h4>
            <div>url</div>
            <div class="block">
                <div>/test.php?c=Doc\demo&f=<?php echo $m->getName(); ?></div>
            </div>
            <div><?php echo $objDoc->explain($doc); ?></div>
            <div>协议</div>
            <div class="block">
                <div><?php echo $objDoc->protocol($doc); ?></div>
            </div>
            <div>参数</div>
            <div class="block params">
                <?php
                if (!is_array($params) || count($params) <= 0) {
                    ?>
                    <div>无</div>
                    <?php
                } else {
                    ?>
                    <div>
                        <label class="index">序号</label>
                        <label class="key">字段</label>
                        <label class="type">类型</label>
                        <label class="default">默认值</label>
                        <label class="demo">实例</label>
                        <label class="explain">说明</label>
                    </div>
                    <?php
                    foreach ($params as $i => $v) {
                        ?>

                        <div>
                            <label class="index"><?php echo($i + 1); ?></label>
                            <label class="key"><?php echo $v["key"]; ?></label>
                            <label class="type"><?php echo $v["type"]; ?></label>
                            <label class="default"><?php echo $v["default"]; ?></label>
                            <label class="demo"><?php echo $v["demo"]; ?></label>
                            <label class="explain"><?php echo $v["explain"]; ?></label>
                        </div>
                        <?php
                    }
                }
                ?>
            </div>


            <div>返回值</div>
            <div class="block">
                <div><span class="tag">类型：</span><?php echo $returns["type"]; ?></div>
                <div><span class="tag">返回实例：</span><?php echo $returns["demo"]; ?></div>
            </div>


            <div>返回字段：</div>
            <div class="block">
                <?php
                $keyindex = 0;
                printkeys($keys, $keyindex);
                ?>
            </div>

            <div>测试</div>
            <div class="block">
                <?php
                foreach ($params as $i => $v) {
                    ?>
                    <div>
                        <span class="tag">参数<?php echo $v["key"]; ?>：</span>

                        <input class='params' name='<?php echo $v["key"]; ?>' type='input'
                               value='<?php echo $v["default"]; ?>'/>
                    </div>
                <?php } ?>
                <div><span class="tag">返回值：</span>


                    <input onclick="ontest(this,'/test.php?c=Doc\\demo&f=<?php echo $m->getName(); ?>')" type='button'
                           value='测试'/>
                </div>
                <div style="padding-left: 115px;"><textarea class='result'></textarea></div>
            </div>

            <?php
        }

    }

    ?>

</div>
</body>
</html>

