<?php
/*
 * Copyright (C) 2020 Tencent Cloud.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'TencentWordpressPluginsSettingActions.php';
require_once TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'class-tencent-cloud-cos-base.php';
class TencentWordpressCOS extends TencentWordpressCosBase {

    private static $initiated = false;
    private static $version = '';
    private static $plugin_type = 'cos';

    /**
     * 初始化函数 单例模式
     */
    public static function init()
    {
        if (!self::$initiated) {
            self::initHooks();
        }
        if (class_exists('TencentWordpressPluginsSettingActions')) {
            TencentWordpressPluginsSettingActions::init();
        }
    }

    /**
     * 绑定插件在Wordpress中的钩子
     */
    private static function initHooks()
    {
        self::$initiated = true;
        self::$version = get_bloginfo('version');

        add_action('admin_notices', array('TencentWordpressCOS', 'HelloTcwpCos'));

        // 更新插件
        add_action('upgrader_process_complete', array('TencentWordpressCOS', 'tcwpcosUpgradeOptions'));

        // 文件名自动重命名为随机字符串
        add_filter('sanitize_file_name', array('TencentWordpressCOS', 'tcwpcosSanitizeFileName'), 10, 1);

        // 避免上传插件/主题时出现同步到COS的情况
        if (substr_count($_SERVER['REQUEST_URI'], '/update.php') <= 0) {
            add_filter('wp_handle_upload', array('TencentWordpressCOS', 'tcwpcosUploadAttachments'));
            if ((float)self::$version < 5.3) {
                add_filter('wp_update_attachment_metadata', array('TencentWordpressCOS', 'tcwpcosUploadAndThumbs'));
            } else {
                add_filter('wp_generate_attachment_metadata', array('TencentWordpressCOS', 'tcwpcosUploadAndThumbs'));
                add_filter('wp_save_image_editor_file', array('TencentWordpressCOS', 'tcwpcosSaveImageEditorFile'));
            }
        }

        // 相同文件名重写,文件名后缀数字加一
        add_filter('wp_unique_filename', array('TencentWordpressCOS', 'tcwpcosUniqueFilename'));

        // 删除文件
        add_action('delete_attachment', array('TencentWordpressCOS', 'tcwpcosDeleteRemoteAttachment'));

        // 针对文章内容中图片数据万象处理
        add_filter('the_content', array('TencentWordpressCOS', 'tcwpcosImageProcessing'));

        // 将插件的配置页面加入到设置列表中
        add_action('admin_menu', array('TencentWordpressCOS', 'tcwpcosAddSettingPage'));

        // 插件列表加入"配置"按钮
        add_filter('plugin_action_links', array('TencentWordpressCOS', 'tcwpcosSetPluginActionLinks'), 10, 2);

        // js脚本引入
        add_action('admin_enqueue_scripts', array('TencentWordpressCOS', 'tcwpcosLoadScriptEnqueue'));

        // 验证空间名称请求处理
        add_action('wp_ajax_check_cos_bucket', array('TencentWordpressCOS', 'tcwpcosCheckBucket'));

        // 将站点中静态文件的URL替换成COS对象存储路径
        add_action('wp_ajax_replace_localurl_to_cosurl', array('TencentWordpressCOS', 'replaceLegacyUrl2CosUrl'));

        // 将站点中静态文件的URL替换成COS对象存储路径
        add_action('wp_ajax_sync_attachment_to_cos', array('TencentWordpressCOS', 'syncLegacyAttachmentToCos'));

        // 保存COS插件配置信息
        add_action('wp_ajax_save_cos_options', array('TencentWordpressCOS', 'tcwpcosSaveOptions'));
    }

    /**
     * 获取数据上报数据
     * @param $action
     * @return mixed
     */
    public static function getTencentCloudWordPressStaticData($action)
    {
        $site_id = TencentWordpressPluginsSettingActions::getWordPressSiteID();
        $site_url = TencentWordpressPluginsSettingActions::getWordPressSiteUrl();
        $site_app = TencentWordpressPluginsSettingActions::getWordPressSiteApp();
        $static_data['action'] = $action;
        $static_data['plugin_type'] = self::$plugin_type;
        $static_data['data'] = array(
            'site_id'  => $site_id,
            'site_url' => $site_url,
            'site_app' => $site_app
        );

        $common_option = get_option(TENCENT_WORDPRESS_COMMON_OPTIONS);
        $tcwpcos_options = get_option(TENCENT_WORDPRESS_COS_OPTIONS);
        if ($tcwpcos_options['customize_secret'] === true && isset($tcwpcos_options['secret_id']) && isset($tcwpcos_options['secret_key'])) {
            $secret_id = $tcwpcos_options['secret_id'];
            $secret_key = $tcwpcos_options['secret_key'];
        } elseif (isset($common_option['secret_id']) && isset($common_option['secret_key'])) {
            $secret_id = $common_option['secret_id'];
            $secret_key = $common_option['secret_key'];
        } else {
            $secret_id = '';
            $secret_key = '';
        }
        $static_data['data']['uin'] = TencentWordpressPluginsSettingActions::getUserUinBySecret($secret_id, $secret_key);

        $static_data['data']['cust_sec_on'] = ((int)$tcwpcos_options['customize_secret']) === 1 ? 1 : 2;

        $others = array(
            'cos_bucket' => $tcwpcos_options['bucket'],
            'cos_region' => $tcwpcos_options['region']
        );
        $static_data['data']['others'] = json_encode($others);
        return $static_data;
    }

    /**
     * 开启插件
     */
    public static function tcwpcosActivatePlugin()
    {
        $options = array(
            'version' => TENCENT_WORDPRESS_COS_VERSION,
            'customize_secret' => false,
            'activation' => false,
            'bucket' => "",
            'region' => "",
            'secret_id' => "",
            'secret_key' => "",
            'no_local_file' => false,
            'cos_url_path' => '',
            'opt' => array(
                'auto_rename' => 0,
                'img_process' => array(
                    'switch' => '',
                    'style_value' => ''
                )
            )
        );

        $tcwpcos_options = self::getCosOptons();
        if (empty($tcwpcos_options)) {
            add_option(TENCENT_WORDPRESS_COS_OPTIONS, $options);
        } else {
            if (isset($tcwpcos_options['opt']['thumbsize'])) {
                $tcwpcos_options = self::setCosThumbsize($tcwpcos_options, true);
            }
            $tcwpcos_options = array_merge($options, $tcwpcos_options);
            $tcwpcos_options['activation'] = true;
            update_option(TENCENT_WORDPRESS_COS_OPTIONS, $tcwpcos_options);
        }

        if (isset($tcwpcos_options['cos_url_path']) && $tcwpcos_options['cos_url_path'] != '') {
            update_option(TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH, $tcwpcos_options['cos_url_path']);
        }
        $plugin = array(
            'plugin_name' => TENCENT_WORDPRESS_COS_SHOW_NAME,
            'nick_name' => TENCENT_WORDPRESS_COS_NICK_NAME,
            'plugin_dir' => TENCENT_WORDPRESS_COS_RELATIVE_PATH,
            'href' => "admin.php?page=tencent_wordpress_plugin_cos",
            'activation' => 'true',
            'status' => 'true',
            'download_url' => ''
        );
        TencentWordpressPluginsSettingActions::prepareTencentWordressPluginsDB($plugin);

        // 第一次开启插件则生成一个全站唯一的站点id，保存在公共的option中
        TencentWordpressPluginsSettingActions::setWordPressSiteID();
        //发送用户体验数据
        $static_data = self::getTencentCloudWordPressStaticData('activate');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);
    }

    /**
     * 禁止插件
     */
    public static function tcwpcosDeactivePlugin()
    {
        $tcwpcos_options = self::getCosOptons();
        $tcwpcos_options['cos_url_path'] = get_option(TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH);
        if (!array_key_exists('opt', $tcwpcos_options)) {
            $tcwpcos_options['opt'] = array(
                'auto_rename' => 0,
                'img_process' => array()
            );
        }
        $tcwpcos_options = self::setCosThumbsize($tcwpcos_options, false);
        $tcwpcos_options['activation'] = false;
        update_option(TENCENT_WORDPRESS_COS_OPTIONS, $tcwpcos_options);
        update_option(TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH, '');
        TencentWordpressPluginsSettingActions::disableTencentWordpressPlugin(TENCENT_WORDPRESS_COS_SHOW_NAME);

        //发送用户体验数据
        $static_data = self::getTencentCloudWordPressStaticData('deactivate');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);
    }

    /**
     * 插件开启提示，在网页的左上角提示插件开启
     */
    public static function HelloTcwpCos()
    {
        if (($GLOBALS['pagenow'] == 'upload.php') || (isset($GLOBALS['_REQUEST']) && isset($GLOBALS['_REQUEST']['page']) && $GLOBALS['_REQUEST']['page'] === 'tencent_wordpress_plugin_cos')) {
            $tcwpcos_options = self::getCosOptons();
            if (isset($tcwpcos_options['activation']) && $tcwpcos_options['activation'] === true) {
                $chosen = '腾讯云对象存储（COS）插件生效中';
            } else {
                $chosen = '腾讯云对象存储（COS）插件启用中';
            }
            echo '<div id="cos_message" class="updated notice is-dismissible" style="margin-bottom: 1%;margin-left:0%;">
                     <p>' . $chosen . '</p>
                 </div>';
        }
    }


    /**
     * 更新插件配置
     */
    public static function tcwpcosUpgradeOptions()
    {
        $tcwpcos_options = self::getCosOptons();
        if (!array_key_exists('opt', $tcwpcos_options)) {
            $tcwpcos_options['opt'] = array(
                'auto_rename' => 0,
                'img_process' => array()
            );
            update_option(TENCENT_WORDPRESS_COS_OPTIONS, $tcwpcos_options);
        }
    }

    /**
     * 文件名自动重命名为随机字符串
     * @param $filename
     * @return string
     */
    public static function tcwpcosSanitizeFileName($filename)
    {
        $tcwpcos_options = self::getCosOptons();
        if ($tcwpcos_options['opt']['auto_rename']) {
            return date("YmdHis") . "" . mt_rand(100, 999) . "." . pathinfo($filename, PATHINFO_EXTENSION);
        } else {
            return $filename;
        }
    }

    /**
     * 上传附件（包括图片的原图）
     * @param $upload  array 附件信息
     * @return mixed
     */
    public static function tcwpcosUploadAttachments($upload)
    {
        $mime_types = get_allowed_mime_types();
        $image_mime_types = array(
            $mime_types['jpg|jpeg|jpe'],
            $mime_types['gif'],
            $mime_types['png'],
            // 默认图片编辑支持以上3种格式
            $mime_types['bmp'],
            $mime_types['tiff|tif'],
            $mime_types['ico'],  // ico格式一般不产生缩略图
        );
        if (!in_array($upload['type'], $image_mime_types)) {
            $key = str_replace(wp_upload_dir()['basedir'], '', $upload['file']);
            $local_path = $upload['file'];
            $tcwpcos_options = self::getCosOptons();
            self::uploadFileToCos($key, $local_path, $tcwpcos_options['no_local_file']);
        }

        return $upload;
    }

    /**
     * 上传图片的缩略图
     * @param $metadata
     * @return mixed
     */
    public static function tcwpcosUploadAndThumbs($metadata)
    {
        $tcwpcos_options = self::getCosOptons();
        $wp_uploads = wp_upload_dir();

        if (isset($metadata['file'])) {
            $attachment_key = '/' . $metadata['file'];
            $attachment_local_path = $wp_uploads['basedir'] . $attachment_key;
            self::uploadFileToCos($attachment_key, $attachment_local_path, $tcwpcos_options['no_local_file']);
        }
        if (isset($metadata['sizes']) && count($metadata['sizes']) > 0) {
            foreach ($metadata['sizes'] as $val) {
                $attachment_thumbs_key = '/' . dirname($metadata['file']) . '/' . $val['file'];
                $attachment_thumbs_local_path = $wp_uploads['basedir'] . $attachment_thumbs_key;
                self::uploadFileToCos($attachment_thumbs_key, $attachment_thumbs_local_path, $tcwpcos_options['no_local_file']);
            }
        }
        return $metadata;
    }

    /**
     * 图片编辑保存时的钩子函数
     * @param $override
     * @return mixed
     */
    public static function tcwpcosSaveImageEditorFile($override)
    {
        add_filter('wp_update_attachment_metadata', array('TencentWordpressCOS', 'tcwpcosImageEditorFileSave'));
        return $override;
    }

    /**
     * 上传编辑后的图片
     * @param $metadata
     * @return mixed
     */
    public static function tcwpcosImageEditorFileSave($metadata)
    {
        $tcwpcos_options = self::getCosOptons();
        $wp_uploads = wp_upload_dir();
        if (isset($metadata['file'])) {
            $attachment_key = '/' . $metadata['file'];
            $attachment_local_path = $wp_uploads['basedir'] . $attachment_key;
            self::uploadFileToCos($attachment_key, $attachment_local_path, $tcwpcos_options['no_local_file']);
        }
        if (isset($metadata['sizes']) && count($metadata['sizes']) > 0) {
            foreach ($metadata['sizes'] as $val) {
                $attachment_thumbs_key = '/' . dirname($metadata['file']) . '/' . $val['file'];
                $attachment_thumbs_local_path = $wp_uploads['basedir'] . $attachment_thumbs_key;
                self::uploadFileToCos($attachment_thumbs_key, $attachment_thumbs_local_path, $tcwpcos_options['no_local_file']);
            }
        }
        remove_filter('wp_update_attachment_metadata', 'tcwpcos_image_editor_file_save');
        return $metadata;
    }

    /**
     * 相同文件名重写,文件名后缀数字加一
     * @param $filename
     * @return string|string[]
     */
    public static function tcwpcosUniqueFilename($filename)
    {
        $ext = '.' . pathinfo($filename, PATHINFO_EXTENSION);
        $number = '';
        while (self::isCosRemoteFileExists(wp_get_upload_dir()['subdir'] . "/$filename")) {
            $new_number = (int)$number + 1;
            if ('' == "$number$ext") {
                $filename = "$filename-" . $new_number;
            } else {
                $filename = str_replace(array("-$number$ext", "$number$ext"), '-' . $new_number . $ext, $filename);
            }
            $number = $new_number;
        }
        return $filename;
    }

    /**
     * 上传cos中的附件
     * @param $post_id
     */
    public static function tcwpcosDeleteRemoteAttachment($post_id)
    {
        $deleteObjects = array();
        $meta = wp_get_attachment_metadata($post_id);  // 以下获取的key都不以/开头, 但该sdk方法必须非/开头
        $upload_url_path = self::getUploadUrlPath();

        // 最后一次编辑生成的图
        if (isset($meta['file'])) {
            $attachment_key = '/' . $meta['file'];
            array_push($deleteObjects, array('Key' => ltrim(self::getCosHanderkey($attachment_key, $upload_url_path), '/'),));
        } else {
            $file = get_attached_file($post_id);
            if ($file) {
                $attached_key = '/' . str_replace(wp_get_upload_dir()['basedir'] . '/', '', $file);
                $deleteObjects[] = array('Key' => ltrim(self::getCosHanderkey($attached_key, $upload_url_path), '/'),);
            }
        }

        // 剪辑图
        if (isset($meta['sizes']) && count($meta['sizes']) > 0) {
            foreach ($meta['sizes'] as $val) {
                $attachment_thumbs_key = '/' . dirname($meta['file']) . '/' . $val['file'];
                $deleteObjects[] = array('Key' => ltrim(self::getCosHanderkey($attachment_thumbs_key, $upload_url_path), '/'),);
            }
        }

        // 原图和剪辑过程中生成的中间文件
        $backup_sizes = get_post_meta($post_id, '_wp_attachment_backup_sizes', true);
        if (is_array($backup_sizes)) {
            foreach ($backup_sizes as $size) {
                $attachment_thumbs_key = '/' . dirname($meta['file']) . '/' . $size['file'];
                $deleteObjects[] = array('Key' => ltrim(self::getCosHanderkey($attachment_thumbs_key, $upload_url_path), '/'),);

            }
        }

        if (!empty($deleteObjects)) {
            $cosClient = self::getCosClient();
            $tcwpcos_options =self::getCosOptons();
            try {
                $cosClient->deleteObjects(array(
                    'Bucket' => esc_attr($tcwpcos_options['bucket']),
                    'Objects' => $deleteObjects,
                ));
            } catch (Exception $ex) {
                echo $ex;
            }
        }
    }

    /**
     * 文章内容中的图片数据万象处理
     * @param $content
     * @return string|string[]|null
     */
    public static function tcwpcosImageProcessing($content)
    {
        $tcwpcos_options = self::getCosOptons();
        if (isset($tcwpcos_options['opt']['img_process']['switch']) && $tcwpcos_options['opt']['img_process']['switch'] === 'on') {
            $media_url = self::getUploadUrlPath();
            $pattern = '#<img[\s\S]*?src\s*=\s*[\"|\'](.*?)[\"|\'][\s\S]*?>#ims';  // img匹配正则
            $content = preg_replace_callback(
                $pattern,
                function ($matches) use ($tcwpcos_options, $media_url) {
                    if (strpos($matches[1], $media_url) === false) {
                        return $matches[0];
                    } else {
                        return str_replace(
                            $matches[1],
                            $matches[1] . '?' . $tcwpcos_options['opt']['img_process']['style_value'],
                            $matches[0]);
                    }
                },
                $content);
        }
        return $content;
    }

    /**
     * 插件列表加入"配置"按钮
     * @param $links string 插件配置页面的链接地址
     * @param $file string 配置页面路径
     * @return mixed
     */
    public static function tcwpcosSetPluginActionLinks($links, $file)
    {
        if ($file == plugin_basename(TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'tencentcloud-cos.php')) {
            $links[] = '<a href="admin.php?page=tencent_wordpress_plugin_cos">设置</a>';
        }
        return $links;
    }

    /**
     * 将插件的配置页面加入到设置列表中
     */
    public static function tcwpcosAddSettingPage()
    {
        if ( !current_user_can( 'manage_options' ) )  {
            wp_die( __( 'Sorry, you are not allowed to manage options for this site.' ) );
        }
        TencentWordpressPluginsSettingActions::addTencentWordpressCommonSettingPage();
        $pagehook = add_submenu_page('TencentWordpressPluginsCommonSettingPage','对象存储','对象存储', 'manage_options', 'tencent_wordpress_plugin_cos', array('TencentWordpressCOS', 'tcwpcosSettingPage'));
        add_action( 'admin_print_styles-'.$pagehook, array('TencentWordpressCOS', 'tcwpcosLoadCssForSettingPage'));
    }

    public static function tcwpcosLoadCssForSettingPage()
    {
        wp_enqueue_style('tencent_cloud_cos_bootstrap_css',TENCENT_WORDPRESS_COS_PLUGIN_CSS_URL.'bootstrap.min.css');
        wp_enqueue_style('tencent_cloud_cos_admin_css',TENCENT_WORDPRESS_COS_PLUGIN_CSS_URL.'admin.css');
    }

    /**
     * 获取自动重命名参数和数据万象配置参数
     * @param array $options 配置参数
     * @return array $tcwpcos_options['opt'] 插件配置参数
     */
    public static function updateCosOptionItemOpt($tcwpcos_options, $options)
    {
        if (isset($options['img_process_switch'])) {
            $tcwpcos_options['opt']['img_process']['switch'] = $options['img_process_switch'];
            $tcwpcos_options['opt']['img_process']['img_process_style_choice'] = $options['img_process_style_choice'];
            if ($options['img_process_style_choice'] == '0') {
                $tcwpcos_options['opt']['img_process']['style_value'] = "watermark/2/text/6IW-6K6v5LqRwrfkuIfosaHkvJjlm74/fill/IzNEM0QzRA/fontsize/20/dissolve/50/gravity/northeast/dx/20/dy/20/batch/1/degree/45";
            } else {
                $tcwpcos_options['opt']['img_process']['style_value'] = sanitize_text_field($options['img_process_style_customize']);
            }
        } else {
            unset($tcwpcos_options['opt']['img_process']['switch']);
        }

        $tcwpcos_options['opt']['auto_rename'] = isset($options['auto_rename']) ? 1 : 0;

        return $tcwpcos_options['opt'];
    }

    /**
     * 更新插件配置信息
     * @param array $options 配置参数
     * @return array $tcwpcos_options 插件配置参数
     */
    public static function updateCosOptions($options)
    {
        $tcwpcos_options = self::getCosOptons();
        if (isset($tcwpcos_options) && count($tcwpcos_options) > 0) {
            foreach ($tcwpcos_options as $k => $v) {
                if ($k == 'no_local_file' || $k == 'customize_secret') {
                    $tcwpcos_options[$k] = isset($options[$k]);
                } elseif ($k == 'opt') {
                    $tcwpcos_options['opt'] = self::updateCosOptionItemOpt($tcwpcos_options, $options);
                } else {
                    if (isset($options[$k]) && $k != 'cos_url_path') {
                        $tcwpcos_options[$k] = sanitize_text_field($options[$k]);
                    }
                }
            }
        }
        // 是否生成缩略图
        $tcwpcos_options = self::setCosThumbsize($tcwpcos_options, isset($options['disable_thumb']));
        return $tcwpcos_options;
    }


    /**
     * 对象 转 数组
     * @param object $obj 对象
     * @return array
     */
    public static function object_to_array($obj)
    {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)self::object_to_array($v);
            }
        }

        return $obj;
    }

    /**
     * 插件配置信息操作页面
     */
    public static function tcwpcosSettingPage()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('Sorry, you are not allowed to manage options for this site.'));
        }
        include TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'tencentcloud-plugin-cos-setting-page.php';
    }

    /**
     * 加载js脚本
     */
    public static function tcwpcosLoadScriptEnqueue()
    {
        wp_register_script('back_admin_txwp_cos_setting', TENCENT_WORDPRESS_COS_PLUGIN_JS_URL . 'txwp_cos_setting.js', array('jquery'), '2.1', true);
        wp_enqueue_script('back_admin_txwp_cos_setting');
    }

    /**
     * 检查"空间名称"是否正确
     */
    public static function tcwpcosCheckBucket()
    {
        $region = sanitize_text_field($_POST['region']);
        $bucketName = sanitize_text_field($_POST['bucket']);
        $tcwpcos_options = self::getCosOptons();
        if (isset($tcwpcos_options) && isset($tcwpcos_options['customize_secret']) && $tcwpcos_options['customize_secret'] === false) {
            $tcwp_common_options = get_option('tencent_wordpress_common_options');
            $secretId = sanitize_text_field($tcwp_common_options['secret_id']);
            $secretKey = sanitize_text_field($tcwp_common_options['secret_key']);
        } else {
            $secretId = sanitize_text_field($_POST['secret_id']);
            $secretKey = sanitize_text_field($_POST['secret_key']);
        }
        $options = array(
            'region' => $region,
            'secret_id' => $secretId,
            'secret_key' => $secretKey,
            'bucket' => $bucketName
        );

        if (self::checkCosBucket($options)) {
            wp_send_json_success();
        } else {
            wp_send_json_error();
        }
    }

    /**
     * 将站点中静态文件的URL替换成COS对象存储路径
     * @return bool|mixed|void
     */
    public static function replaceLegacyUrl2CosUrl()
    {
        global $wpdb;
        $originalContent = home_url('/wp-content/uploads');
        $newContent = self::getUploadUrlPath();
        if (empty($originalContent) || empty($newContent)) {
            wp_send_json_error();
        }

        // 文章内容文字/字符替换
        $result = $wpdb->query(
            "UPDATE {$wpdb->prefix}posts SET `post_content` = REPLACE( `post_content`, '{$originalContent}', '{$newContent}');"
        );

        if ($result >= 0) {
            wp_send_json_success(array('replace' => $result));
        }
        wp_send_json_error();
    }

    /**
     * 将站点中静态文件的URL替换成COS对象存储路径
     * @return int $i 同步附件的数量
     */
    public static function syncLegacyAttachmentToCos()
    {
        $uploads = wp_upload_dir();
        $synv = self::readDirQueue($uploads['basedir']);
        $i = 0;
        foreach ($synv as $k) {
            if (!self::isCosRemoteFileExists($k['key'])) {
                $tcwpcos_options = self::getCosOptons();
                self::uploadFileToCos($k['key'], $k['filepath'], $tcwpcos_options['no_local_file']);
                $i++;
            }
        }
        if ($i >= 0)  {
            wp_send_json_success(array('replace' => $i));
        }
        wp_send_json_error();
    }

    /**
     * 保存配置参数
     */
    public static function tcwpcosSaveOptions()
    {
        if (empty($_POST) || empty($_POST['formdata'])) {
            wp_send_json_error(array('errMsg' => '参数错误'));
        }

        parse_str($_POST['formdata'], $output);
        $options = array(
            'region'     => sanitize_text_field($output['region']),
            'regionname' => sanitize_text_field($output['regionname']),
            'bucket'     => sanitize_text_field($output['bucket']),
            'upload_url_path' => sanitize_text_field($output['upload_url_path'])
        );

        if (isset($output['auto_rename'])) {
            $options['auto_rename'] = sanitize_text_field($output['auto_rename']);
        }

        if (isset($output['secret_id'])) {
            $options['secret_id'] = sanitize_text_field($output['secret_id']);
        }

        if (isset($output['secret_key'])) {
            $options['secret_key'] = sanitize_text_field($output['secret_key']);
        }

        if (isset($output['customize_secret']) && $output['customize_secret'] == 'on') {
            $options['customize_secret'] = sanitize_text_field($output['auto_rename']);
        }

        if (isset($output['no_local_file'])) {
            $options['no_local_file'] = sanitize_text_field($output['no_local_file']);
        }

        if (isset($output['disable_thumb'])) {
            $options['disable_thumb'] = sanitize_text_field($output['disable_thumb']);
        }

        if (isset($output['img_process_switch'])) {
            $options['img_process_switch'] = sanitize_text_field($output['img_process_switch']);
        }

        if (isset($output['img_process_style_choice'])) {
            $options['img_process_style_choice'] = sanitize_text_field($output['img_process_style_choice']);
        }

        if (isset($output['img_process_style_customize'])) {
            $options['img_process_style_customize'] = sanitize_text_field($output['img_process_style_customize']);
        }

        $tcwpcos_options = self::updateCosOptions($options);
        $tcwpcos_options['activation'] = true;

        update_option(TENCENT_WORDPRESS_COS_OPTIONS, $tcwpcos_options);
        $upload_url_path = sanitize_text_field($output['upload_url_path']);
        update_option(TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH, $upload_url_path);

        //发送用户体验数据
        $static_data = self::getTencentCloudWordPressStaticData('save_config');
        TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);
        wp_send_json_success();
    }
}
