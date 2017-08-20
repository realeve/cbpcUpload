# cbpcUpload
资源服务器

# 配置

1.解压安装/tools/ImageMagick*.7z

2.将dll拷贝至 %phpext%

3.将%phprc%/php.ini 中插件设置打开：

> extension=php_imagick.dll


# 功能说明

1. 原生PHP，自动按年/月、资源类型建立目录
2. 图片缩略图生成
3. webp图像格式支持

# 调用
post

# 返回数据

`
{
    "width": 2000,
    "height": 1200,
    "size": 267.57,
    "type": "images/webp",
    "url": "/assets/2017/08/webp/1503243658_715581PqNzDZ4GgpwnPbgeyWn2Gob2vvH5wgWC.webp",
    "status": 1,
    "msg": "上传成功",
    "name": "league-legends-of-sona-2.jpeg"
}
`