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
//防止恶意访问此文件
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit();
}
defined('TENCENT_WORDPRESS_COS_PLUGIN_DIR') or define('TENCENT_WORDPRESS_COS_PLUGIN_DIR', plugin_dir_path(__FILE__));
require_once TENCENT_WORDPRESS_COS_PLUGIN_DIR . 'tencent-cloud-cos.php';
require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'TencentWordpressPluginsSettingActions.php';
TencentWordpressPluginsSettingActions::deleteTencentWordpressPlugin('tencent-cloud-cos');

//发送用户体验数据
$static_data = TencentWordpressCOS::getTencentCloudWordPressStaticData('uninstall');
TencentWordpressPluginsSettingActions::sendUserExperienceInfo($static_data);

$tcwpcos_options = get_option('tencent_wordpress_cos_options', true);
$upload_url_path = get_option('upload_url_path');
$tcwpcos_upload_url_path = esc_attr($tcwpcos_options['upload_url_path']);

//如果现在使用的是COS的URL，则恢复原状
if ($upload_url_path == $tcwpcos_upload_url_path) {
    update_option('upload_url_path', "");
}

//移除配置
delete_option('tencent_wordpress_cos_options');
