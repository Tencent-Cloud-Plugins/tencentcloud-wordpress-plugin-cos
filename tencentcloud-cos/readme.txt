=== 腾讯云对象存储（COS） ===
Contributors: 腾讯云中小企业产品中心（SMB Product Center of Tencent Cloud）
Donate link: https://cloud.tencent.com/
Tags:腾讯云wordpress,腾讯云COS,腾讯云对象存储,腾讯云存储分离,腾讯云存储
Requires at least: 5.0
Tested up to: 5.4.1
Requires PHP: 5.6
Stable tag: 1.0.0
License: Apache 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

tencentcloud-cos，基于腾讯云COS存储在WordPress框架中实现静态资源无缝同步到COS中，提升网站内容访问速度，降低本地存储开销。

== Description ==

tencentcloud-cos，基于腾讯云COS存储在WordPress框架中实现静态资源无缝同步到COS中，提升网站内容访问速度，降低本地存储开销。

## 主要功能

1. 可配置是否上传缩略图；
2. 可配置是否保留本地备份；
3. 本地删除可同步删除腾讯云对象存储 COS 中的文件；
4. 支持替换数据库中旧的资源链接地址
5. 支持腾讯云数据万象 CI 图片处理
6. 支持上传文件自动重命名
7. 支持同步历史附件到 COS

## 致谢

该插件参考了 WordPress 插件 [WPCOS](https://github.com/lezaiyun/wpcos) 及 [Sync QCloud COS](https://github.com/sy-records/wordpress-qcloud-cos) 的实现方法，特此对其主创团队进行致谢。

== Installation ==

1. 把tencentcloud-cos文件夹上传到/wp-content/plugins/目录下
2. 在后台插件列表中激活腾讯云COS插件
3. 在左侧菜单中选择腾讯云设置填写腾讯云COS对象存储相关参数信息

== Changelog ==

= 1.0.0 =

* 可配置是否上传缩略图；
* 可配置是否保留本地备份；
* 本地删除可同步删除腾讯云对象存储 COS 中的文件；
* 支持替换数据库中旧的资源链接地址
* 支持腾讯云数据万象 CI 图片处理
* 支持上传文件自动重命名
* 支持同步历史附件到 COS