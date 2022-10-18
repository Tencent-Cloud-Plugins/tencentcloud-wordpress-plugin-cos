<?php
/**
 * Plugin Name: tencentcloud-cos
 * Plugin URI: https://openapp.qq.com/
 * Description: 通过腾讯云对象存储服务使网站中静态文件无缝同步腾讯云对象存储COS，提升网站内容访问速度，降低本地存储开销。
 * Version: 1.0.5
 * Author: 腾讯云
 * Author URI: https://cloud.tencent.com/
 *
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

// Check that the file is not accessed directly.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

defined('TENCENT_WORDPRESS_COS_VERSION') or define('TENCENT_WORDPRESS_COS_VERSION', '1.0.5');
defined('TENCENT_WORDPRESS_COS_PLUGIN_DIR') or define('TENCENT_WORDPRESS_COS_PLUGIN_DIR', plugin_dir_path(__FILE__));
defined('TENCENT_WORDPRESS_COS_LOGS') or define('TENCENT_WORDPRESS_COS_LOGS', plugin_dir_path(__FILE__) . 'logs');

defined('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_DIR', TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'common' . DIRECTORY_SEPARATOR);

defined('TENCENT_WORDPRESS_COS_PLUGIN_INC_DIR') or define('TENCENT_WORDPRESS_COS_PLUGIN_INC_DIR', TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'includes' . DIRECTORY_SEPARATOR);
defined('TENCENT_WORDPRESS_COS_PLUGIN_VENDER_DIR') or define('TENCENT_WORDPRESS_COS_PLUGIN_VENDER_DIR', TENCENT_WORDPRESS_COS_PLUGIN_INC_DIR . 'vendor' . DIRECTORY_SEPARATOR);

defined('TENCENT_WORDPRESS_COS_NAME') or define('TENCENT_WORDPRESS_COS_NAME', 'tencentcloud-cos');
defined('TENCENT_WORDPRESS_COS_SHOW_NAME') or define('TENCENT_WORDPRESS_COS_SHOW_NAME', 'tencentcloud-cos');
defined('TENCENT_WORDPRESS_COS_NICK_NAME') or define('TENCENT_WORDPRESS_COS_NICK_NAME', '腾讯云对象存储（COS）插件');
defined('TENCENT_WORDPRESS_PLUGIN_URL') or define('TENCENT_WORDPRESS_PLUGIN_URL', plugins_url() . '/');
defined('TENCENT_WORDPRESS_COS_PLUGIN_URL') or define('TENCENT_WORDPRESS_COS_PLUGIN_URL', TENCENT_WORDPRESS_PLUGIN_URL . basename(__DIR__) . '/');
defined('TENCENT_WORDPRESS_COS_PLUGIN_ASSETS_URL') or define('TENCENT_WORDPRESS_COS_PLUGIN_ASSETS_URL', TENCENT_WORDPRESS_COS_PLUGIN_URL . 'assets' . '/');
defined('TENCENT_WORDPRESS_COS_PLUGIN_JS_URL') or define('TENCENT_WORDPRESS_COS_PLUGIN_JS_URL', TENCENT_WORDPRESS_COS_PLUGIN_ASSETS_URL . 'javascript' . '/');
defined('TENCENT_WORDPRESS_COS_PLUGIN_CSS_URL') or define('TENCENT_WORDPRESS_COS_PLUGIN_CSS_URL', TENCENT_WORDPRESS_COS_PLUGIN_ASSETS_URL . 'css' . '/');

defined('TENCENT_WORDPRESS_PLUGINS_COMMON_URL') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_URL', TENCENT_WORDPRESS_COS_PLUGIN_URL . 'common' . '/');
defined('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL') or define('TENCENT_WORDPRESS_PLUGINS_COMMON_CSS_URL', TENCENT_WORDPRESS_PLUGINS_COMMON_URL . 'css' . '/');

defined('TENCENT_WORDPRESS_COS_OPTIONS') or define('TENCENT_WORDPRESS_COS_OPTIONS', 'tencent_wordpress_cos_options');
defined('TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH') or define('TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH', 'upload_url_path');
defined('TENCENT_WORDPRESS_COS_ADMIN_AJAX') or define('TENCENT_WORDPRESS_COS_ADMIN_AJAX', 'admin-ajax.php');
defined('TENCENT_WORDPRESS_COS_RELATIVE_PATH') or define('TENCENT_WORDPRESS_COS_RELATIVE_PATH', basename(__DIR__) . DIRECTORY_SEPARATOR . basename(__FILE__));

require_once TENCENT_WORDPRESS_COS_PLUGIN_VENDER_DIR . 'autoload.php';
require_once TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'class-tencent-cloud-cos.php';

require(TENCENT_WORDPRESS_COS_PLUGIN_DIR . "tencentcloud-cos-debuger.php");
register_activation_hook(__FILE__, array('TencentWordpressCOS', 'tcwpcosActivatePlugin'));
register_deactivation_hook(__FILE__, array('TencentWordpressCOS', 'tcwpcosDeactivePlugin'));

add_action('init', array('TencentWordpressCOS', 'init'));
