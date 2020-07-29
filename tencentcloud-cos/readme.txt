=== 腾讯云对象存储（COS） ===
Contributors: 腾讯云中小企业产品中心（SMB Product Center of Tencent Cloud）
Donate link: https://cloud.tencent.com/
Tags: 腾讯云wordpress, 腾讯云COS,腾讯云对象存储,腾讯云存储分离,腾讯云存储
Requires at least: 5.0
Tested up to: 5.4.1
Requires PHP: 5.6
Stable tag: 1.0.0
License: Apache 2.0
License URI: http://www.apache.org/licenses/LICENSE-2.0.txt

== Description ==
<strong>tencentcloud-cos，基于腾讯云COS存储在WordPress框架中实现静态资源无缝同步到COS中，提升网站内容访问速度，降低本地存储开销。</strong>
<strong>主要功能：</strong>
1、支持验证桶名是否有效；
2、可配置是否上传缩略图；
3、可配置是否保留本地备份；
4、本地删除可同步删除腾讯云对象存储 COS 中的文件；
5、支持替换数据库中旧的资源链接地址
6、支持腾讯云数据万象 CI 图片处理
7、支持上传文件自动重命名
8、支持同步历史附件到 COS

== Installation ==
1、把tencentcloud-cos文件夹上传到/wp-content/plugins/目录下<br />
2、在后台插件列表中激活腾讯云COS插件<br />
3、在"设置""菜单中输入腾讯云COS对象存储相关参数信息<br />

== Changelog ==
= 1.0.0 =
* 支持验证桶名是否有效；
* 可配置是否上传缩略图；
* 可配置是否保留本地备份；
* 本地删除可同步删除腾讯云对象存储 COS 中的文件；
* 支持替换数据库中旧的资源链接地址
* 支持腾讯云数据万象 CI 图片处理
* 支持上传文件自动重命名
* 支持同步历史附件到 COS