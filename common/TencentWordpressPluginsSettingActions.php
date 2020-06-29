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
defined('TENCENT_WORDPRESS_COMMON_OPTIONS') or define('TENCENT_WORDPRESS_COMMON_OPTIONS', 'tencent_wordpress_common_options');
require_once TENCENT_WORDPRESS_PLUGINS_COMMON_DIR . 'tencent-wordpress-plugins-setting-page.php';
if (!class_exists('TencentWordpressPluginsSettingActions')) {
    class TencentWordpressPluginsSettingActions
    {
        private static $initiated = false;
        private static $wpdb;
        //表名
        const TABLE_NAME = 'wp_tencent_wordpress_options';
        //插件开启
        const STATUS_OPEN = 'true';
        //插件关闭
        const STATUS_CLOSE = 'false';
        //插件安装
        const ACTIVATION_INSTALL = 'true';
        //插件未安装
        const ACTIVATION_UNINSTALL = 'false';
        //插件名称前缀
        const WP_PLUGIN_PREFIX = 'tencentcloud-plugin';

        /**
         * 初始化函数 单例模式
         */
        public static function init()
        {
            if (!self::$initiated) {
                self::initHooks();
            }
        }

        /**
         * 初始化数据操作对象
         */
        public static function initDatabase()
        {
            if (!self::$wpdb) {
                self::$wpdb = $GLOBALS['wpdb'];
            }
        }

        /**
         * 绑定插件在Wordpress中的钩子
         */
        private static function initHooks()
        {
            self::$initiated = true;

            // 新增且只新增一个"腾讯云设置"菜单
            add_action('admin_menu', array('TencentWordpressPluginsSettingActions', 'tcwpAddTencentWordpressCommonSettingPage'));

            // 保存插件公共配置信息
            add_action('wp_ajax_save_tencent_wordpress_common_options', array('TencentWordpressPluginsSettingActions', 'tcwpSaveTencentWordpressCommonOptions'));

            // 将数据库wp_tencent_wordpress_options表中插件的状态为false
            add_action('wp_ajax_close_tencent_wordpress_plugin', array('TencentWordpressPluginsSettingActions', 'tcwpCloseTencentWordpressPlugin'));

            // 将数据库wp_tencent_wordpress_options表中插件的状态为true
            add_action('wp_ajax_open_tencent_wordpress_plugin', array('TencentWordpressPluginsSettingActions', 'tcwpOpenTencentWordpressPlugin'));
        }

        /**
         * 新增且只新增一个"腾讯云设置"菜单
         */
        public static function tcwpAddTencentWordpressCommonSettingPage()
        {
            global $menu;
            $exists = false;
            foreach ($menu as $menu_item) {
                if ($menu_item['2'] == 'TencentWordpressPluginsCommonSettingPage') {
                    $exists = true;
                }
            }
            if ($exists === false) {
                add_menu_page('腾讯云设置', '腾讯云设置', 'manage_options', 'TencentWordpressPluginsCommonSettingPage', 'tencent_wordpress_plugin_common_page', 'dashicons-admin-site-alt3');
            }
        }

        /**
         * 保存插件公共密钥，确保"TENCENT_WORDPRESS_COMMON_OPTIONS"常量必须存在
         */
        public static function tcwpSaveTencentWordpressCommonOptions()
        {
            $option = array();
            if (empty($_POST['secret_id']) || empty($_POST['secret_key'])) {
                wp_send_json_error();
            }
            $option['secret_id'] = sanitize_text_field($_POST['secret_id']);
            $option['secret_key'] = sanitize_text_field($_POST['secret_key']);
            if (get_option(TENCENT_WORDPRESS_COMMON_OPTIONS)) {
                update_option(TENCENT_WORDPRESS_COMMON_OPTIONS, $option);
            } else {
                add_option(TENCENT_WORDPRESS_COMMON_OPTIONS, $option);
            }
            wp_send_json_success();
        }

        /**
         * 创建保存已安装的腾讯云插件信息
         * @param $tableName string 表名称
         */
        public static function createTencentWordpressPluginsTable($tableName)
        {
            $sql = "CREATE TABLE IF NOT EXISTS `{$tableName}` (
            `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
            `plugin_name` varchar(255) NOT NULL DEFAULT '',
            `nick_name` varchar(255) NOT NULL DEFAULT '',
            `plugin_dir` varchar(255) NOT NULL DEFAULT '',
            `href` varchar(255) NOT NULL DEFAULT '',
            `activation` varchar(32) NOT NULL DEFAULT '',
            `status` varchar(32) NOT NULL DEFAULT '',
            `download_url` varchar(255) DEFAULT '',
            `install_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            `last_modify_datetime` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
            PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta($sql);

        }

        /**
         * 将插件信息保存到数据库表中
         * @param $plugin array 待插入的插件信息
         * @return mixed
         */
        public static function insertCurrentTencentWordpressPluginInfo($plugin)
        {
            $tableName = self::TABLE_NAME;
            $plugin_name = sanitize_text_field($plugin['plugin_name']);
            $nick_name = sanitize_text_field($plugin['nick_name']);
            $plugin_dir = sanitize_text_field($plugin['plugin_dir']);
            $href = sanitize_text_field($plugin['href']);
            $activation = isset($plugin['activation']) ? $plugin['activation'] : 'false';
            $status = isset($plugin['status']) ? $plugin['status'] : 'false';
            $download_url = sanitize_text_field($plugin['download_url']);
            $install_datetime = date('Y-m-d H:i:s');
            $last_modify_datetime = date('Y-m-d H:i:s');
            $sql = "SELECT COUNT(`id`) as `count` FROM `{$tableName}` WHERE `plugin_name` = '%s'";
            $resutl_obj = self::$wpdb->get_row(self::$wpdb->prepare($sql, $plugin_name));
            $count = (int)$resutl_obj->count;

            if ($count === 0) {
                $sql = "INSERT INTO `{$tableName}` (`plugin_name`, `nick_name`, `plugin_dir`, `href`, `activation`, `status`, `download_url`, `install_datetime`, `last_modify_datetime`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s);";
                return self::$wpdb->query(self::$wpdb->prepare(
                    $sql, $plugin_name, $nick_name, $plugin_dir, $href, $activation, $status, $download_url, $install_datetime, $last_modify_datetime
                ));
            } elseif ($count === 1) {
                return self::$wpdb->update($tableName, array('activation' => 'true', 'status' => 'true', 'href' => $href, 'plugin_dir' => $plugin_dir,'nick_name' => $nick_name, 'last_modify_datetime' => $last_modify_datetime), array('plugin_name' => $plugin_name));
            } else {
                self::$wpdb->delete($tableName, array('plugin_name' => $plugin_name));
                $sql = "INSERT INTO `{$tableName}` (`plugin_name`, `nick_name`, `plugin_dir`, `href`, `activation`, `status`, `download_url`, `install_datetime`, `last_modify_datetime`) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s);";
                return self::$wpdb->query(self::$wpdb->prepare(
                    $sql, $plugin_name, $nick_name, $plugin_dir, $href, $activation, $status, $download_url, $install_datetime, $last_modify_datetime
                ));
            }
        }

        /**
         * 插件启动时将插件的信息保存到数据库中
         * @param $plugin array 待插入的插件信息
         */
        public static function prepareTencentWordressPluginsDB($plugin)
        {
            self::initDatabase();
            $tableName = self::TABLE_NAME;
            if (self::$wpdb->get_var("SHOW TABLES LIKE '{$tableName}'") !== $tableName) {
                self::createTencentWordpressPluginsTable($tableName);
            }

            self::insertCurrentTencentWordpressPluginInfo($plugin);
        }

        /**
         * 获取已安装的腾讯云插件信息
         * @return array
         */
        public static function getTencentWordpressPluggins()
        {
            self::initDatabase();
            $tableName = self::TABLE_NAME;
            $sql = "SELECT * FROM `{$tableName}` ORDER BY `id` DESC ";
            $result = self::$wpdb->get_results($sql, ARRAY_A);
            $tencent_plugins = array();
            $all_plugins = get_plugins();
            foreach ($all_plugins as $path => $plugin) {
                $tencent_plugin_prefix = self::WP_PLUGIN_PREFIX;
                if (preg_match("/^$tencent_plugin_prefix/i", $plugin['Name'])) {
                    foreach ($result as $item) {
                        if (strtolower($plugin['Name']) === strtolower($item['plugin_name'])) {
                            $plugin = array_merge($plugin, $item);
                        }
                    }
                    $tencent_plugins[$path] = $plugin;
                }
            }
            return $tencent_plugins;
        }

        /**
         * 禁用插件
         * @param $plugin_name string 插件名称
         */
        public static function disableTencentWordpressPlugin($plugin_name)
        {
            if (empty($plugin_name)) {
                return;
            }
            self::initDatabase();
            $tableName = self::TABLE_NAME;
            $sql = "UPDATE `{$tableName}` SET `status`=%s WHERE `plugin_name`=%s";
            self::$wpdb->query(self::$wpdb->prepare($sql, self::STATUS_CLOSE, $plugin_name));
            return;
        }

        /**
         * 启用插件时更新数据库
         * @param $plugin_name string 插件名称
         */
        public static function enableTencentWordpressPlugin($plugin_name)
        {
            if (empty($plugin_name)) {
                return;
            }
            self::initDatabase();
            $tableName = self::TABLE_NAME;
            $sql = "UPDATE `{$tableName}` SET `status`=%s WHERE `plugin_name`=%s";
            self::$wpdb->query(self::$wpdb->prepare($sql, self::STATUS_OPEN, $plugin_name));
            return;
        }

        /**
         * 删除插件时更新数据库
         * @param $plugin_name string 插件名称
         */
        public static function deleteTencentWordpressPlugin($plugin_name)
        {
            if (empty($plugin_name)) {
                return;
            }
            self::initDatabase();
            $tableName = self::TABLE_NAME;
            self::$wpdb->delete($tableName, array('plugin_name' => $plugin_name));
            return;
        }

        /**
         * 获取已经启动的插件个数
         */
        public static function getActivatePlugin()
        {
            self::initDatabase();
            $tableName = self::TABLE_NAME;
            $plugin_status = self::STATUS_OPEN;
            $sql = "SELECT COUNT(`id`) as `count` FROM `{$tableName}` WHERE `status` = '%s'";
            $resutl_obj = self::$wpdb->get_row(self::$wpdb->prepare($sql, $plugin_status));
            $count = (int)$resutl_obj->count;
            return $count;
        }

        /**
         * 禁用插件
         */
        public static function tcwpCloseTencentWordpressPlugin()
        {
            $plugins_name = sanitize_text_field($_POST['plugin_name']);
            $plugins_dir = sanitize_text_field($_POST['plugin_dir']);
            self::disableTencentWordpressPlugin($plugins_name);
            if (!current_user_can('deactivate_plugin', $plugins_dir)) {
                wp_die(__('Sorry, you are not allowed to deactivate this plugin.'));
            }

            deactivate_plugins($plugins_dir, false, is_network_admin());
            if (!is_network_admin()) {
                update_option('recently_activated', array($plugins_dir => time()) + (array)get_option('recently_activated'));
            } else {
                update_site_option('recently_activated', array($plugins_dir => time()) + (array)get_site_option('recently_activated'));
            }

            $activate_count = self::getActivatePlugin();

            if ($activate_count === 0) {
                $redirect = self_admin_url('plugins.php');
            }
            wp_send_json_success(array('redirect' => $redirect));
        }

        /**
         * 启动插件
         */
        public static function tcwpOpenTencentWordpressPlugin()
        {
            $plugins_name = sanitize_text_field($_POST['plugin_name']);
            $plugins_dir = sanitize_text_field($_POST['plugin_dir']);

            self::enableTencentWordpressPlugin($plugins_name);

            $result = activate_plugin($plugins_dir, false, is_network_admin());

            if (is_wp_error($result)) {
                if ('unexpected_output' == $result->get_error_code()) {
                    $redirect = self_admin_url('plugins.php?error=true&charsout=' . strlen($result->get_error_data()) . '&plugin=' . urlencode($plugins_dir) . "&plugin_status=$status&paged=$page&s=$s");
                    wp_redirect(add_query_arg('_error_nonce', wp_create_nonce('plugin-activation-error_' . $plugins_dir), $redirect));
                    exit;
                } else {
                    wp_die($result);
                }
            }

            if (!is_network_admin()) {
                $recent = (array)get_option('recently_activated');
                unset($recent[$plugins_dir]);
                update_option('recently_activated', $recent);
            } else {
                $recent = (array)get_site_option('recently_activated');
                unset($recent[$plugins_dir]);
                update_site_option('recently_activated', $recent);
            }
            wp_send_json_success();
        }
    }
}
