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
// Check that the file is not accessed directly.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

$tcwpcos_options = get_option(TENCENT_WORDPRESS_COS_OPTIONS);
$upload_url_path = get_option(TENCENT_WORDPRESS_COS_UPLOAD_URL_PATH);
$ajax_url = admin_url(TENCENT_WORDPRESS_COS_ADMIN_AJAX);

?>

<!--TencentCloud COS Plugin Setting Page-->
<div class="wrap">
    <div class="bs-docs-section">
        <div class="row">
            <div class="col-lg-12">
                <div class="page-header ">
                    <h1 id="forms">腾讯云对象存储（COS）插件</h1>
                </div>
                <p>WordPress静态文件无缝同步腾讯云对象存储COS，提升网站内容访问速度，降低本地存储开销</p>
            </div>
        </div>
        <div class="postbox">
            <div class="row">
                <div class="col-lg-9">
                    <form id="wpcosform_cos_info_set" data-ajax-url="<?php echo $ajax_url ?>" name="tcwpcosform"
                          method="post" class="bs-component">
                        <!-- Setting Option no_local_file-->
                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>自定义密钥</h5>
                            </label>
                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="customize_secret" type="checkbox" class="custom-control-input"
                                       id="customize_secret_information_checkbox_id"
                                <?php
                                    if (isset($tcwpcos_options)
                                        && isset($tcwpcos_options['customize_secret'])
                                        && $tcwpcos_options['customize_secret'] === true) {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label" for="customize_secret_information_checkbox_id">为本插件就配置不同于全局腾讯云密钥的单独密钥</label>
                            </div>
                        </div>
                        <!-- Setting Option SecretId-->
                        <div style="margin-left:20px;">
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>SecretId</h5></label>
                                <input id="input_secret_id" name="secret_id" type="password" class="col-lg-5 is-invalid"
                                       placeholder="SecretId"
                                <?php
                                    if (!isset($tcwpcos_options) || !isset($tcwpcos_options['customize_secret'])
                                        || $tcwpcos_options['customize_secret'] === false) {
                                        echo 'disabled="true"';
                                    }
                                    ?>

                                value="<?php if (isset($tcwpcos_options) && isset($tcwpcos_options['secret_id'])) {
                                           echo esc_attr($tcwpcos_options['secret_id']);
                                       } ?>">

                                <span id="secret_id_change_type" class="dashicons dashicons-hidden"></span>
                                <span id="span_secret_id" class="invalid-feedback offset-lg-2"></span>
                            </div>
                            <!-- Setting Option SecretKey-->
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>SecretKey</h5></label>
                                <input id="input_secret_key" name="secret_key" type="password" class="col-lg-5 is-invalid"
                                       placeholder="SecretKey"
                                <?php
                                    if (!isset($tcwpcos_options) || !isset($tcwpcos_options['customize_secret'])
                                        || $tcwpcos_options['customize_secret'] === false) {
                                        echo 'disabled="true"';
                                    }
                                    ?>
                                value="<?php if (isset($tcwpcos_options) && isset($tcwpcos_options['secret_key'])) {
                                           echo esc_attr($tcwpcos_options['secret_key']);
                                       } ?>">
                                <span id="secret_key_change_type" class="dashicons dashicons-hidden"></span>
                                <span id="span_secret_key" class="invalid-feedback offset-lg-2"></span>
                                <div class="offset-lg-2">
                                    <p>访问 <a href="https://console.qcloud.com/cam/capi" target="_blank">密钥管理</a>获取
                                        SecretId和SecretKey或通过"新建密钥"创建密钥串</p>
                                </div>
                            </div>
                            <!-- Setting Option region-->
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>所属地域</h5></label>
                                <input id="input_region" name="region" type="text" class="col-lg-3 is-invalid"
                                       placeholder=""
                                       value="<?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region'])) {
                                           echo esc_attr($tcwpcos_options['region']);
                                       } ?>">
                                <select id="select_region" name="region" class="select_region_style">
                                    <option value="">请选择所属区域</option>
                                    <option value="ap-beijing-1" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-beijing-1') {
                                        echo ' selected="selected"';
                                    } ?>>北京一区
                                    </option>
                                    <option value="ap-beijing" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-beijing') {
                                        echo ' selected="selected"';
                                    } ?>>北京
                                    </option>
                                    <option value="ap-nanjing" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-nanjing') {
                                        echo ' selected="selected"';
                                    } ?>>南京
                                    </option>
                                    <option value="ap-shanghai" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-shanghai') {
                                        echo ' selected="selected"';
                                    } ?>>上海
                                    </option>
                                    <option value="ap-guangzhou" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-guangzhou') {
                                        echo ' selected="selected"';
                                    } ?>>广州
                                    </option>
                                    <option value="ap-chengdu" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-chengdu') {
                                        echo ' selected="selected"';
                                    } ?>>成都
                                    </option>
                                    <option value="ap-chongqing" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-chongqing') {
                                        echo ' selected="selected"';
                                    } ?>>重庆
                                    </option>
                                    <option value="ap-shenzhen-fsi" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-shenzhen-fsi') {
                                        echo ' selected="selected"';
                                    } ?>>深圳金融
                                    </option>
                                    <option value="ap-shanghai-fsi" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-shanghai-fsi') {
                                        echo ' selected="selected"';
                                    } ?>>上海金融
                                    </option>
                                    <option value="ap-beijing-fsi" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-beijing-fsi') {
                                        echo ' selected="selected"';
                                    } ?>>北京金融
                                    </option>
                                    <option value="ap-hongkong" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-hongkong') {
                                        echo ' selected="selected"';
                                    } ?>>中国香港
                                    </option>
                                    <option value="ap-singapore" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-singapore') {
                                        echo ' selected="selected"';
                                    } ?>>新加坡
                                    </option>
                                    <option value="ap-mumbai" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-mumbai') {
                                        echo ' selected="selected"';
                                    } ?>>孟买
                                    </option>
                                    <option value="ap-seoul" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-seoul') {
                                        echo ' selected="selected"';
                                    } ?>>首尔
                                    </option>
                                    <option value="ap-bangkok" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-bangkok') {
                                        echo ' selected="selected"';
                                    } ?>>曼谷
                                    </option>
                                    <option value="ap-tokyo" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'ap-seoul') {
                                        echo ' selected="selected"';
                                    } ?>>东京
                                    </option>
                                    <option value="na-siliconvalley" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'na-siliconvalley') {
                                        echo ' selected="selected"';
                                    } ?>>硅谷
                                    </option>
                                    <option value="na-ashburn" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'na-ashburn') {
                                        echo ' selected="selected"';
                                    } ?>>弗吉尼亚
                                    </option>
                                    <option value="na-toronto" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'na-toronto') {
                                        echo ' selected="selected"';
                                    } ?>>多伦多
                                    </option>
                                    <option value="eu-frankfurt" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'eu-frankfurt') {
                                        echo ' selected="selected"';
                                    } ?>>法兰克福
                                    </option>
                                    <option value="eu-moscow" <?php if (isset($tcwpcos_options) && isset($tcwpcos_options['region']) && $tcwpcos_options['region'] == 'eu-moscow') {
                                        echo ' selected="selected"';
                                    } ?>>莫斯科
                                    </option>
                                </select>
                                <span id="span_region" class="invalid-feedback offset-lg-2"></span>
                                <div class="offset-lg-2">
                                    <p>可在右边下拉列表中选择所属区域或手动填写，如ap-shanghai</p>
                                    <p>补充说明：此处填写的"所属区域"的值必须和腾讯云对象存储中存储桶的所属区域一致</p>
                                </div>
                            </div>
                            <!-- Setting Option bucket-->
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2"><h5>空间名称</h5></label>
                                <input id="input_bucket" name="bucket" type="text" class="col-lg-5 is-invalid"
                                       placeholder="BUCKET 比如 wordpress-cos-xxxxxx"
                                       value="<?php if (isset($tcwpcos_options) && isset($tcwpcos_options['bucket'])) {
                                           echo esc_attr($tcwpcos_options['bucket']);
                                       } ?>">
                                <span id="span_bucket" class="invalid-feedback offset-lg-2"></span>
                                <div class="offset-lg-2">
                                    <p>首先到<a href="https://console.cloud.tencent.com/cos5/bucket" target="_blank">腾讯云控制台</a>新建bucket存储桶或填写腾讯云"COS"已创建的bucket
                                    </p>
                                    <p> 示范：wordpress-xxxxxx</p>
                                </div>
                            </div>
                            <!-- Setting Option upload_url_path-->
                            <div class="row form-group">
                                <label class="col-form-label col-lg-2" for="inputDefault"><h5>访问域名</h5></label>
                                <input id="input_upload_url_path" name="upload_url_path" type="text" class="col-lg-5"
                                       placeholder="COS域名/自定义目录"
                                       value="<?php if (isset($upload_url_path)) {
                                           echo esc_attr($upload_url_path);
                                       } ?>">
                                <p>
                                    <span id="span_upload_url_path" class="invalid-feedback offset-lg-2"></span>
                                </p>
                                <div class="offset-lg-2">
                                    <p>示范1：https://wordpress-cos-xxxxx.cos.ap-shanghai.myqcloud.com</p>
                                    <p>示范2：https://wordpress-cos-xxxxx.cos.ap-shanghai.myqcloud.com/uploads</p>
                                    <p>补充说明：支持cos自定义域名及(cos域名)/自定义目录</p>
                                </div>
                            </div>
                        </div>
                        <!-- Setting Option auto_rename-->
                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>自动重命名</h5>
                            </label>

                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="auto_rename_switch" type="checkbox" class="custom-control-input"
                                       id="auto_rename_switch"
                                <?php
                                    if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_switch'])
                                        && $tcwpcos_options['opt']['auto_rename_config']['auto_rename_switch'] === 'on') {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label"
                                       for="auto_rename_switch">上传到COS后自动重命名，避免与已有同名文件相冲突</label>
                            </div>
                        </div>
                        <div class="row form-group offset-lg-2">
                            <fieldset>
                                <p id="div_auto_rename" style="display:
                                <?php
                                if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_switch']) &&
                                    $tcwpcos_options['opt']['auto_rename_config']['auto_rename_switch'] === 'on') {
                                    echo 'block';
                                } else {
                                    echo 'none';
                                }
                                ?>;">

                                    <label>
                                        <input id="auto_rename_style_default1" name="auto_rename_style_choice"
                                               type="radio" value="0"
                                        <?php
                                            if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'])
                                                && $tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'] === '0') {
                                                echo 'checked="TRUE"';
                                            }
                                            ?>
                                        > 默认(日期+随机串）
                                    </label>
                                    <br/>

                                    <label>
                                        <input id="auto_rename_style_default2" name="auto_rename_style_choice"
                                               type="radio" value="1"
                                        <?php
                                            if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'])
                                                && $tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'] === '1') {
                                                echo 'checked="TRUE"';
                                            }
                                            ?>
                                        > 格式一（日期+文件名+随机串）
                                    </label>
                                    <br/>

                                    <label>
                                        <input id="auto_rename_style_customize" name="auto_rename_style_choice"
                                               type="radio" value="2"
                                        <?php
                                            if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'])
                                                && $tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'] === '2') {
                                                echo 'checked="TRUE"';
                                            }
                                            ?>
                                        > 格式二（自定义前缀+日期+文件名称+自定义后缀）
                                    </label>
                                    <br/>

                                    <input class="image_process_style_customize" id="auto_rename_style_customize_prefix"
                                           name="auto_rename_customize_prefix" type="text"
                                           placeholder="请填写自定义前缀名(中文/字母/数字/下划线)"

                                    <?php
                                        if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'])
                                            && $tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'] === '2') {
                                            echo 'value=' . esc_attr($tcwpcos_options['opt']['auto_rename_config']['auto_rename_customize_prefix']);
                                        } else {
                                            echo 'value="" disabled="disabled"';
                                        }
                                        ?>
                                    >
                                    <br/>
                                    <input class="image_process_style_customize"
                                           id="auto_rename_style_customize_postfix"
                                           name="auto_rename_customize_postfix" type="text"
                                           placeholder="请填写自定义后缀名(中文/字母/数字/下划线)"
                                    <?php
                                        if (isset($tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'])
                                            && $tcwpcos_options['opt']['auto_rename_config']['auto_rename_style_choice'] === '2') {
                                            echo 'value=' . esc_attr($tcwpcos_options['opt']['auto_rename_config']['auto_rename_customize_postfix']);
                                        } else {
                                            echo 'value="" disabled="disabled"';
                                        }
                                        ?>
                                    >
                                <p>
                                    <span id="auto_rename_error_message" class="invalid-feedback offset-lg-2"></span>
                                </p>
                                </p>
                            </fieldset>
                        </div>
                        <!-- Setting Option no_local_file-->
                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>不在本地保存</h5>
                            </label>
                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="no_local_file" type="checkbox" class="custom-control-input"
                                       id="no_local_file_witch"
                                <?php
                                    if (isset($tcwpcos_options)
                                        && isset($tcwpcos_options['no_local_file'])
                                        && $tcwpcos_options['no_local_file'] === true) {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label" for="no_local_file_witch">上传文件后，静态文件全部同步到COS后删除本地副本，释放本地存储空间</label>
                            </div>
                        </div>

                        <!-- Setting Option keep_cos_file-->
                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>保留远程文件</h5>
                            </label>
                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="keep_cos_file" type="checkbox" class="custom-control-input"
                                       id="keep_cos_file_witch"
                                <?php
                                    if (isset($tcwpcos_options)
                                        && isset($tcwpcos_options['keep_cos_file'])
                                        && $tcwpcos_options['keep_cos_file'] === true) {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label" for="keep_cos_file_witch">删除文件后，只删除本地文件副本，保留远程COS桶中的文件副本</label>
                            </div>

                        </div>

                        <!-- Setting Option disable_thumb-->
                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>禁止缩略图</h5>
                            </label>

                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="disable_thumb" type="checkbox" class="custom-control-input"
                                       id="disable_thumb_switch"
                                <?php
                                    if (isset($tcwpcos_options) && isset($tcwpcos_options['opt']['thumbsize'])) {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label" for="disable_thumb_switch">仅生成和上传主图</label>
                            </div>
                        </div>

                        <!-- Setting Option image_process-->
                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>数据万象</h5>
                            </label>
                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="img_process_switch" type="checkbox" class="custom-control-input"
                                       id="image_process_switch"
                                <?php
                                    if (isset($tcwpcos_options['opt']['img_process']['switch']) &&
                                        $tcwpcos_options['opt']['img_process']['switch'] === "on") {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label" for="image_process_switch">开启数据万象对图片进行编辑，压缩、转换格式、水印添加等操作，
                                    <a href="https://cloud.tencent.com/document/product/436/42215"
                                       target="_blank">了解详情</a></label>
                            </div>
                        </div>

                        <div class="row form-group offset-lg-2">
                            <fieldset>
                                <p id="div_img_process_code" style="display:
                                <?php
                                if (isset($tcwpcos_options['opt']['img_process']['switch']) &&
                                    $tcwpcos_options['opt']['img_process']['switch'] === 'on') {
                                    echo 'block';
                                } else {
                                    echo 'none';
                                }
                                ?>;">
                                    <label>
                                        <input id="img_process_style_default" name="img_process_style_choice"
                                               type="radio" value="0"
                                        <?php
                                            if (isset($tcwpcos_options['opt']['img_process']['img_process_style_choice'])
                                                && $tcwpcos_options['opt']['img_process']['img_process_style_choice'] === '0') {
                                                echo 'checked="TRUE"';
                                            }
                                            ?>
                                        > 默认(文字水印，不可修改)
                                    </label>
                                    <br>
                                    <input class="image_process_style_customize" id="img_word_style_customize_input"
                                           name="img_word_style_customize" type="text" id="word_rule" readonly="readonly"
                                           value="watermark/2/text/6IW-6K6v5LqRwrfkuIfosaHkvJjlm74/fill/IzNEM0QzRA/fontsize/20/dissolve/50/gravity/northeast/dx/20/dy/20/batch/1/degree/45"
                                    >
                                    <br/>
                                    <label>
                                        <input id="img_process_style_customize" name="img_process_style_choice"
                                               type="radio" value="1"
                                        <?php
                                            if (isset($tcwpcos_options['opt']['img_process']['img_process_style_choice'])
                                                && $tcwpcos_options['opt']['img_process']['img_process_style_choice'] === '1') {
                                                echo 'checked="TRUE"';
                                            }
                                            ?>
                                        >自定义规则(下方规则示例将图片处理为渐进显示的jpg格式)
                                    </label>
                                    <br/>
                                    <input class="image_process_style_customize" id="img_process_style_customize_input"
                                           name="img_process_style_customize" type="text" id="rss_rule"
                                           placeholder="规则示例：imageMogr2/format/jpg/interlace/1"
                                    <?php
                                        if (isset($tcwpcos_options['opt']['img_process']['img_process_style_choice'])
                                            && $tcwpcos_options['opt']['img_process']['img_process_style_choice'] === '0') {
                                            echo 'value="" disabled="disabled"';
                                        } else {
                                            echo 'value=' . esc_attr($tcwpcos_options['opt']['img_process']['style_value']);
                                        }
                                        ?>
                                    >
                                </p>
                            </fieldset>
                        </div>

                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left">
                                <h5>文件审核</h5>
                            </label>
                            <div style="margin-top: 20px">
                                <label>您可以通过开启COS存储桶中的内容审核服务，对图片和音视频进行审核，<a
                                        href="https://cloud.tencent.com/document/product/436/45435"
                                        target="_blank">了解详情</a>
                                </label>
                            </div>
                        </div>

                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>文档预览</h5>
                            </label>
                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="attachment_preview_switch" type="checkbox" class="custom-control-input"
                                       id="attachment_preview_switch"
                                <?php
                                    if (isset($tcwpcos_options['opt']['attachment_preview']['switch']) &&
                                        $tcwpcos_options['opt']['attachment_preview']['switch'] === "on") {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label"
                                       for="attachment_preview_switch">开启文档预览服务，在网页中在线展示文档内容，
                                    <a href="https://cloud.tencent.com/document/product/436/45906"
                                       target="_blank">了解详情</a></label>
                                <div>
                                    <p>使用文档预览服务，需要开启存储桶的文档预览服务，点击<a href="https://cloud.tencent.com/document/product/436/45906" target="_blank">前往开启</a></p>
                                </div>
                            </div>

                        </div>

                        <div class="row form-group">
                            <label class="col-form-label col-lg-2 lable_padding_left" for="inputDefault"><h5>调试</h5>
                            </label>
                            <div class="custom-control custom-switch div_custom_switch_padding_top">
                                <input name="automatic_logging" type="checkbox" class="custom-control-input"
                                       id="automatic_logging_switch"
                                <?php
                                    if (isset($tcwpcos_options['opt'], $tcwpcos_options['opt']['automatic_logging'])
                                        && $tcwpcos_options['opt']['automatic_logging'] === 'on') {
                                        echo 'checked="true"';
                                    }
                                    ?>
                                >
                                <label class="custom-control-label" for="automatic_logging_switch">记录错误,异常和警告信息</label>
                            </div>
                        </div>

                        <div class="row form-group offset-lg-2">
                            <fieldset>
                                <p id="div_delete_filelog_code" style="display:
                                <?php
                                if (isset($tcwpcos_options['opt'], $tcwpcos_options['opt']['automatic_logging'])
                                    && $tcwpcos_options['opt']['automatic_logging'] === 'on') {
                                    echo 'block';
                                } else {
                                    echo 'none';
                                }
                                ?>;">
                                    <button id="button_delete_logfile" <?php if (isset($tcwpcos_options['activation']) && $tcwpcos_options['activation'] === false) {
                                        echo 'disabled="disabled"';
                                    } ?> type="button" class="btn btn-warning">清空调试日志
                                    </button>
                                </p>
                                <span id="span_delete_logfile" class="invalid-feedback offset-lg-2"></span>
                            </fieldset>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <button id="button_save" type="button" class="btn btn-primary">保存配置</button>
        <span id="span_button_save" class="invalid-feedback offset-lg-2"></span>
        <hr class="my-4">
        <div class="row">
            <div class="col-lg-9">
                <form id="wpcosform_attachment_sync" name="tcwpcosform_attachment_sync" method="post"
                      class="bs-component">
                    <div class="form-group">
                        <label class="col-form-label col-lg-2" for="inputDefault"><h5>附件同步</h5></label>
                        <button id="form_cos_attachment_sync" <?php if (isset($tcwpcos_options['activation']) && $tcwpcos_options['activation'] === false) {
                            echo 'disabled="disabled"';
                        } ?> type="button" class="btn btn-primary">开始同步
                        </button>
                        <span id="span_attachment_sync" class="invalid-feedback offset-lg-2"></span>
                        <div class="offset-lg-2 cos_attachment_sync">
                            <p>同步媒体库中的全部文件到腾讯云COS，
                                <button id="cos_attachment_sync_link" type="button" class="btn btn-link">了解详情</button>
                            </p>
                            <div id="cos_attachment_sync_desc" style="display:none">
                                <p>1. 初次使用对象存储插件前，站点媒体库中已经有附件，可通过"附件同步"按钮将历史附件同步到腾讯云的存储桶中。</p>
                                <p>2. 附件同步默认只会同步媒体库中的附件。</p>
                                <p>3. 首次同步，执行时间会比较长，有可能会因执行时间过长，页面显示超时或者报错。推荐使用官方的 <a target="_blank" rel="nofollow"
                                                                                                                              href="https://cloud.tencent.com/document/product/436/11366">COSBrowser</a>同步工具将本地文件对应目录上传到COS目录中。
                                </p>
                                <p>4. 如果使用官方的同步工具上传附件，则需保证附件在存储桶中的相对路径和本地的相对路径保持一致。</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <hr class="my-4">
        <div class="row">
            <div class="col-lg-9">
                <form id="wpcosform_cos_info_replace" name="tcwpcosform_cos_info_replace" method="post"
                      class="bs-component">
                    <div class="form-group">
                        <label class="col-form-label col-lg-2" for="inputDefault"><h5>一键替换</h5></label>
                        <button id="form_cos_info_replace" <?php if (isset($tcwpcos_options['activation']) && $tcwpcos_options['activation'] === false) {
                            echo 'disabled="disabled"';
                        } ?> type="button" class="btn btn-primary">开始替换
                        </button>
                        <span id="span_cos_info_replace" class="invalid-feedback offset-lg-2"></span>
                        <div class="offset-lg-2 cos_info_replace">
                            <p>替换网站内容中所有静态文件地址为腾讯云COS文件地址，
                                <button id="cos_info_replace_link" type="button" class="btn btn-link">了解详情</button>
                            </p>
                            <div id="cos_info_replace_desc" style="display:none">
                                <p>1. 初次使用对象存储插件，可以通过上面"一键替换COS地址"按钮快速替换网站内容中的原有图片地址更换为COS地址。</p>
                                <p>2. 建议不熟悉的朋友先备份网站和数据。</p>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="setting_page_footer">
            <a href="https://openapp.qq.com/docs/Wordpress/cos.html" target="_blank">文档中心</a>
            | <a href="https://github.com/Tencent-Cloud-Plugins/tencentcloud-wordpress-plugin-cos" target="_blank">GitHub</a>
            | <a href="https://da.do/y0rp" target="_blank">反馈建议</a>
        </div>
    </div>
</div>

