var $jx = jQuery.noConflict();

$jx(document).ready(function(){
    $jx(`form.form-${prefix}`).each(function(){
        let $form_name = $jx(this).attr('name');
        let $name = $form_name;

        console.log('*************** FORM NAME: ' + $form_name);

        $l = $jx('form[name="' + $name + '"]').length;
        
        $id = $jx('form[name="' + $name + '"] input[name="key_id"]').val();
        $cn = $jx('form[name="' + $name + '"] input[name="key_cn"]').val();

        if ($l > 0) {            
            $jx('form[name="' + $name + '"] img.file-img-preview').on('load', function() {
                var imageObj = $jx(this);
                $url = $jx(this).attr('src');
                $jx(this).parents('.field-upload').find('i.rotate').attr('data-url', $url);
                $jx(this).slideDown();
            });
            $jx('body').on('click', 'form[name="' + $name + '"] .rotate', function(){
                $url = $jx(this).attr('data-url') ? $jx(this).attr('data-url') : ''; 
                $id = $jx(this).attr('rel');
                $jx(this).addClass('fa-spin');
                $jx.ajax({
                    url: ajaxurl, //AJAX file path – admin_url("admin-ajax.php")
                    type: "POST",
                    data: 'action=form-src_rotate_img&url=' + esc_url($url) + '&key_cn=' + encodeURIComponent($cn) + '&key_id=' + $id,
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
                console.log('change');

                $id = $jx(this).attr('id');
                $is_multiple = $jx(this).attr('multiple');

                if (this.files) {
                    var $files = this.files;
                    var $total_files = this.files.length;
                    console.log($total_files);
                    for ($i = 0; $i < $total_files; $i++) {
                        var reader = new FileReader();       
                        reader.fileName = this.files[$i]['name'];             
                        reader.onload = function(e) {
                            console.log(e.target);
                            if ($jx('#' + $id).hasClass('image-only')) {
                                result = e.target.result;
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
                                console.log('=> ' + $i);

                                $url = e.target.result; 
                                $filename = e.target.fileName; 

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
                                            
                                            console.log('-> ID: ' + $id);
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
                $id = $jx(this).attr('rel');
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
                    $value = this.value;
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
                $parent = $jx(this).parents('.std-form-line');
                $jx($parent).find('.fa-star').removeClass('selected');
                $value = $jx(this).val();
                for (i = $value; i >= 1; i--) {
                    $jx($parent).find('.fa-star[data-val="' + (i) + '"]').addClass('selected');
                } //for            
            });
            $jx('body').on('mouseenter', 'form[name="' + $name + '"] .rating-options.stars .fa-star', function(){
                $parent = $jx(this).parents('.std-form-line');
                $jx($parent).find('.fa-star').removeClass('hover');
                $value = $jx(this).data('val');
                for (i = $value; i >= 1; i--) {
                    $jx($parent).find('.fa-star[data-val="' + (i) + '"]').addClass('hover');
                } //for
            });
            $jx('body').on('mouseleave', 'form[name="' + $name + '"] .rating-options .fa-star', function(){
                $parent = $jx(this).parents('.std-form-line');
                $parent_id = $jx(this).parents('.rating-options').attr('id');
                $jx($parent).find('.fa-star').removeClass('hover');
            });

            $jx(function(){
                $has_color_picker = $jx('.field-colorpicker').length;
                $has_date_picker = $jx('.field-datepicker').length;

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

                $jx('form[name="' + $name + '"] select.has-dependent').each(function(){
                    $val = $jx(this).val();
                    $id = $jx(this).attr('id');
                    
                    $jx('form[name="' + $name + '"] [rel="' + $id + '"]').hide('fast');
                    if ($jx('form[name="' + $name + '"] .opt-' + $val).length) {
                        $jx('form[name="' + $name + '"] .opt-' + $val).show('slow');
                    }
                    else {
                        $jx('form[name="' + $name + '"] .opt-else').show('slow');
                    }
                });

                $jx('body').on('change', 'form[name="' + $name + '"] select.has-dependent', function(){                
                    $val = $jx(this).val();
                    $id = $jx(this).attr('id');
                    
                    console.log('@@@@@@@ Has Dependent Changed');

                    $jx('form[name="' + $name + '"] [rel="' + $id + '"]').hide('fast');
                    if ($jx('form[name="' + $name + '"] [rel="' + $id + '"].opt-' + $val).length) {
                        $jx('form[name="' + $name + '"] [rel="' + $id + '"].opt-' + $val).show('slow');
                    }
                    else {
                        $jx('form[name="' + $name + '"] [rel="' + $id + '"].opt-else').show('slow');
                    }
                });
                
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
                        toolbar1: 'insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | forecolor backcolor | link unlink',
                        image_advtab: true,
                        templates: [
                        { title: 'Test template 1', content: 'Test 1' },
                        { title: 'Test template 2', content: 'Test 2' }
                        ],                    
                    });
                });            

                $jx('body').on('click', '.media-uploader', function(){
                    var send_attachment_bkp = wp.media.editor.send.attachment;
                    var button = $jx(this);
                    var rel = $jx(this).attr('rel');
                    _custom_media = true;
                    wp.media.editor.send.attachment = function(props, attachment){
                        if ( _custom_media ) {
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
            });
            $jx('form[name="' + $name + '"] .add-to-list').on('click', function(){
                $parent = $jx(this).parents('.std-form-line.repeater-field');
                $field = $parent.find('.repeater-data').html();
                $count = $parent.data('count');
                $field = $field.replace(/\[-1\]/g, '[' + ($count) + ']');
                $field = $field.replace(/-1/g, $count);
                $field = $field.replace(/temp/g, '');
                
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
                $type = $jx(this).data('el-type');
                $parent = $jx(this).parents('.std-form-line.repeater-field');
                var $outer_html = $jx("<div />").append($jx('#builder-elements .el-' + $type).clone()).html();
                
                //alert('type ' + $type + ' => ' + $outer_html);
                $field = '<div id="repeater-item--1" class="repeater-item--1 repeater-item">' + $outer_html + '</div>';
                $count = $parent.data('count');
                $field = $field.replace(/\[-1\]/g, '[' + ($count) + ']');
                $field = $field.replace(/-1/g, $count);
                $field = $field.replace(/temp/g, '');
                
                $append_to = $jx(this).parents('.repeater-item');
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
                $type = $jx(this).data('el-type');
                $parent = $jx(this).parents('.std-form-line.repeater-field');
                var $outer_html = $jx("<div />").append($jx('#builder-elements .el-' + $type).clone()).html();
                //alert($outer_html);
                $field = '<div id="repeater-item--1" class="repeater-item--1">' + $outer_html + '</div>';
                $count = $parent.data('count');
                $field = $field.replace(/\[-1\]/g, '[' + ($count) + ']');
                $field = $field.replace(/-1/g, $count);
                $field = $field.replace(/temp/g, '');
                
                $parent.find(' > .grp').append($field);
                $parent.find(' > .grp').sortable();
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
                $parent = $jx(this).parents('.std-form-line.repeater-field');
                $count = $parent.data('count');
                if (confirm('Are you sure you want to delete this item? This cannot be undone.')) {
                    $parent = $jx(this).parents('.std-form-line.repeater-field');
                    $jx(this).parents('div[class^=\'repeater-item\']').remove();   
                    $parent.data('count', parseInt($count-1)); 
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

                $form_data = new FormData(this);
                $form_data.append('submit_val', submit_val);

                $custom_css = '';

                $jx('#custom-css input[name="element-css-editor[styles][]"]').each(function(){
                    $selector = $jx(this).data('selector');  
                    $apply_custom_css = $jx(this).data('apply-custom-css');
                    $custom_css += '$$$' + $selector + "|" + $apply_custom_css;

                    $fields = ['color', 'color-hover', 'font-family', 'font-weight', 'font-style', 'transform', 'text-align', 'text-decoration', 'text-transform', 
                               'list-style-type', 'list-style-position', 'display', 'float', 'vertical-align', 'box-sizing', 'align-self', 
                               'background-color', 'background-color-hover', 'background-type', 'background-image', 'background-size', 'background-repeat', 'background-position', 'background-attachment',
                                'flex-basis', 'flex-grow', 'show-box-shadow', 'box-shadow'];

                    for ($i in $fields) {                        
                        $value = $jx(this).data($fields[$i]);

                        if ($value === 0 || ($value !== undefined && $value != 'default' && $value != ' ')) {
                            console.log(`${$selector} --- ${$fields[$i]} --- ${$value}`);
                            $custom_css += '|' + $fields[$i] + ":" + $value;    
                        }
                    }

                    $fields_slider = ['font-size', 'letter-spacing', 'word-spacing', 'line-height', 'height', 'width', 'max-width', 'max-height', 'min-height', 'gap', 'flex-basis'];

                    for ($i in $fields_slider) {

                        $value = $jx(this).data($fields_slider[$i]);

                        if ($value && $value !== undefined && $value != 'default') {
                            $unit = $jx(this).data($fields_slider[$i] + '-unit');

                            if (!$unit || $unit === undefined) {
                                $unit = 'px';
                            }

                            $custom_css += '|' + $fields_slider[$i] + ":" + $value + $unit;
                            $custom_css += '|' + $fields_slider[$i] + "-unit:" + $unit;
                        }
                    }

                    // $margin_left_auto = $jx(this).data('margin-left-auto');
                    // $margin_right_auto = $jx(this).data('margin-right-auto');

                    $fields_trbl = ['margin', 'padding'];

                    for ($i in $fields_trbl) {
                        $top = $jx(this).data($fields_trbl[$i] + '-top');
                        $right = $jx(this).data($fields_trbl[$i] + '-right');
                        $bottom = $jx(this).data($fields_trbl[$i] + '-bottom');
                        $left = $jx(this).data($fields_trbl[$i] + '-left');

                        if ($top !== '' && $top !== undefined) {
                            $custom_css += '|' + $fields_trbl[$i] + "-top:" + $top + 'px';
                        }

                        if ($right !== '' && $right !== undefined) {
                            if ($right == 'auto') {
                                $custom_css += '|' + $fields_trbl[$i] + "-right:auto";
                            }
                            else {
                                $custom_css += '|' + $fields_trbl[$i] + "-right:" + $right + 'px';    
                            }
                            
                        }

                        if ($bottom !== ''  && $bottom !== undefined) {
                            $custom_css += '|' + $fields_trbl[$i] + "-bottom:" + $bottom + 'px';
                        }

                        if ($left !== ''  && $left !== undefined) {
                            if ($left == 'auto') {
                                $custom_css += '|' + $fields_trbl[$i] + "-left:auto";
                            }
                            else {
                                $custom_css += '|' + $fields_trbl[$i] + "-left:" + $left + 'px';    
                            }
                        }                                                                        
                    }

                    // Border Rdius 
                    $top = $jx(this).data('border-top-left-radius');
                    $right = $jx(this).data('border-top-right-radius');
                    $bottom = $jx(this).data('border-bottom-left-radius');
                    $left = $jx(this).data('border-bottom-right-radius');

                    if ($top && $top !== undefined) {
                        $custom_css += '|' + "border-top-left-radius:" + $top + 'px';
                    }

                    if ($right && $right !== undefined) {
                        $custom_css += '|' + "border-top-right-radius:" + $right + 'px';
                    }

                    if ($bottom && $bottom !== undefined) {
                        $custom_css += '|' + "border-bottom-left-radius:" + $bottom + 'px';
                    }

                    if ($left && $left !== undefined) {
                        $custom_css += '|' + "border-bottom-right-radius:" + $left + 'px';
                    }

                    // BORDER
                    $border = ['all', 'top', 'bottom', 'left', 'right'];

                    for ($i in $border) {
                        if ($border[$i] == 'all') {
                            $border_style =  $jx(this).data('border-style');
                            $border_width =  $jx(this).data('border-width');
                            $border_color =  $jx(this).data('border-color');
                        }
                        else {
                            $border_style =  $jx(this).data('border-' + $border[$i] + '-style');
                            $border_width =  $jx(this).data('border-' + $border[$i] + '-width');
                            $border_color =  $jx(this).data('border-' + $border[$i] + '-color');
                        }

                        if ($border_style != 'default' && $border_style !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-style:" + $border_style;
                        }

                        if ($border_width && $border_width != 'default' && $border_width !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-width:" + $border_width + 'px';
                        }            

                        if ($border_color != 'default' && $border_color !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-color:" + $border_color;
                        }                        
                    }

                    // BACKGROUND GRADIENT
                    // 'bg-gradient-angle', 'bg-gradient-color1', 'bg-gradient-color1-hover', 'bg-gradient-color2', 'bg-gradient-color2-hover'
                    bg_image_type = $jx(this).data('background-type');
                    bg_gradient_angle = $jx(this).data('bg-gradient-angle');
                    bg_gradient_color1 = $jx(this).data('bg-gradient-color1');
                    bg_gradient_color2 = $jx(this).data('bg-gradient-color2');

                    bg_gradient_color1_hover = $jx(this).data('bg-gradient-color1-hover');
                    bg_gradient_color2_hover = $jx(this).data('bg-gradient-color2-hover');                    


                    if (bg_image_type == 'linear-gradient') {
                        // gradient_bg_image = (bg_gradient_angle ? bg_gradient_angle : 90) + 'deg, ' + (bg_gradient_color1 ? bg_gradient_color1 : '#00000000') + ', ' + (bg_gradient_color2 ? bg_gradient_color2 : '#0000000'); 
                        gradient_bg_image = $jx(this).data('bg-linear-gradient');
                        $custom_css += `|bg-linear-gradient:${gradient_bg_image}`;

                        gradient_bg_image_hover = $jx(this).data('bg-linear-gradient-hover');

                        if (gradient_bg_image_hover) {
                            $custom_css += `|bg-linear-gradient-hover:${gradient_bg_image_hover}`;
                        }
                    }
                });

                $form_data.append('element-css-editor[custom_css]', $custom_css);

                $custom_css = '';

                $jx('#custom-css-tablet input[name="element-css-editor[styles][]"]').each(function(){
                    $selector = $jx(this).data('selector');  
                    $apply_custom_css = $jx(this).data('apply-custom-css');
                    $custom_css += '$$$' + $selector + "|" + $apply_custom_css;

                    $fields = ['color', 'color-hover', 'font-family', 'font-weight', 'font-style', 'transform', 'text-align', 'text-decoration', 'text-transform', 
                               'list-style-type', 'list-style-position', 'display', 'float', 'vertical-align', 'box-sizing', 'align-self', 
                               'background-color', 'background-color-hover', 'background-image', 'background-size', 'background-repeat', 'background-position', 'background-attachment',
                                'flex-basis', 'flex-grow','bg-gradient-angle', 'bg-gradient_color1', 'bg-gradient-color1-hover', 'bg-gradient-color2', 'bg-gradient-color2-hover'];

                    for ($i in $fields) {                        
                        $value = $jx(this).data($fields[$i]);

                        if (Number.isInteger($value) || ($value && $value !== undefined && $value != 'default' && $value != '' && $value != ' ')) {
                            $custom_css += '|' + $fields[$i] + ":" + $value;    
                        }
                    }

                    $fields_slider = ['font-size', 'letter-spacing', 'word-spacing', 'line-height', 'height', 'width', 'max-width', 'max-height', 'min-height', 'gap'];

                    for ($i in $fields_slider) {

                        $value = $jx(this).data($fields_slider[$i]);

                        if ($value && $value !== undefined) {
                            $unit = $jx(this).data($fields_slider[$i] + '-unit');

                            if (!$unit || $unit === undefined) {
                                $unit = 'px';
                            }

                            $custom_css += '|' + $fields_slider[$i] + ":" + $value + $unit;
                            $custom_css += '|' + $fields_slider[$i] + "-unit:" + $unit;
                        }
                    }

                    // $margin_left_auto = $jx(this).data('margin-left-auto');
                    // $margin_right_auto = $jx(this).data('margin-right-auto');

                    $fields_trbl = ['margin', 'padding'];

                    for ($i in $fields_trbl) {
                        $top = $jx(this).data($fields_trbl[$i] + '-top');
                        $right = $jx(this).data($fields_trbl[$i] + '-right');
                        $bottom = $jx(this).data($fields_trbl[$i] + '-bottom');
                        $left = $jx(this).data($fields_trbl[$i] + '-left');
                        

                        if ($top !== '' && $top !== undefined) {
                            $custom_css += '|' + $fields_trbl[$i] + "-top:" + $top + 'px';
                        }

                        if ($right !== '' && $right !== undefined) {
                            if ($right == 'auto') {
                                $custom_css += '|' + $fields_trbl[$i] + "-right:auto";
                            }
                            else {
                                $custom_css += '|' + $fields_trbl[$i] + "-right:" + $right + 'px';    
                            }
                            
                        }

                        if ($bottom !== ''  && $bottom !== undefined) {
                            $custom_css += '|' + $fields_trbl[$i] + "-bottom:" + $bottom + 'px';
                        }

                        if ($left !== ''  && $left !== undefined) {
                            if ($left == 'auto') {
                                $custom_css += '|' + $fields_trbl[$i] + "-left:auto";
                            }
                            else {
                                $custom_css += '|' + $fields_trbl[$i] + "-left:" + $left + 'px';    
                            }
                        }                                                                        
                    }

                    // Border Rdius 
                    $top = $jx(this).data('border-top-left-radius');
                    $right = $jx(this).data('border-top-right-radius');
                    $bottom = $jx(this).data('border-bottom-left-radius');
                    $left = $jx(this).data('border-bottom-right-radius');

                    if ($top && $top !== undefined) {
                        $custom_css += '|' + "border-top-left-radius:" + $top + 'px';
                    }

                    if ($right && $right !== undefined) {
                        $custom_css += '|' + "border-top-right-radius:" + $right + 'px';
                    }

                    if ($bottom && $bottom !== undefined) {
                        $custom_css += '|' + "border-bottom-left-radius:" + $bottom + 'px';
                    }

                    if ($left && $left !== undefined) {
                        $custom_css += '|' + "border-bottom-right-radius:" + $left + 'px';
                    }

                    // BORDER
                    $border = ['all', 'top', 'bottom', 'left', 'right'];

                    for ($i in $border) {
                        if ($border[$i] == 'all') {
                            $border_style =  $jx(this).data('border-style');
                            $border_width =  $jx(this).data('border-width');
                            $border_color =  $jx(this).data('border-color');
                        }
                        else {
                            $border_style =  $jx(this).data('border-' + $border[$i] + '-style');
                            $border_width =  $jx(this).data('border-' + $border[$i] + '-width');
                            $border_color =  $jx(this).data('border-' + $border[$i] + '-color');
                        }

                        if ($border_style != 'default' && $border_style !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-style:" + $border_style;
                        }

                        if ($border_width && $border_width != 'default' && $border_width !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-width:" + $border_width + 'px';
                        }            

                        if ($border_color != 'default' && $border_color !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-color:" + $border_color;
                        }                        
                    }
                });

                $form_data.append('element-css-editor[custom_css_tablet]', $custom_css);

                $custom_css = '';
                $jx('#custom-css-mobile input[name="element-css-editor[styles][]"]').each(function(){
                    $selector = $jx(this).data('selector');  
                    $apply_custom_css = $jx(this).data('apply-custom-css');
                    $custom_css += '$$$' + $selector + "|" + $apply_custom_css;

                    $fields = ['color', 'color-hover', 'font-family', 'font-weight', 'font-style', 'transform', 'text-align', 'text-decoration', 'text-transform', 
                               'list-style-type', 'list-style-position', 'display', 'float', 'vertical-align', 'box-sizing', 'align-self', 
                               'background-color', 'background-color-hover', 'background-image', 'background-size', 'background-repeat', 'background-position', 'background-attachment',
                                'flex-basis', 'flex-grow','bg-gradient-angle', 'bg-gradient_color1', 'bg-gradient-color1-hover', 'bg-gradient-color2', 'bg-gradient-color2-hover'];

                    for ($i in $fields) {                        
                        $value = $jx(this).data($fields[$i]);

                        if (Number.isInteger($value) || ($value && $value !== undefined && $value != 'default' && $value != '' && $value != ' ')) {
                            $custom_css += '|' + $fields[$i] + ":" + $value;    
                        }
                    }

                    $fields_slider = ['font-size', 'letter-spacing', 'word-spacing', 'line-height', 'height', 'width', 'max-width', 'max-height', 'min-height', 'gap'];

                    for ($i in $fields_slider) {

                        $value = $jx(this).data($fields_slider[$i]);

                        if ($value && $value !== undefined) {
                            $unit = $jx(this).data($fields_slider[$i] + '-unit');

                            if (!$unit || $unit === undefined) {
                                $unit = 'px';
                            }

                            $custom_css += '|' + $fields_slider[$i] + ":" + $value + $unit;
                            $custom_css += '|' + $fields_slider[$i] + "-unit:" + $unit;
                        }
                    }

                    // $margin_left_auto = $jx(this).data('margin-left-auto');
                    // $margin_right_auto = $jx(this).data('margin-right-auto');

                    $fields_trbl = ['margin', 'padding'];

                    for ($i in $fields_trbl) {
                        $top = $jx(this).data($fields_trbl[$i] + '-top');
                        $right = $jx(this).data($fields_trbl[$i] + '-right');
                        $bottom = $jx(this).data($fields_trbl[$i] + '-bottom');
                        $left = $jx(this).data($fields_trbl[$i] + '-left');
                        

                        if ($top !== '' && $top !== undefined) {
                            $custom_css += '|' + $fields_trbl[$i] + "-top:" + $top + 'px';
                        }

                        if ($right !== '' && $right !== undefined) {
                            if ($right == 'auto') {
                                $custom_css += '|' + $fields_trbl[$i] + "-right:auto";
                            }
                            else {
                                $custom_css += '|' + $fields_trbl[$i] + "-right:" + $right + 'px';    
                            }
                            
                        }

                        if ($bottom !== ''  && $bottom !== undefined) {
                            $custom_css += '|' + $fields_trbl[$i] + "-bottom:" + $bottom + 'px';
                        }

                        if ($left !== ''  && $left !== undefined) {
                            if ($left == 'auto') {
                                $custom_css += '|' + $fields_trbl[$i] + "-left:auto";
                            }
                            else {
                                $custom_css += '|' + $fields_trbl[$i] + "-left:" + $left + 'px';    
                            }
                        }                                                                        
                    }

                    // Border Rdius 
                    $top = $jx(this).data('border-top-left-radius');
                    $right = $jx(this).data('border-top-right-radius');
                    $bottom = $jx(this).data('border-bottom-left-radius');
                    $left = $jx(this).data('border-bottom-right-radius');

                    if ($top && $top !== undefined) {
                        $custom_css += '|' + "border-top-left-radius:" + $top + 'px';
                    }

                    if ($right && $right !== undefined) {
                        $custom_css += '|' + "border-top-right-radius:" + $right + 'px';
                    }

                    if ($bottom && $bottom !== undefined) {
                        $custom_css += '|' + "border-bottom-left-radius:" + $bottom + 'px';
                    }

                    if ($left && $left !== undefined) {
                        $custom_css += '|' + "border-bottom-right-radius:" + $left + 'px';
                    }

                    // BORDER
                    $border = ['all', 'top', 'bottom', 'left', 'right'];

                    for ($i in $border) {
                        if ($border[$i] == 'all') {
                            $border_style =  $jx(this).data('border-style');
                            $border_width =  $jx(this).data('border-width');
                            $border_color =  $jx(this).data('border-color');
                        }
                        else {
                            $border_style =  $jx(this).data('border-' + $border[$i] + '-style');
                            $border_width =  $jx(this).data('border-' + $border[$i] + '-width');
                            $border_color =  $jx(this).data('border-' + $border[$i] + '-color');
                        }

                        if ($border_style != 'default' && $border_style !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-style:" + $border_style;
                        }

                        if ($border_width && $border_width != 'default' && $border_width !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-width:" + $border_width + 'px';
                        }            

                        if ($border_color != 'default' && $border_color !== undefined) {
                            $custom_css += '|' + "border" + ($border[$i] != 'all' ? '-' + $border[$i] : '') + "-color:" + $border_color;
                        }                        
                    }
                });

                $form_data.append('element-css-editor[custom_css_mobile]', $custom_css);

                $jx.ajax({
                    url: ajaxurl, //AJAX file path – admin_url("admin-ajax.php")
                    type: "POST",
                    data:  $form_data,
                    dataType: "json",
                    contentType: false,
                    cache: false,
                    processData:false,                
                    success: function($data){
                        console.log('Form ' + $form_name);
                        if ($data.success) {                        
                            $jx('form[name="' + $form_name + '"] .msg').html($data.message);
                            
                            if ($data.redirect_to) {
                                window.location = $data.redirect_to;
                                //$jx('.sending-form').removeAttr('disabled').removeClass('sending-form');
                            }
                            else {
                                $jx('form[name="' + $form_name + '"] .sending-form').removeAttr('disabled').removeClass('sending-form');
                                $jx('form[name="' + $form_name + '"] .msg').html(($data.message) ? $data.message : 'Changes saved.');
                            }
                            if ($data['img_previews']) {
                                for (ip in $data['img_previews']) {
                                    $jx('i.rotate[rel="' + ip + '"]').attr('data-url', $data['img_previews'][ip]).slideDown();
                                }
                            }
                        }
                        else {
                            $jx('form[name="' + $form_name + '"] .sending-form').removeAttr('disabled').removeClass('sending-form');                        
                            for ($idx in $data.errors) {
                                $jx('.field-' + $idx).find('.error').addClass('active');
                                $jx('.field-' + $idx).find('.error-msg').html($data.errors[$idx]);
                            }
                            $jx('form[name="' + $form_name + '"] .msg').html(($data.message) ? $data.message : 'No changes has been made!');                        
                        }
                        // if (typeof processFormResults !== 'undefined' && typeof processFormResults === 'function') {
                        //     processFormResults($data);
                        // }
                        
                        if (typeof window[$data.function] !== 'undefined' && typeof window[$data.function] === 'function') {
                            window[$data.function]($data);
                        }
                    },
                    error: function($data, $textStatus, $errorThrown){
                        console.log($data);
                        console.log($textStatus);
                        console.log($errorThrown);

                        $jx('form[name="' + $form_name + '"] .sending-form').removeAttr('disabled').removeClass('sending-form');
                        $jx('form[name="' + $form_name + '"] .msg').html('An error occured. Please try again!');
                    }
                });
     
                return false;
            }); 
        }
    });


});
