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
jQuery(function ($) {
    var ajaxUrl = $("#wpcosform_cos_info_set").data("ajax-url");

    $("#customize_secret_information_checkbox_id").change(function() {
        var disabled = !($(this).is(':checked'));
        $("#input_secret_id").attr('disabled',disabled);
        $("#input_secret_key").attr('disabled',disabled);
    });

    function change_type(input_element, span_eye) {
        if(input_element[0].type === 'password') {
            input_element[0].type = 'text';
            span_eye.addClass('dashicons-visibility').removeClass('shicons-hidden');
        } else {
            input_element[0].type = 'password';
            span_eye.addClass('shicons-hiddenda').removeClass('dashicons-visibility');
        }
    }

    $('#secret_id_change_type').click(function () {
        change_type($('#input_secret_id'), $('#secret_id_change_type'));
    });

    $('#secret_key_change_type').click(function () {
        change_type($('#input_secret_key'), $('#secret_key_change_type'));
    });

    $('#input_secret_id').blur(function () {
        if ($('#customize_secret_information_checkbox_id')[0].checked === true && !($('#input_secret_id')[0].value)) {
            $('#span_secret_id')[0].innerHTML = "SecretId的值不能为空";
            $('#span_secret_id').show();
        } else {
            $('#span_secret_id').hide();
        }
    });

    $('#input_secret_key').blur(function () {
        if ($('#customize_secret_information_checkbox_id')[0].checked === true && !($('#input_secret_key')[0].value)) {
            $('#span_secret_key')[0].innerHTML = "secretkey的值不能为空";
            $('#span_secret_key').show();
        } else {
            $('#span_secret_key').hide();
        }
    });

    $('#input_region').blur(function () {
        if (!($('#input_region')[0].value)) {
            $('#span_region')[0].innerHTML = "所属地域的值不能为空";
            $('#span_region').show();
        } else {
            $("#select_region option").each(function (){
                if($(this)[0].value === $('#input_region')[0].value){
                    $(this)[0].selected = true;
                } else {
                    $(this)[0].selected = false;
                }
            });
            $('#span_region').hide();
        }
    });

    $('#select_region').change(function () {
        $('#input_region')[0].value = $('#select_region option:selected').attr('value');
        $('#span_region').hide();
    });

    $('#input_bucket').blur(function () {
        var bucket_name = $('#input_bucket')[0].value;
        if (!bucket_name) {
            $('#span_bucket')[0].innerHTML = "空间名称的值不能为空";
            return
        } else {
            var region_name = $('#input_region')[0].value;
            var secret_id = $('#input_secret_id')[0].value;
            var secret_key = $('#input_secret_key')[0].value;

            if ($('#customize_secret_information_checkbox_id')[0].checked === true) {
                if (!secret_id || !secret_key) {
                    $('#span_bucket')[0].innerHTML = "SecretId、SecretKey的值都不能为空！";
                    return
                }
            }

            if (!region_name) {
                $('#span_bucket')[0].innerHTML = "所属地域的值不能为空！";
                return
            }

            $.ajax({
                type: "post",
                url: ajaxUrl,
                dataType:"json",
                data: {
                    action: "check_cos_bucket",
                    region: region_name,
                    secret_id: secret_id,
                    secret_key: secret_key,
                    bucket: bucket_name
                },
                success: function(response) {
                    if (response.success){
                        $('#span_bucket').hide();
                    } else {
                        $('#span_bucket')[0].innerHTML = "空间名称错误，请检查参数是否正确！";
                        $('#span_bucket').show();
                    }
                }
            });
        }
    });

    $('#input_upload_url_path').blur(function () {
        if (!($('#input_upload_url_path')[0].value)) {
            $('#span_upload_url_path')[0].innerHTML = "访问域名的值不能为空";
            $('#span_upload_url_path').show();
        } else {
            $('#span_upload_url_path').hide();
        }
    });

    $('#button_save').click(function () {
        var region_name = $('#input_region')[0].value;
        var bucket_name = $('#input_bucket')[0].value;
        var upload_url_path = $('#input_upload_url_path')[0].value;
        var secret_id = $('#input_secret_id')[0].value;
        var secret_key = $('#input_secret_key')[0].value;
        if ($('#customize_secret_information_checkbox_id')[0].checked == true) {
            if (!secret_id || !secret_key) {
                alert("SecretId、SecretKey的值都不能为空！");
                return false
            }
        }

        if (!region_name || !bucket_name || !upload_url_path) {
            alert("SecretId、SecretKey、所属地域、空间名称和访问域名的值都不能为空！");
            return false;
        }



        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "save_cos_options",
                formdata: $('form#wpcosform_cos_info_set').serialize()
            },
            success: function(response) {
                if (response.success){
                    $('#span_button_save')[0].innerHTML = "保存成功！";

                } else {
                    $('#span_button_save')[0].innerHTML = "保存失败！";
                }
                $('#span_button_save').show().delay(3000).fadeOut();
                setTimeout(location.reload.bind(location), 3000);
                //location.reload();
            }
        });
    });

    $('#form_cos_info_replace').click(function () {
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "replace_localurl_to_cosurl"
            },
            success: function(response) {
                if (response.success){
                    $('#span_cos_info_replace')[0].innerHTML = "成功替换" + response.data.replace + "个COS地址！";
                    $('#span_cos_info_replace').show().delay(5000).fadeOut();
                } else {
                    $('#span_cos_info_replace')[0].innerHTML = "替换失败，请检查本地文件路径和cos地址路径是否正确！";
                    $('#span_cos_info_replace').show().delay(5000).fadeOut();
                }
            }
        });
    });

    $('#form_cos_attachment_sync').click(function () {
        $.ajax({
            type: "post",
            url: ajaxUrl,
            dataType:"json",
            data: {
                action: "sync_attachment_to_cos"
            },
            success: function(response) {
                if (response.success){
                    $('#span_attachment_sync')[0].innerHTML = "成功同步"+ response.data.replace+ "个附件！";
                    $('#span_attachment_sync').show().delay(5000).fadeOut();
                } else {
                    $('#span_attachment_sync')[0].innerHTML = "同步失败，请检查腾讯云COS配置信息、本地文件路径和cos地址路径是否正确！";
                    $('#span_attachment_sync').show().delay(5000).fadeOut();
                }
            }
        });
    });

    $('#image_process_switch').change(function () {
        if ($('#image_process_switch')[0].checked) {
            $('#div_img_process_code').show();
        } else {
            $('#div_img_process_code').hide();
        }
    });

    $('#img_process_style_default').change(function () {
        if ($('#img_process_style_default')[0].checked) {
            $('#img_process_style_customize_input')[0].disabled = true;
            $('#img_process_style_customize_input')[0].value = '';
        }
    });

    $('#img_process_style_customize').change(function () {
        if ($('#img_process_style_customize')[0].checked) {
            $('#img_process_style_customize_input')[0].disabled = false;
        }
    });

    $('#cos_attachment_sync_link').click(function () {
        $('#cos_attachment_sync_desc').toggle();
    });

    $('#cos_info_replace_link').click(function () {
        $('#cos_info_replace_desc').toggle();
    });
});