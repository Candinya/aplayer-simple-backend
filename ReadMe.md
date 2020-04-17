# APlayer-Simple-Backend

## 简要介绍

这是一个为[Aplayer](https://aplayer.js.org)编写的，基于[Meting](https://github.com/metowolf/Meting)作为后端的API样例程式。

由于开发者常用的是网易云音乐，因此对于其他平台暂时没有相应的测试数据。如果不符合您的预期，我们随时欢迎您来为这个项目提供一臂之力。

如果您在调试或是使用过程中有发现任何问题，可以提交一个issue。

## 环境需求

由于是基于Meiting，因此首先要准备Meting。

``` bash
composer require metowolf/meting
```

## 温馨提示

Meting需要 BCMath, cURL 和 OpenSSL 三个PHP插件来工作，因此请准备相应的插件环境。

关于某云音乐的cookie设置需求此处不再赘述，您可以在`api.php`的第5行修改它。

第9行有设置允许跨域请求的域名，默认允许任何域，但是为了安全，建议您修改成您需要使用的域名。

## 调用样例

具体的参数可参见源文件内的注释，此处只给出一个样例。

获取一个网易云歌单：
`https://api.lcy.moe/aplayer.php?p=netease&m=ap&t=playlist&id=歌单的ID`

这样的操作会返回一个JSON格式的歌单，可以直接放入APlayer的audio段。

例如ajax方式获取音乐：

``` javascript
$.ajax({
    type:"get",
    url:"<%- theme.playlist %>",
    dataType:"json",
    success: function(data){
        new APlayer({
            container: document.getElementById('aplayer'),
            fixed: true,
            lrcType: 3,
            audio: data
        });
    }
});
```

一个配置样例可以参见主题[Kratos-Rebirth](https://github.com/Candinya/Kratos-Rebirth)的[layout/_tools/player.ejs](https://github.com/Candinya/Kratos-Rebirth/blob/master/layout/_tools/player.ejs)部分。
