var $jx = jQuery.noConflict();

$jx(document).ready(function(){
    $jx('form.form-core2').each(function(){
        var $form_name = $jx(this).attr('name');
        var $name = $form_name;


        var $l = $jx('form[name="' + $name + '"]').length;
        
        var $id = $jx('form[name="' + $name + '"] input[name="key_id"]').val();
        var $cn = $jx('form[name="' + $name + '"] input[name="key_cn"]').val();

        if ($l > 0) {            
            $jx('form[name="' + $name + '"] img.file-img-preview').on('load', function() {
                var imageObj = $jx(this);
                var $url = $jx(this).attr('src');
                $jx(this).parents('.field-upload').find('i.rotate').attr('data-url', $url);
                $jx(this).slideDown();
            });

            $jx('body').on('click', 'form[name="' + $name + '"] .rotate', function(){
                var $url = $jx(this).attr('data-url') ? $jx(this).attr('data-url') : ''; 
                var $id = $jx(this).attr('rel');
                
                $jx(this).addClass('fa-spin');
                
                $jx.ajax({
                    url: ajaxurl, //AJAX file path – admin_url("admin-ajax.php")
                    type: "POST",
                    data: 'action=form-core_rotate_img&url=' + esc_url($url) + '&key_cn=' + encodeURIComponent($cn) + '&key_id=' + $id,
                    dataType: "json",
                    success: function($data){
                        if ($data['success']) {
                            console.log($data['url']);
                            $jx('img[src^="' + $data['url'] + '"]').attr('src', $data['url'] + '?t' + new Date().getTime());
                            $jx('.field-' + $id + ' img.file-img-preview').attr('src', $data['url'] + '?t' + new Date().getTime());
                            $jx('.field-' + $id + ' input[type="file"]').val('');
                            $jx('.field-' + $id + ' input[class$="-default"]').val($data['url']);
                            $jx('.field-' + $id + ' .rotate').attr('data-url', $data['url']);
                            $jx('.field-' + $id + ' .rotate').removeClass('fa-spin');
                            console.log('====================');
                        }
                        else {
                            alert('An error occured! Please try again..');
                        }
                    },
                    error: function($data, $textStatus, $errorThrown){
                    }
                });

                return false;
            });

            $jx('body').on('change', 'form[name="' + $name + '"] input[type="file"]', function(){
                var $id = $jx(this).attr('id');
                var $is_multiple = $jx(this).attr('multiple');

                if (this.files) {
                    var $files = this.files;
                    var $total_files = this.files.length;

                    for ($i = 0; $i < $total_files; $i++) {
                        var reader = new FileReader();       

                        reader.fileName = this.files[$i]['name'];             
                        reader.onload = function(e) {

                            if ($jx('#' + $id).hasClass('image-only')) {
                                var result = e.target.result;

                                if (result.substring(0, 10) != 'data:image') {
                                    alert('Invalid file. Please upload an image only!');
                                    $jx(this).val('');
                                }
                                else {
                                    $jx('form[name="' + $name + '"] .file-upload-preview[rel="' + esc_attr($id) + '"] img').attr('src', e.target.result).slideDown();
                                    
                                    if ($jx('form[name="' + $name + '"] .rotate[rel="' + esc_attr($id) + '"]').hasClass('pre-rotate')) {
                                        $jx('form[name="' + $name + '"] .rotate[rel="' + esc_attr($id) + '"]').slideDown();    
                                    }
                                }
                            }
                            else {
                                var $url = e.target.result; 
                                var $filename = e.target.fileName; 

                                $jx.ajax({
                                    url: ajaxurl, //AJAX file path – admin_url("admin-ajax.php")
                                    type: "POST",
                                    data: 'action=save_tmp_img&url=' + $url + '&filename=' + $filename + '&key_cn=' + encodeURIComponent($cn) + '&key_id=' + $id,
                                    dataType: "json",
                                    success: function($data){
                                        if ($data['success']) {
                                            console.log($data);
                                            // console.log($data['url']);
                                            // $jx('img[src^="' + $data['url'] + '"]').attr('src', $data['url'] + '?t' + new Date().getTime());
                                            // $jx('.field-' + $id + ' img.file-img-preview').attr('src', $data['url'] + '?t' + new Date().getTime());
                                            // $jx('.field-' + $id + ' input[type="file"]').val('');
                                            // $jx('.field-' + $id + ' input[class$="-default"]').val($data['url']);
                                            // $jx('.field-' + $id + ' .rotate').attr('data-url', $data['url']);
                                            // $jx('.field-' + $id + ' .rotate').removeClass('fa-spin');
                                            // console.log('====================');
                                            
                                            if ($is_multiple) {
                                                $jx('form[name="' + $name + '"] .preview-files[rel="' + esc_attr($id) + '"]').append(
                                                    `<div class="preview" style="border: solid #eee 3px; width: 100px; height: 100px; display: inline-block">
                                                        <i rel="` + $id + `" style="display: inline-block;" class="fas fa-sync rotate pre-rotate" data-url="` + $data['image_tmp_url'] + `"></i>
                                                        <button class="clear" rel="photos"><i class="fas fa-times"></i> Clear</button>
                                                        <span class="file-upload-preview" rel="photos" style="display: block">
                                                            <a href="" target="_blank">
                                                                <img src="` + $data['image_tmp_url'] + `" class="file-img-preview" alt="Preview of Photos" style="">
                                                            </a>                    
                                                        </span>
                                                        <input type="hidden" name="<?php echo esc_attr($this->_id) ?>[files][` + $id + `][]" value="` + $data['image_tmp_path'] + `" />
                                                    </div>`);      
                                            }
                                            else {
                                                $jx('form[name="' + $name + '"] .preview-files[rel="' + $id + '"]').html(
                                                    `<div class="preview" style="border: solid #eee 3px; width: 100px; height: 100px; display: inline-block">
                                                        <i rel="` + $id + `" style="display: inline-block;" class="fas fa-sync rotate pre-rotate" data-url="` + $data['image_tmp_url'] + `"></i>
                                                        <button class="clear" rel="photos"><i class="fas fa-times"></i> Clear</button>
                                                        <span class="file-upload-preview" rel="photos" style="display: block">
                                                            <a href="" target="_blank">
                                                                <img src="` + $data['image_tmp_url'] + `" class="file-img-preview" alt="Preview of Photos" style="">
                                                            </a>                    
                                                        </span>
                                                        <input type="hidden" name="<?php echo esc_attr($this->_id) ?>[files][` + $id + `][]" value="` + $data['image_tmp_path'] + `" />
                                                    </div>`);                                                    
                                            }                                      
                                        }
                                        else {
                                            alert('An error occured! Please try again.');
                                        }
                                    },
                                    error: function($data, $textStatus, $errorThrown){
                                    }
                                });
                                if ($jx('form[name="' + $name + '"] .rotate[rel="' + $id + '"]').hasClass('pre-rotate')) {
                                    $jx('form[name="' + $name + '"] .rotate[rel="' + $id + '"]').slideDown();
                                }
                            }
                            
                        }
                        console.log(this.files[$i]);
                        console.log(this.files[$i]['name']);
                        $jx('form[name="' + $name + '"] .rotate[rel="' + $id + '"]').data('file_name', this.files[$i]['name']);
                        reader.readAsDataURL(this.files[$i]); // convert to base64 string
                    } //for 
                } //if
                //console.log($jx(this).val());
            });

            $jx('body').on('click', 'form[name="' + $name + '"] .clear', function(){
                var $id = $jx(this).attr('rel');
                if (confirm('Are you sure you want to clear the value of this field?')) {
                    // $jx('.file-upload-preview[rel="' + $id + '"] img').slideUp();
                    // $jx('input.' + $id + '-default').val('');
                    // $jx('.rotate[rel="' + $id + '"]').data('url', '').slideUp();
                    // $jx('#' + $id).val('');
                    $jx(this).parents('.preview').slideUp();
                }
                return false;
            });

            $jx('body').on('click', 'form[name="' + $name + '"] .field-upload .upload-btn', function() {
                $jx(this).parents('.field-upload').find('input[type="file"]').trigger('click');
                return false;
            });

            $jx('form[name="' + $name + '"] .rating-options').each(function() {
                $jx(this).find('input[type=radio]').each(function(){
                    var $value = this.value;

                    if (this.checked) {
                        $jx(this).parents('.rating-options').find('input').parents('i').removeClass('selected');            

                        for (i = this.value; i >= 1; i--) {
                            console.log('=> ' + i);
                            $jx(this).parents('.rating-options').find('input[value=' + i + ']').parents('i').addClass('selected');
                        }
                    }      
                });
            });

            $jx('body').on('change', 'form[name="' + $name + '"] .rating-options.stars input[type=radio]', function() {
                var $parent = $jx(this).parents('.std-form-line');
                var $value = $jx(this).val();

                $jx($parent).find('.fa-star').removeClass('selected');

                for (i = $value; i >= 1; i--) {
                    $jx($parent).find('.fa-star[data-val="' + (i) + '"]').addClass('selected');
                } //for
            });

            $jx('body').on('mouseenter', 'form[name="' + $name + '"] .rating-options.stars .fa-star', function(){
                const $parent = $jx(this).parents('.std-form-line');
                const $value = $jx(this).data('val');

                $jx($parent).find('.fa-star').removeClass('hover');

                for (i = $value; i >= 1; i--) {
                    $jx($parent).find('.fa-star[data-val="' + (i) + '"]').addClass('hover');
                } //for
            });

            $jx('body').on('mouseleave', 'form[name="' + $name + '"] .rating-options .fa-star', function(){
                const $parent = $jx(this).parents('.std-form-line');
                const $parent_id = $jx(this).parents('.rating-options').attr('id');

                $jx($parent).find('.fa-star').removeClass('hover');
            });

            $jx(function(){
                var $has_color_picker = $jx('.field-colorpicker').length;
                var $has_date_picker = $jx('.field-datepicker').length;

                if ($has_date_picker) {
                    if ($jx.fn.datepicker) {
                        $jx('.jquery-datepicker').datepicker();    
                    }
                    else {
                        alert('Pls. enqueue datepicker()');
                        // wp_enqueue_script('jquery-ui-datepicker');   
                        // wp_enqueue_style('jquery-ui-datepicker-style');                         
                    }
                }

                if ($has_color_picker) {
                    if ($jx.fn.wpColorPicker) {
                        $jx('.wp-color-picker:not(.temp)').wpColorPicker({
                                    /**
                                     * @param {Event} event - standard jQuery event, produced by whichever
                                     * control was changed.
                                     * @param {Object} ui - standard jQuery UI object, with a color member
                                     * containing a Color.js object.
                                     */
                                    change: function (event, ui) {
                                        var element = event.target;
                                        var name = event.target.name;
                                        var color = ui.color.toString();

                                        $jx('[name="' + name + '"]').val(color);

                                        if (color) {
                                            $jx('[name="' + name + '"]').trigger('change');
                                        }
                                    },

                                    /**
                                     * @param {Event} event - standard jQuery event, produced by "Clear"
                                     * button.
                                     */
                                    clear: function (event, ui) {
                                        var element = $jx(this).siblings();
                                        var color = '';
                                        var name = event.target.name;

                                        $jx(this).parents('.wp-picker-container').find('.wp-color-picker.std-input').val(' ');
                                        $jx(this).parents('.wp-picker-container').find('.wp-color-picker.std-input').trigger('change');
                                        $jx(this).parents('.wp-picker-container').find('.wp-color-picker.std-input').val('');
                                        $jx(this).parents('.wp-picker-container').find('.wp-color-picker').removeClass('iris-error');

                                        $jx(this).parents('.border-opts').find('.wp-color-picker.std-input').val(' ');
                                        $jx(this).parents('.border-opts').find('.wp-color-picker.std-input').trigger('change');
                                        $jx(this).parents('.border-opts').find('.wp-color-picker.std-input').val('');
                                        $jx(this).parents('.border-opts').find('.wp-color-picker').removeClass('iris-error');                                        
                                    }
                                }
                            );
                    }
                    else {
                        alert("Pls. enqueue wpColorPicker()");

                        // Front end
                        // wp_enqueue_script(
                        //     'iris',
                        //     admin_url( 'js/iris.min.js' ),
                        //     array( 
                        //         'jquery-ui-draggable', 
                        //         'jquery-ui-slider', 
                        //         'jquery-touch-punch'
                        //     ),
                        //     false,
                        //     1
                        // );
                        // wp_enqueue_script(
                        //     'wp-color-picker',
                        //     admin_url( 'js/color-picker.min.js' ),
                        //     array( 'iris' ),
                        //     false,
                        //     1
                        // );
                        // $colorpicker_l10n = array(
                        //     'clear' => __( 'Clear' ),
                        //     'defaultString' => __( 'Default' ),
                        //     'pick' => __( 'Select Color' ),
                        //     'current' => __( 'Current Color' ),
                        // );
                        
                        // wp_localize_script( 
                        //     'wp-color-picker',
                        //     'wpColorPickerL10n', 
                        //     $colorpicker_l10n 
                        // );      
                        // wp_enqueue_style('wp-color-picker');     
                        
                        // Back End!!!               
                        // wp_enqueue_script(
                        //     'wp-color-picker',
                        //     admin_url( 'js/color-picker.min.js'),
                        //     array( 'iris' ),
                        //     false,
                        //     1
                        // );
                        // wp_enqueue_style('wp-color-picker');                    
                    }                
                }

            
            });

            $jx('form[name="' + $name + '"] .add-to-list').on('click', function(){
                var $parent = $jx(this).parents('.std-form-line.repeater-field');
                var $field = $parent.find('.repeater-data').html();
                var $count = $parent.data('count');
                var $field = $field.replace(/\[-1\]/g, '[' + ($count) + ']');
                var $field = $field.replace(/-1/g, $count);
                var $field = $field.replace(/temp/g, '');
                
                $parent.find(' > .grp').append($field);
                $parent.data('count', parseInt($count)+1);
                
                if (typeof addToListAction !== 'undefined' && typeof addToListAction === 'function') {
                    addToListAction($jx(this));
                }
                
                if ($jx('.wp-color-picker').length > 0) {
                    $jx('.wp-color-picker').wpColorPicker();   
                }
                
                $jx('.grp > textarea:not(.temp):not(.textarea)').each(function(){
                    $editor_id = $jx(this).attr('id');
                    tinymce.init({
                        selector: '#' + $editor_id,
                        theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
                        theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview,|,forecolor,backcolor",
                        theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl,|,fullscreen",                    
                    });
                });
            });

            $jx('body').on('click', '.insert-element', function(){
                var $type = $jx(this).data('el-type');
                var $parent = $jx(this).parents('.std-form-line.repeater-field');
                var $outer_html = $jx("<div />").append($jx('#builder-elements .el-' + $type).clone()).html();
                
                //alert('type ' + $type + ' => ' + $outer_html);
                var $field = '<div id="repeater-item--1" class="repeater-item--1 repeater-item">' + $outer_html + '</div>';
                var $count = $parent.data('count');
                
                $field = $field.replace(/\[-1\]/g, '[' + ($count) + ']');
                $field = $field.replace(/-1/g, $count);
                $field = $field.replace(/temp/g, '');
                
                var $append_to = $jx(this).parents('.repeater-item');
                
                $jx($field).insertAfter($append_to);
                //$parent.find(' > .grp').append($field);
                //$parent.find(' > .grp').sortable();
                $parent.data('count', parseInt($count)+1);

                $jx('.wp-color-picker:not(.temp)').wpColorPicker();
                $jx('.grp > textarea.single:not(.temp)').each(function(){
                    $editor_id = $jx(this).attr('id');
                    tinymce.init({
                        selector: '#' + $editor_id,
                        height: 150,
                        theme: 'modern',
                        menubar:false,
                        statusbar: false,                    
                        plugins: ['lists link image charmap hr',                    
                        'paste textcolor colorpicker'
                        ],
                        toolbar1: 'insertfile undo redo | bold italic | forecolor backcolor | link unlink',
                        image_advtab: true,
                        templates: [
                        { title: 'Test template 1', content: 'Test 1' },
                        { title: 'Test template 2', content: 'Test 2' }
                        ],                    
                    });
                });

                $jx('.grp > textarea.multiple:not(.temp)').each(function(){
                    var $editor_id = $jx(this).attr('id');
                    tinymce.init({
                        selector: '#' + $editor_id,
                        height: 150,
                        theme: 'modern',
                        menubar:false,
                        statusbar: false,                    
                        plugins: ['lists link image charmap hr',                    
                        'paste textcolor colorpicker'
                        ],
                        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link unlink',
                        image_advtab: true,
                        templates: [
                        { title: 'Test template 1', content: 'Test 1' },
                        { title: 'Test template 2', content: 'Test 2' }
                        ],                    
                    });
                });                       
            });
            $jx('body').on('click', '.add-element', function(){
                var $type = $jx(this).data('el-type');
                var $parent = $jx(this).parents('.std-form-line.repeater-field');
                var $outer_html = $jx("<div />").append($jx('#builder-elements .el-' + $type).clone()).html();
                //alert($outer_html);
                
                var $field = '<div id="repeater-item--1" class="repeater-item--1">' + $outer_html + '</div>';
                var $count = $parent.data('count');
                
                $field = $field.replace(/\[-1\]/g, '[' + ($count) + ']');
                $field = $field.replace(/-1/g, $count);
                $field = $field.replace(/temp/g, '');
                
                $parent.find(' > .grp').append($field);
                $parent.find(' > .grp').sortable();
                $parent.data('count', parseInt($count)+1);
                
                $jx('.wp-color-picker:not(.temp)').wpColorPicker();
                $jx('.grp > textarea.single:not(.temp)').each(function(){
                    var $editor_id = $jx(this).attr('id');

                    tinymce.init({
                        selector: '#' + $editor_id,
                        height: 150,
                        theme: 'modern',
                        menubar:false,
                        statusbar: false,                    
                        plugins: ['lists link image charmap hr',                    
                        'paste textcolor colorpicker'
                        ],
                        toolbar1: 'insertfile undo redo | bold italic | forecolor backcolor | link unlink',
                        image_advtab: true,
                        templates: [
                        { title: 'Test template 1', content: 'Test 1' },
                        { title: 'Test template 2', content: 'Test 2' }
                        ],                    
                    });
                });

                $jx('.grp > textarea.multiple:not(.temp)').each(function(){
                    var $editor_id = $jx(this).attr('id');

                    tinymce.init({
                        selector: '#' + $editor_id,
                        height: 150,
                        theme: 'modern',
                        menubar:false,
                        statusbar: false,                    
                        plugins: ['lists link image charmap hr',                    
                        'paste textcolor colorpicker'
                        ],
                        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link unlink',
                        image_advtab: true,
                        templates: [
                        { title: 'Test template 1', content: 'Test 1' },
                        { title: 'Test template 2', content: 'Test 2' }
                        ],                    
                    });
                });                      
            });

            $jx('body').on('click', '.delete-repeater-item', function(){
                var $parent = $jx(this).parents('.std-form-line.repeater-field');
                var $count = $parent.data('count');

                if (confirm('Are you sure you want to delete this item? This cannot be undone.')) {
                    $parent = $jx(this).parents('.std-form-line.repeater-field');
                    $parent.data('count', parseInt($count-1)); 

                    $jx(this).parents('div[class^=\'repeater-item\']').remove();   
                }

                return false;
            });

            var submit_val = '';

            document.success_form = [];

            $jx('body').on('click', 'input[type="submit"]', function(){
                submit_val = $jx(this).val();
            });

            $jx('body').on('submit', 'form[name="' + $form_name + '"]', function($e){
                $e.preventDefault();

                $jx(this).find('input[type="submit"]').attr('disabled', 'disabled').addClass('sending-form');
                $jx('form[name="' + $form_name + '"] .msg').css('display', 'inline');
                $jx('form[name="' + $form_name + '"] .msg').html('<div class="loader"></div>');
                $jx('form[name="' + $form_name + '"] .error-msg').html('');
                $jx('form[name="' + $form_name + '"] .error').removeClass('check').removeClass('active');

                var $form_data = new FormData(this);

                $form_data.append('submit_val', submit_val);
     
                return false;
            }); 
        }
    });

    $jx('body').on('click', '.media-uploader', function(){
        var send_attachment_bkp = wp.media.editor.send.attachment;
        var button = $jx(this);
        var rel = $jx(this).attr('rel');
        var _custom_media = true;

        wp.media.editor.send.attachment = function(props, attachment){
            if (_custom_media) {
                $jx("#" + rel).val(attachment.url);
                if ($jx('.media-preview-' + rel).length) {
                    $jx('.media-preview-' + rel).html('<img src="' + attachment.url + '">');
                }
            }
            else {
                return _orig_send_attachment.apply( this, [props, attachment] );
            }
        }

        wp.media.editor.open(button);
    });  

    
    console.log($jx('.grp > textarea').length);

    $jx('.grp textarea.single:not(.temp)').each(function(){                    
        var $editor_id = $jx(this).attr('id');

        console.log(`Single line textarea ${$editor_id}`);

        tinymce.init({
            selector: '#' + $editor_id,
            height: 150,
            theme: 'modern',
            menubar:false,
            statusbar: false,                    
            plugins: ['lists link image charmap hr',                    
            'paste textcolor colorpicker'
            ],
            toolbar1: 'insertfile undo redo | bold italic | forecolor backcolor | link unlink',
            image_advtab: true,
            templates: [
            { title: 'Test template 1', content: 'Test 1' },
            { title: 'Test template 2', content: 'Test 2' }
            ],                    
        });
    });

    $jx('.grp textarea.multiple:not(.temp)').each(function(){
        var $editor_id = $jx(this).attr('id');

        console.log(`Multiple line textarea ${$editor_id}`);

        tinymce.init({
            selector: '#' + $editor_id,
            height: 150,
            theme: 'modern',
            menubar:false,
            statusbar: false,                    
            plugins: ['lists link image charmap hr',                    
            'paste textcolor colorpicker'
            ],
            toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link unlink',
            image_advtab: true,
            templates: [
            { title: 'Test template 1', content: 'Test 1' },
            { title: 'Test template 2', content: 'Test 2' }
            ],                    
        });

        // tinymce.execCommand('mceRemoveEditor', false, $editor_id);
        // tinymce.execCommand('mceAddEditor', false, $editor_id);
        // quicktags({id : $editor_id});        
    });
    
    $jx('.std-form-line select.has-dependent').each(function(){
        var $val = $jx(this).val();
        var $id = $jx(this).attr('id');

        console.log($val + ' ' + $id);

        $jx('.conditional[rel="' + $id + '"]').hide('fast');

        if ($jx('.conditional.opt-' + $val).length) {
            $jx('.conditional.opt-' + $val).show('slow');
        }
        else {
            $jx('.conditional.opt-else').show('slow');
        }
    });

    $jx('body').on('change', '.std-form-line select.has-dependent', function(){                
        var $val = $jx(this).val();
        var $id = $jx(this).attr('id');

        $jx('.conditional[rel="' + $id + '"]').hide('fast');

        if ($jx('.conditional[rel="' + $id + '"].opt-' + $val).length) {
            $jx('.conditional[rel="' + $id + '"].opt-' + $val).show('slow');
        }
        else {
            $jx('.conditional[rel="' + $id + '"].opt-else').show('slow');
        }
    });     

});