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

class TencentWordpressCosBase
{

    /**
     * 返回cos对象
     * @param array $options 用户自定义插件参数
     * @return \Qcloud\Cos\Client
     */
    public static function getCosClient($options = null)
    {
        $tcwpcos_options = self::getCosOptons();
        if (isset($tcwpcos_options) && isset($tcwpcos_options['customize_secret']) && $tcwpcos_options['customize_secret'] === false) {
            $tcwp_common_options = get_option('tencent_wordpress_common_options');
            $tcwpcos_options['secret_id'] = $tcwp_common_options['secret_id'];
            $tcwpcos_options['secret_key'] = $tcwp_common_options['secret_key'];
        }

        if (isset($options)) {
            $tcwpcos_options = $options;
        }

        return new Qcloud\Cos\Client(
            array(
                'region' => $tcwpcos_options['region'],
                'schema' => (self::isHttps() === true) ? "https" : "http",
                'credentials' => array(
                    'secretId' => $tcwpcos_options['secret_id'],
                    'secretKey' => $tcwpcos_options['secret_key']
                )
            )
        );
    }

    /**
     * 判断是否为https请求
     * @return bool
     */
    public static function isHttps()
    {
        if (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            return true;
        } elseif (!empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        } elseif ($_SERVER['SERVER_PORT'] == 443) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 获取cos配置信息
     */
    public static function getCosOptons()
    {
        return get_option(TENCENT_WORDPRESS_COS_OPTIONS);
    }

    /**
     * 获取文件上传路径
     */
    public static function getUploadUrlPath()
    {
        return get_option(TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH);
    }


    /**
     * 获取对象路径
     * @param $key
     * @param $upload_url_path
     * @return string  string 对象键（Key）是对象在存储桶中的唯一标识
     */
    public static function getCosHanderkey($key, $upload_url_path)
    {
        # 参数2 为了减少option的获取次数
        $url_parse = wp_parse_url($upload_url_path);
        # 约定url不要以/结尾，减少判断条件
        if (array_key_exists('path', $url_parse)) {
            $key = $url_parse['path'] . $key;
        }
        return $key;
    }


    /**
     * 删除本地文件
     * @param $file_path string 本地文件路径
     * @return bool
     */
    public static function deleteLocalFile($file_path)
    {
        try {
            if (!@file_exists($file_path)) {
                TencentCloudCosDebugLog::writeDebugLog('error', 'msg : ' . $file_path . ' file not exist!', __FILE__, __LINE__);
                return false;
            }
            if (!@unlink($file_path)) {
                TencentCloudCosDebugLog::writeDebugLog('error', 'msg : ' . $file_path . ' delete file failed!', __FILE__, __LINE__);
                return false;
            }
            return true;
        } catch (Exception $ex) {
            TencentCloudCosDebugLog::writeDebugLog('error', 'msg : ' . $ex->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * $array array 值为目录路径的数组
     */
    public static function cosFunctionEach(&$array)
    {
        $res = array();
        $key = key($array);
        if ($key !== null) {
            next($array);
            $res[1] = $res['value'] = $array[$key];
            $res[0] = $res['key'] = $key;
        } else {
            $res = false;
        }
        return $res;
    }

    /**
     * 遍历媒体库目录，获取附件的key路径
     * @param $dir string 媒体库目录
     * @return mixed
     */
    public static function readDirQueue($dir)
    {
        $uploads = wp_upload_dir();
        if (isset($dir) && is_dir($dir)) {
            $dd = array();
            $files = array();
            $queue = array($dir);
            while ($data = self::cosFunctionEach($queue)) {
                $path = $data['value'];
                if (is_dir($path) && $handle = opendir($path)) {
                    while ($file = readdir($handle)) {
                        if ($file == '.' || $file == '..' || $file[0] === '.') {
                            continue;
                        }
                        $files[] = $real_path = $path . '/' . $file;
                        if (is_dir($real_path)) {
                            $queue[] = $real_path;
                        }
                    }
                }
                closedir($handle);
            }
            $i = 0;
            foreach ($files as $v) {
                if (!is_dir($v)) {
                    $dd[$i]['filepath'] = $v;
                    $dd[$i]['key'] = explode($uploads['basedir'], $v)[1];
                }
                $i++;
            }
        } else {
            $dd = '';
        }
        return $dd;
    }

    /**
     * 上传文件到腾讯云
     * @param $key
     * @param $file_local_path
     * @param bool $no_local_file
     * @return bool
     */
    public static function uploadFileToCos($key, $file_local_path, $no_local_file = false)
    {
        $cosClient = self::getCosClient();
        $tcwpcos_options = self::getCosOptons();
        $upload_url_path = self::getUploadUrlPath();

        try {
            $file = fopen($file_local_path, 'rb');
            if ($file) {
                $result = $cosClient->Upload(
                    $bucket = $tcwpcos_options['bucket'],
                    $key = self::getCosHanderkey($key, $upload_url_path),
                    $body = $file
                );
                if ($result && $no_local_file) {
                    self::deleteLocalFile($file_local_path);
                }
                return true;
            } else {
                TencentCloudCosDebugLog::writeDebugLog('warring', 'msg : ' . $file_local_path . ' The file path is empty', __FILE__, __LINE__);
            }
        } catch (\Exception $e) {
            TencentCloudCosDebugLog::writeDebugLog('error', 'msg : ' . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * 判断文件是否存储
     * @param $key
     * @return bool
     */
    public static function isCosRemoteFileExists($key)
    {
        $cosClient = self::getCosClient();
        $tcwpcos_options = self::getCosOptons();
        $upload_url_path = self::getUploadUrlPath();
        try {
            $result = $cosClient->headObject(array(
                'Bucket' => $tcwpcos_options['bucket'],
                'Key' => self::getCosHanderkey($key, $upload_url_path),
            ));
            if (is_object($result)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            TencentCloudCosDebugLog::writeDebugLog('error', 'msg : ' . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    /**
     * 检查存储桶是否存在
     * @param $tcwpcos_options
     * @return bool
     */
    public static function checkCosBucket($tcwpcos_options)
    {
        $cosClient = self::getCosClient($tcwpcos_options);
        try {
            $buckets_obj = $cosClient->listBuckets();
            $cos_bucket = esc_attr($tcwpcos_options['bucket']);
            $cos_region = esc_attr($tcwpcos_options['region']);
            if (isset($buckets_obj['Buckets'][0]['Bucket'])) {
                if (isset($buckets_obj['Buckets'][0]['Bucket'][0])) {
                    foreach ($buckets_obj['Buckets'][0]['Bucket'] as $bucket) {
                        if ($cos_bucket == $bucket['Name'] && $cos_region == $bucket['Location']) {
                            return true;
                        } else {
                            continue;
                        }
                    }
                } else {
                    if ($cos_bucket == $buckets_obj['Buckets'][0]['Bucket']['Name']
                        && $cos_region == $buckets_obj['Buckets'][0]['Bucket']['Location']) {
                        return true;
                    } else {
                        return false;
                    }
                }
            }
        } catch (ServiceResponseException $e) {
            TencentCloudCosDebugLog::writeDebugLog('error', 'msg : ' . $e->getMessage(), __FILE__, __LINE__);
            return false;
        }
    }

    public static function setCosThumbsize($tcwpcos_options, $set_thumb)
    {
        if ($set_thumb) {
            $tcwpcos_options['opt']['thumbsize'] = array(
                'thumbnail_size_w' => get_option('thumbnail_size_w'),
                'thumbnail_size_h' => get_option('thumbnail_size_h'),
                'medium_size_w' => get_option('medium_size_w'),
                'medium_size_h' => get_option('medium_size_h'),
                'large_size_w' => get_option('large_size_w'),
                'large_size_h' => get_option('large_size_h'),
                'medium_large_size_w' => get_option('medium_large_size_w'),
                'medium_large_size_h' => get_option('medium_large_size_h'),
            );
            update_option('thumbnail_size_w', 0);
            update_option('thumbnail_size_h', 0);
            update_option('medium_size_w', 0);
            update_option('medium_size_h', 0);
            update_option('large_size_w', 0);
            update_option('large_size_h', 0);
            update_option('medium_large_size_w', 0);
            update_option('medium_large_size_h', 0);
            update_option(TENCENT_WORDPRESS_COS_OPTIONS, $tcwpcos_options);
        } else {
            if (isset($tcwpcos_options['opt']['thumbsize'])) {
                update_option('thumbnail_size_w', $tcwpcos_options['opt']['thumbsize']['thumbnail_size_w']);
                update_option('thumbnail_size_h', $tcwpcos_options['opt']['thumbsize']['thumbnail_size_h']);
                update_option('medium_size_w', $tcwpcos_options['opt']['thumbsize']['medium_size_w']);
                update_option('medium_size_h', $tcwpcos_options['opt']['thumbsize']['medium_size_h']);
                update_option('large_size_w', $tcwpcos_options['opt']['thumbsize']['large_size_w']);
                update_option('large_size_h', $tcwpcos_options['opt']['thumbsize']['large_size_h']);
                update_option('medium_large_size_w', $tcwpcos_options['opt']['thumbsize']['medium_large_size_w']);
                update_option('medium_large_size_h', $tcwpcos_options['opt']['thumbsize']['medium_large_size_h']);
                unset($tcwpcos_options['opt']['thumbsize']);
                update_option(TENCENT_WORDPRESS_COS_OPTIONS, $tcwpcos_options);
            }
        }
        return $tcwpcos_options;
    }
}
