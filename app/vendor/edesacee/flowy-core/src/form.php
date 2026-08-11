<?php
/**
 * @license proprietary?
 *
 * Modified by edesacee on 11-August-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
namespace DHB\Flowy;

if (!defined('ABSPATH')) exit;

/* Dont forget to call __init() for each class created */
abstract class Form {
    /* version 1.0.7 - FINAL */
    const _TYPE_ALHANUMERIC = 1; /* letters and numbers */
    const _TYPE_DIGIT       = 2; /* numbers only */
    const _TYPE_ALPHABET    = 3; /* letters only */
    const _TYPE_FLOAT       = 4; /* set the number of digits after the decimal point */
    const _TYPE_GENDER      = 5; /* m or f <- value in the database */
    const _FIELD_REQUIRED   = 6; /* field cannot be empty */
    const _SUPPORTED_FIELDS = array();

    public static $__prefix;

    protected $_show_processing_modal = false;
    protected $_validators = array();
    
    protected $_id; /* plugin id */
    protected $_classes = array(); /* plugin class */
    protected $_prefix;
    protected $_data;
    protected $_dbid;
    protected $_values = array();
    protected $_slug;
    protected $_framework_folder;
    protected $_type; /*admin,wp,metabox */
    protected $_disabled; /* admin,wp,metabox */
    protected $_with_file = false;
    protected $_errors = array();

    protected $_conditions = array();
    protected $_has_color_picker = false;
    protected $_has_date_picker = false;
    protected $_fields = array();

    public function __construct($id, $options = array()) {
        $this->_framework_folder = basename(dirname(dirname(__FILE__)));
        $this->_id    = $id;
        $this->_disabled = isset($options['disabled']) && $options['disabled'] ? $options['disabled'] : false;
        $type         = (isset($options['type'])) ? $options['type'] :'';
        
        $this->_slug = isset($options['slug']) ? $options['slug'] : '';

        list($namespace1, $namespace2) = explode('\\', __NAMESPACE__);
        $this->_prefix = 'form-' . strtolower($namespace1);
        
        $this->_show_processing_modal = isset($options['show_processing_modal']) ? $options['show_processing_modal'] : false;
        $this->_classes []= $this->_prefix;
        $this->_classes []= (isset($options['class'])) ? $options['class'] : 'frm-' . uniqid();
        $this->_type = $type;

        if ($type == 'wp') {
            add_action('wp_footer', array($this, 'addScript'), 100);
        }
        else if($type == 'admin' || $type == 'metabox'){
            add_action('admin_footer', array($this, 'addScript'));        
        }  
    }

    public static function __init($prefix) {
        self::$__prefix = $prefix;
        $class = get_class();

        add_action('wp_ajax_form-' . $prefix, array($class, 'ajaxCallSaveForm'));
        add_action('wp_ajax_nopriv_form-' . $prefix, array($class, 'ajaxCallSaveForm2')); 
    }

    public static function __getAllowedTagsForFormField() {
        $allowed_tags = array('button' => array('class' => array(), 'id' => array(), 'type' => array(), 'style' => array()),
                              'i' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'div' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'span' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'label' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'input' => array('class' => array(), 'id' => array(), 'type' => array(), 'placeholder' => array(), 'name' => array(), 'value' => array(), 'style' => array()),
                              'textarea' => array('class' => array(), 'id' => array(), 'placeholder' => array(), 'name' => array(), 'style' => array()),
                              'p' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'h1' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'h2' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'h3' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'h4' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'h5' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'h6' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'img' => array('class' => array(), 'id' => array(), 'alt' => array(), 'src' => array(), 'height' => array(), 'width' => array(), 'style' => array()),
                              'ul' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'li' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'a' => array('class' => array(), 'id' => array(), 'href' => array(), 'style' => array()),
                              'strong' => array('class' => array(), 'id' => array(), 'style' => array()),
                              'em' => array('class' => array(), 'id' => array(), 'style' => array()),
                              );

        return $allowed_tags;   
    }

    public function getErrors() {
        return $this->_errors;
    }

    public function isValid($data) {
        foreach ($this->_validators as $fieldname => $validators) {
            foreach ($validators as $v) {
                $value = $data[$fieldname];

                if ($v == self::_FIELD_REQUIRED && !$value) {
                    $this->_errors [$fieldname] = __('This is a required field.', 'flowy-visual-styler');
                    continue;
                }
                else if ($value) {
                    $is_valid = $this->isValidField($v, $value);
                    if (!$is_valid) {
                        $this->_errors [$fieldname] = $this->_error_messages[$v];
                        continue;
                    }
                }
            }
        }

        if (count($this->_errors) > 0) {
            return false;
        }

        return true;
    }

    public function isValidField($type, $value, $args = array()) {
        switch($type) {
            case 1: /* alphanumeric */
                if (ctype_alnum($value)) {
                    return true;
                }
                break;
            case 2: /* digit */
                if (is_numeric($value)) {
                    return true;
                }
                break;
            case 3: /* letters */
                if (ctype_alpha($value)) {
                    return true;
                }
                break;
            case 4: /* float */
                break;
            case 5: /* gender */
                break;
            default :
                return true;
        }
        return false;
    }
    
    protected function _getValue($db_fieldname, $default = '') {
        $value = (isset($this->_data[$db_fieldname])) ? $this->_data[$db_fieldname] : $default;
        return $value;
    }
    
    public function setValues($data) {
        $this->_data = $data;
    }
    public function setData($name, $value) {
        $v = array($name => $value);
        $this->setValues((object)$v);
    }
    
    public function setDBID($id) {
        $this->_dbid = $id;
    }     
    
    public function getConditions() {
        return $this->_conditions;
    }

    protected function _isConditionMeet($f, $details) {
        $db_fn = str_replace('-', '_', $f);
        $val = $this->_data->$db_fn;
        switch ($details['operator']) {
            case '=':
                if ($val == $details['value']) {
                    return true;
                }
                break;
        }
        
        return false;
    }

    public function isShowField($details = array()) {
        $has_condition = false;
        $conditions = $details;

        $action = $details['action'];
        
        if (is_array($conditions)) {
            $condition_success = false;
            $has_condition = (count($conditions)) > 0 ? true : false;
            foreach ($conditions as $f => $details2) {
                $condition_success = $this->_isConditionMeet($f, $details2);
                $action = $details2['action'];
                if (!$condition_success) {
                    break;
                }
            }
        }
        
        if(!$has_condition || ($has_condition && $action == 'show' && $condition_success) || ($has_condition && $action == 'hide' && !$condition_success)) {
            return true;
        }        
        
        return false;
    }

    public static function ajaxCallSaveForm2() {
        $key_id = isset($_POST['key_id']) ? sanitize_text_field(wp_unslash($_POST['key_id'])) : '';
        $wp_nonce = isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '';

        if (!wp_verify_nonce($wp_nonce, $key_id)) {
            $ret ['success'] = false;
            $ret ['message'] = esc_html__('Invalid access.', 'flowy-visual-styler');

            wp_send_json($ret);
            exit;
        }

        $key_cn = isset($_POST['key_cn']) ? sanitize_text_field(wp_unslash($_POST['key_cn'])) : '';

        $class_name = get_class();
        $class = $class_name::unswapChars($key_cn);
        
        $obj = new $class($key_id);

        if (!$obj) {
            return array('success' => false, 'message' => __('No access allowed!', 'flowy-visual-styler'));
        }

        $db_id = isset($_POST['dbid']) ? sanitize_text_field(wp_unslash($_POST['dbid'])) : 0;

        if ($db_id) {
            $data1['ID'] = $db_id;
        }

        $opt = isset($_POST['opt']) ? sanitize_text_field(wp_unslash($_POST['opt'])) : 0;

        $user_input = isset($_POST[$key_id]) ? map_deep(wp_unslash($_POST[$key_id]), 'sanitize_textarea_field') : ''; //isset($_POST[$key_id]) ? array_map('esc_attr', $_POST[$key_id]) : null;

        $obj = new $class($key_id);
        $data = $obj->saveForm($user_input, $files2);
        $data ['function'] = str_replace('-', '', $key_id) . 'Success';        
        
        /* $data ['class'] = $class_name;
        // $data ['sub_class'] = $class; */

        wp_send_json($data);
        exit;
    }

    /* resize and crop image by center */
    public static function __resizeCropImage($max_width, $max_height, $source_file, $dst_dir, $quality = 80){
        $imgsize = getimagesize($source_file);
        $width = $imgsize[0];
        $height = $imgsize[1];
        $mime = $imgsize['mime'];
     
        switch($mime){
            case 'image/gif':
                $image_create = "imagecreatefromgif";
                $image = "imagegif";
                break;
     
            case 'image/png':
                $image_create = "imagecreatefrompng";
                $image = "imagepng";
                $quality = 7;
                break;
     
            case 'image/jpeg':
                $image_create = "imagecreatefromjpeg";
                $image = "imagejpeg";
                $quality = 80;
                break;
     
            default:
                return false;
                break;
        }

        $dst_img = imagecreatetruecolor($max_width, $max_height);
        $src_img = $image_create($source_file);

        $width_new = $height * $max_width / $max_height;
        $height_new = $width * $max_height / $max_width;

        //if the new width is greater than the actual width of the image, then the height is too large and the rest cut off, or vice versa
        if($width_new > $width){
            //cut point by height
            $h_point = (($height - $height_new) / 2);
            //copy image
            imagecopyresampled($dst_img, $src_img, 0, 0, 0, $h_point, $max_width, $max_height, $width, $height_new);
        }
        else{
            //cut point by width
            $w_point = (($width - $width_new) / 2);
            imagecopyresampled($dst_img, $src_img, 0, 0, $w_point, 0, $max_width, $max_height, $width_new, $height);
        }

        $image($dst_img, $dst_dir, $quality);

        if($dst_img)imagedestroy($dst_img);
        if($src_img)imagedestroy($src_img);
    }

    public static function ajaxCallSaveForm() {
        if(!is_user_logged_in()) {
            exit;
        }

        $key_id = isset($_POST['key_id']) ? sanitize_text_field(wp_unslash($_POST['key_id'])) : '';
        $wp_nonce = isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '';

        if (!wp_verify_nonce($wp_nonce, $key_id)) {
            $ret ['success'] = false;
            $ret ['message'] = esc_html__('Invalid access.', 'flowy-visual-styler');

            wp_send_json($ret);
            exit;
        }

        $key_cn = isset($_POST['key_cn']) ? sanitize_text_field(wp_unslash($_POST['key_cn'])) : '';

        $class_name = get_class();
        $class = $class_name::unswapChars($key_cn);
        $obj = new $class($key_id);

        $db_id = isset($_POST['dbid']) ? sanitize_text_field(wp_unslash($_POST['dbid'])) : 0;
        
        if ($db_id) {
            $data1['ID'] = $db_id;
        }

        $user_input = isset($_POST[$key_id]) ? map_deep(wp_unslash($_POST[$key_id]), 'sanitize_textarea_field') : ''; //isset($_POST[$key_id]) ? array_map('esc_attr', $_POST[$key_id]) : null;

        $data = $obj->saveForm($user_input, $files2);
        $data ['function'] = str_replace('-', '', $key_id) . 'Success';    
        $data ['files'] = $files2;
        
        wp_send_json($data);
        exit;
    }

    public function getForm() {
        $class_name = substr(get_class($this), 4);

        if ($this->_type != 'metabox') {
            $form_elements = $this->getFormElements();
            $form_html = '<form class="dropzone ' . $class_name . ' ' . implode(' ', $this->_classes) . ($this->_disabled ? ' disabled' : '') . '" id="' . $this->_id . '" name="form-' . $this->_id . '" method="post" ' . ($this->_with_file ? ' enctype="multipart/form-data"' : '') . '>';
            $form_html .= $form_elements;
            
            if ($this->_dbid) {
                $form_html .= '<input type="hidden" name="dbid" value="' . $this->_dbid . '" />';
            }

            $form_html .= '
                <input type="hidden" name="action" value="form-' . self::$__prefix . '" />
                <input type="hidden" name="wp_nonce" value="' . wp_create_nonce($this->_id) . '" />
                <input type="hidden" name="key_id" value="' . $this->_id . '" />
                <input type="hidden" name="key_cn" value="' . $this->swapChars(get_class($this)) . '" />';
            
            $form_html .= '</form>';
        }
        else {
           $form_html .= $this->getFormElements();
        }
    
        return $form_html;
    } //function

    public function showForm() {
        $class_name = substr(get_class($this), 4);
        if ($this->_type != 'metabox') {
    ?>
        <form class="<?php echo esc_attr($class_name . ' ' . implode(' ', $this->_classes)) ?>" id="<?php echo esc_attr($this->_id) ?>" name="form-<?php echo esc_attr($this->_id) ?>" method="post">
            <?php
            if ($this->_dbid) {
                echo '<input type="hidden" name="dbid" value="' . esc_attr($this->_dbid) . '" />';
            }

            echo '
                <input type="hidden" name="action" value="form-' . esc_attr(self::$__prefix) . '" />
                <input type="hidden" name="wp_nonce" value="' . esc_attr(wp_create_nonce($this->_id)) . '" />
                <input type="hidden" name="key_id" value="' . esc_attr($this->_id) . '" />
                <input type="hidden" name="key_cn" value="' . esc_attr($this->swapChars(get_class($this))) . '" />';                
            ?>        
            <?php $this->showFormElements() ?>
        </form>
    <?php
        }
        else {
           $this->showFormElements();
        }
    } //function

    public static function swapChars($text) {
        $chars1 = 'praywithoutceasing';
        $chars2 = '12345678907!@3#6$%';
         
        for ($i = 0; $i < strlen($text); $i++) {
            $pos = strpos($chars1, $text[$i]);
            if ($pos !== false) {
                $text[$i] = $chars2[$pos];
            }
        }
        return $text;
    } //function

    public static function unSwapChars($text) {
        $chars1 = '12345678907!@3#6$%';
        $chars2 = 'praywithoutceasing';
        for ($i = 0; $i < strlen($text); $i++) {
            $pos = strpos($chars1, $text[$i]);
            if ($pos !== false) {
                $text[$i] = $chars2[$pos];
            }
        }
        return $text;
    } //function

    public function _getColorPicker($fieldname, $args = array()) {        
        $options = array_merge(array('label' => $fieldname, 'placeholder' => '', 'note1' => ''), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }
        $this->_has_color_picker = true;

        $atts = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $v) {
                $atts []= $key . '="' . $v . '"';
            }
        }

        $html = '<div class="std-form-line field-' . $fieldname . ' field-colorpicker">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input class="wp-color-picker std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="text" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . ((isset($args['id']) ? $args['id'] : $el_id)) . '" ' . implode(' ', $atts) . ' name="' . $name . '" value="' . $value . '" />
                    ' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';
        return $html;
    } //function 

    public function _showColorPicker($fieldname, $args = array()) {        
        $options = array_merge(array('label' => $fieldname, 'placeholder' => '', 'note1' => ''), $args);
        $allowed_tags = self::__getAllowedTagsForFormField();

        echo wp_kses($this->_getColorPicker($fieldname, $options), $allowed_tags);
    } //function 
    
    public static function __validatePhone($phone) {
        // (123)-123-1234
        // (817) 301-3552
        // 123-123-1234
        // 1231231234
        $pattern1 = '/^[0-9]{10}$/';
        if ($phone == '+639996060586' || $phone == '+639996060586') {
            return true;
        }
        if (preg_match($pattern1, $phone, $matches)) {
            return true;
        }
        $pattern2 = '/^[0-9]{3}\-[0-9]{3}\-[0-9]{4}$/';
        if (preg_match($pattern2, $phone, $matches)) {
            return true;
        }
        $pattern3 = '/^\([0-9]{3}\)\-[0-9]{3}\-[0-9]{4}$/';
        if (preg_match($pattern3, $phone, $matches)) {
            return true;
        }
        $pattern4 = '/^\([0-9]{3}\)\s[0-9]{3}\-[0-9]{4}$/';
        if (preg_match($pattern4, $phone, $matches)) {
            return true;
        }           
        return false;
    }
        
    protected function _getStandardDropDownField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => ''), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $atts = array();
        ///////////////////////////////////////
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }   
        //////////////////////////////////////////
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key => $v) {
                $atts []= $key . '="' . $v . '"';
            }
        }
        
        if ($options['options'] && is_array($options['options'])) {
            $opt = '<select title="' . $options['label'] . '" id="' . $el_id . '" name="' . $name . '" class="' . (isset($options['classes']) ? $options['classes'] : '') . '" data-name="' . $fieldname . '" ' . implode(' ', $atts) . '>';
            foreach ($options['options'] as $idx => $o) {
                $is_selected = ($idx == $value) ? 'selected="selected"': '';
                $opt .= '<option value="' . $idx . '" ' . $is_selected . '>' . $o . '</option>';
            }
            $opt .= '</select>';
        }
        else {
            $opt .= '';
        }
        $html = '<div class="std-form-line field-' . $fieldname . (isset($options['width']) && $options['width'] ? ' half' : '') . ' field-dropdown">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        ' . $opt . 
                        (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') .
                        '
                        <span class="error"></span>
                        <span class="error-msg"></span>                                
                    </div>
                </div>';
        return $html;
    } //function

    public function _showStandardDropDownField($fieldname, $args = array()) {        
        $allowed_tags = self::__getAllowedTagsForFormField();        
        echo wp_kses($this->_getStandardDropDownField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getRatingField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname), $args);
        $cond = (isset($this->_conditions[$fieldname])) ? $this->_conditions[$fieldname] : false;
        $hide_class = ($cond && !$this->isShowField($this->_conditions[$fieldname])) ?  true : false;       
        
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }     
        $opt = '';
        if (isset($options['options']) && is_array($options['options'])) {
            foreach ($options['options'] as $idx => $o) {
                $is_selected = ($idx == $value) ? 'checked="checked"': '';
                $add_selected_class = ($idx <= $value) ? ' selected': '';
                $opt .= '
                    <span class="rating-options stars" id="' . $fieldname . '-rating-' . ($idx) . '">
                        <i class="fa fa-star' . $add_selected_class . '" data-val="' . $idx . '" aria-hidden="true">
                            <input type="radio" name="' . $name . '" data-name="' . $fieldname . '" value="' . ($idx) . '" ' . $is_selected . '>
                        </i>
                    </span>';
            } //foreach
        } //if
            // <span class="rating-options stars">
            //   <i class="fa fa-star" data-val="1" aria-hidden="true">
            //     <input class="rating" required type="radio" name="review-request[rating]" value="1"></i>
            //   <i class="fa fa-star" data-val="2" aria-hidden="true">
            //     <input class="rating" required type="radio" name="review-request[rating]" value="2"></i>
            //   <i class="fa fa-star" data-val="3" aria-hidden="true">
            //     <input class="rating" required type="radio" name="review-request[rating]" value="3"></i>
            //   <i class="fa fa-star" data-val="4" aria-hidden="true">
            //     <input class="rating" required type="radio" name="review-request[rating]" value="4"></i>
            //   <i class="fa fa-star" data-val="5" aria-hidden="true">
            //     <input class="rating" required type="radio" name="review-request[rating]" value="5"></i>
            // </span>
        $html = '<div class="std-form-line field-' . $fieldname . '" ' . (($hide_class) ? 'style="display: none"' : '') . '>
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        ' . $opt . 
                        (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') .
                        '<span class="error"></span>
                        <span class="error-msg"></span>                                
                    </div>
                </div>';

        return $html;
    } //function

    protected function _getStandardRadioButtonField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname), $args);
        $cond = (isset($this->_conditions[$fieldname])) ? $this->_conditions[$fieldname] : false;
        $hide_class = ($cond && !$this->isShowField($this->_conditions[$fieldname])) ?  true : false;       
        
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $opt = '';

        foreach ($options['options'] as $idx => $o) {
            $is_selected = ($idx == $value) ? 'checked="checked"': '';
            $opt .= '<span class="radio-field"><input type="radio" name="' . $name . '" data-name="' . $fieldname . '" value="' . $idx . '" ' . $is_selected . '>' . $o . '</span>';
        }
        $html = '<div class="std-form-line field-' . $fieldname . '" ' . (($hide_class) ? 'style="display: none"' : '') . '>
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        ' . $opt . 
                        (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') .
                        '<span class="error"></span>
                        <span class="error-msg"></span>                                
                    </div>
                </div>';

        return $html;
    } //function

    public function _showStandardRadioButtonField($fieldname, $args = array()) {        
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getStandardRadioButtonField($fieldname, $options), $allowed_tags);
    } //function 

    protected function _getGenderRadioButtonField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname), $args);
        $this->_fields[$fieldname] = array('validator' => $options['validator']);
        
        $db_fieldname = $fieldname;
        $value = ($this->_data->$db_fieldname) ? $this->_data->$db_fieldname : (isset($args['default']) ? $args['default'] : '');
        $html = '<div class="std-form-line field-' . $fieldname . '">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input type="radio" ' . (($value == 'f') ? 'checked="checked"' : '') . ' name="' . $this->_id . '[' . $db_fieldname . ']" value="f"> Female
                        <input type="radio" ' . (($value == 'm') ? 'checked="checked"' : '') . ' name="' . $this->_id . '[' . $db_fieldname . ']" value="m"> Male
                        <span class="error"></span>
                        <span class="error-msg"></span>                            
                    </div>
                </div>';

        return $html;
    } //function

    protected function _showGenderRadioButtonField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getGenderRadioButtonField($fieldname, $args), $allowed_tags);
    } //function

    /**
    $args = array(
            'default'       => '',
            'label'         => '',
            'placeholder'   => '',
            'id'            => '',
            'classes'       => '',
            'note1'         => ''
        );
     */

    protected function _getStandardTextField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => '', 'default' => '', 'subname' => '', 'type' => 'text'), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $attributes = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes []= $k . '="' . $v . '"';  
            }
        }

        $html = '<div class="std-form-line field-' . $fieldname . (isset($options['type'])  && $options['type'] == 'hidden' ? ' hide-field' : '') . (isset($options['width']) ? ' half' : '') . ' field-textfield">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="' . $options['type'] . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '" name="' . $name  . '" value="' . stripslashes($value) . '" ' . implode(' ', $attributes) . ' />
                        ' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';

        return $html;
    } //function

    protected function _showStandardTextField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();
        echo wp_kses($this->_getStandardTextField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getHiddenInputField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => '', 'default' => '', 'subname' => '', 'type' => 'text'), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }
        $html = '<input class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="hidden" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '" name="' . $name  . '" value="' . stripslashes($value) . '" />';

        return $html;
    }

    protected function _showHiddenInputField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getHiddenInputField($fieldname, $args), $allowed_tags);
    }

    public function _getStandardTextAreaField($fieldname, $args = array()) {
        $options = array_merge(array(
            'height' => 'auto',
            'note1' => '',
            'insert_element' => false,
            'placeholder' => '',
            ), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($options['default']) ? $options['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $html = '<div class="std-form-line field-' . $fieldname . ' ' . (isset($options['container_classes']) ? $options['container_classes'] : '') .'">
            <label>' . $options['label'] . '</label>
            <div class="grp">
                <textarea ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' class="' . (isset($options['classes']) ? $options['classes'] : '') . '"id="' . $el_id . '" name="' . $name  . '" style="height: ' . $options['height'] . '">' . stripslashes($value) . '</textarea>
                <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                <span class="error"></span>
                <span class="error-msg"></span>'.
                ((isset($options['deletable']) && $options['deletable']) ? '<a class="delete-repeater-item button" href="#">X</a>' : '') .
                '
            </div>' .
                ($options['insert_element'] ? 
                '<div class="add-el-btns">
                    <input type="button" style="margin-left: 5px;" data-el-type="image" class="insert-element add-image button" rel="' . $fieldname . '" value="+ Image" />
                    <input type="button" style="margin-left: 5px;" data-el-type="header" class="insert-element button" rel="' . $fieldname . '" value="+ Header" />
                    <input type="button" style="margin-left: 5px;" data-el-type="content" class="insert-element button" rel="' . $fieldname . '" value="+ Content" />
                    <input type="button" style="margin-left: 5px;" data-el-type="highlight" class="insert-element button" rel="' . $fieldname . '" value="+ Hightlight" />
                    <input type="button" style="margin-left: 5px;" data-el-type="note" class="insert-element button" rel="' . $fieldname . '" value="+ Note" />
                    <input type="button" style="margin-left: 5px;" data-el-type="html" class="insert-element button" rel="' . $fieldname . '" value="+ Code" />
                </div>' : '') . '
        </div> ';

        return $html;
    } //function

    public function _showStandardTextAreaField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getStandardTextAreaField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getSliderField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => '', 'default' => '', 'subname' => '', 'type' => 'text', 'max' => 100, 'min' => 0, 'hide_unit' => false), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $unit_name =  $this->_id . '[' . $db_fieldname . ']';

        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }

            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $unit_name.= '[' . $options['index'] . '][' . $options['subname'] . '-unit]';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $attributes = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes []= $k . '="' . $v . '"';  
            }
        }

        if (!$options['hide_unit']) {
            $unit = '<select class="unit" name="' . $unit_name  . '" id="' . $el_id . '-unit">
                        <option value="px">PX</option>
                        <option value="%">%</option>
                        <option value="em">EM</option>
                     </select>';
        }

        $html = '<div class="std-form-line field-' . $fieldname . (isset($options['type'])  && $options['type'] == 'hidden' ? ' hide-field' : '') . (isset($options['width']) ? ' half' : '') . ' field-slider ' . ($options['hide_unit'] ? 'no-unit' : '') . '">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input title="' . $options['label'] . '" type="number" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" data-min="' . $options['min'] . '" data-max="' . $options['max'] . '" type="' . $options['type'] . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '" name="' . $name  . '" value="' . stripslashes($value) . '" ' . implode(' ', $attributes) . ' /> ' . $unit . '
                        <div class="slider" id="slider-' . $el_id . '"></div>' . 
                        (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';

        return $html;
    }

    protected function _getRowTextFields($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => '', 'default' => '', 'subname' => '', 'type' => 'text'), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $attributes = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes []= $k . '="' . $v . '"';  
            }
        }

        $empty = true;

        if ($value['top'] != '' || $value['right'] != '' || $value['bottom'] != '' || $value['left'] != '') {
            $empty = false;
        }

        $html = '<div class="std-form-line field-' . $fieldname . (isset($options['type'])  && $options['type'] == 'hidden' ? ' hide-field' : '') . (isset($options['width']) ? ' half' : '') . ' field-rowtextfields">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="number" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-top" name="' . $name  . '[top]" value="' . stripslashes($value['top']) . '" ' . implode(' ', $attributes) . ' />
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="number" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-right" name="' . $name  . '[right]" value="' . stripslashes($value['right']) . '" ' . implode(' ', $attributes) . ' />
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="number" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-bottom" name="' . $name  . '[bottom]" value="' . stripslashes($value['bottom']) . '" ' . implode(' ', $attributes) . ' />
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="number" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-left" name="' . $name  . '[left]" value="' . stripslashes($value['left']) . '" ' . implode(' ', $attributes) . ' />
                        ' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span>&nbsp;</span>
                        <span class="clear-fields" ' . ($empty ? 'style="display: none"' : '') . '>Clear</span>
                        <span class="undo-clear">Undo</span>
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';

        return $html;
    } //function    

    /**
     *
    $args = array(
            'default'       => '',
            'label'         => '',
            'placeholder'   => '',
            'id'            => '',
            'classes'       => '',
            'note1'         => ''
        );
    FRONT END:
    wp_nav_menu(array('menu' => $data['t1_top_menu']));    
     */
    protected function _getMenuField($fieldname, $args = array()) {
        $menus = wp_get_nav_menus();
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => ''), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';  
        }

        $dd = '<select  class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="text" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . ((isset($args['id']) ? $args['id'] : $fieldname)) . '" name="' . $name .'">';
        $dd .= '<option value="-1">None</option>';
        
        foreach ($menus as $m) {
            $dd .= '<option value="' . $m->term_id . '"' . (($m->term_id == $value) ? ' selected="selected"' : '') . '>' . $m->name . '</option>';
        }

        $dd .= '</select>';

        $html = '<div class="std-form-line field-' . $fieldname . '">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">' . $dd
                        . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg">Create menu <a href="' . admin_url() . 'nav-menus.php' . '" target="_blank">here</a>.</span>
                    </div>
                </div>';

        return $html;
    } //function

    protected function _showMenuField($fieldname, $args = array()) {
        return $this->_getMenuField($fieldname, $args);
    } //function

    protected function _getStandardPasswordField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $html = '<div class="std-form-line field-' . $fieldname . '">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="password" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . ((isset($args['id']) ? $args['id'] : $fieldname)) . '" name="' . $this->_id . '[' . $db_fieldname . ']" style="height: ' . $options['height'] . '" value="' . $value . '" />
                        ' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';
        return $html;
    } //function

    protected function  _showStandardPasswordField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getStandardPasswordField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getStandardDatePicker($fieldname, $args = array()) {
        $options = array_merge(array('placeholder' => '', 'note1' => ''), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $this->_has_date_picker = true;
        $html = '<div class="std-form-line field-' . $fieldname . ' field-datepicker">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input class="jquery-datepicker" type="text" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' name="' . $this->_id . '[' . $db_fieldname . ']" value="' . $value . '" />
                        <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                        <span class="error"></span>
                        <span class="error-msg"></span>                                 
                    </div>
                </div>';
        
        return $html;
    } //function 

    protected function _showStandardDatePicker($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_showStandardDatePicker($fieldname, $args), $allowed_tags);
    } //function

    protected function _getMultipleCheckboxField($fieldname, $args = array()) {
        $options = array_merge(array('note1' => ''), $args);
        
        $db_fieldname = $fieldname;
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        $value = $this->_getValue($db_fieldname, (isset($options['default']) ? $options['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        $opt = '';

        if ($options['options'] && is_array($options['options'])) {
            foreach ($options['options'] as $idx => $o) {
                $is_checked = is_array($value) && in_array($idx, $value) ? 'checked="checked"': '';
                $opt .= '<span class="cb-field"><input type="checkbox" name="' . $name . '[]" data-name="' . $fieldname . '" value="' . $idx . '" ' . $is_checked . '>' . $o . '</span>';
            }
        }
        $html = '<div class="std-form-line field-' . $fieldname . '">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        ' . $opt . '
                        <div class="note1">' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                        <span class="error"></span>
                        <span class="error-msg"></span>                            
                    </div>
                </div>';
        
        return $html;
    }

    protected function _getStandardCheckboxField($fieldname, $args = array()) {
        $options = array_merge(array('note1' => ''), $args);
        
        $db_fieldname = $fieldname;
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        $value = $this->_getValue($db_fieldname, (isset($options['default']) ? $options['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];  
        }

        $html = '<div class="std-form-line field-' . $fieldname . '">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <label class="switch">
                            <input type="checkbox" id="' . $el_id . '" name="' . $name . '" ' . (($value == 'on') ? 'checked="checked"' : '') . ' class="' . (isset($options['classes']) ? $options['classes'] : '') . '" />
                            <span class="slider round"></span>
                        </label>                        
                        <div class="note1">' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                        <span class="error"></span>
                        <span class="error-msg"></span>                            
                    </div>
                </div>';
        
        return $html;
    }

    protected function _showStandardCheckboxField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getStandardCheckboxField($fieldname, $args), $allowed_tags);
    } //function

    public function _getWPEditorField($fieldname, $args = array(), $editor_opt = array()) {
        $options = array_merge(array(
            'note1' => '',
        ), $args);

        $editor_opt = array_merge(array(
            'tinymce' => array(
                'height' => '200px'
            ),
            'editor_class' => 'multiple',
        ), $editor_opt);
        
        $db_fieldname = $fieldname;
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        $value = $this->_getValue($db_fieldname, (isset($options['default']) ? $options['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }

            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];  
            $fieldname = $fieldname . '_' . $options['index'] . '_' . $options['subname'];
            $value = $this->_getValue($fieldname, (isset($options['default']) ? $options['default'] : ''));
        } //if

        $this->_wp_editors []= $fieldname;

        $html .= '<div class="std-form-line field-' . $fieldname . ' ' . $field_id . ' ' . $options['container_classes'] . '">
           <label>' . $args['label'] . '</label>
           <div class="grp">';
        ob_start();
        wp_editor(stripslashes(stripslashes($value)), $fieldname, $editor_opt);
        $editor_contents = ob_get_contents();
        ob_end_clean();
        $html .= $editor_contents;
        if (isset($options['line']) && $options['line'] == 'single') {
            $html .= '<input type="hidden" name="wp-editors-single[]" value="' . $fieldname . '">';
        }
        else {
            $html .= '<input type="hidden" name="wp-editors-multiple[]" value="' . $fieldname . '">'; 
        }
        $html .= '
        <div class="' . $options['container_classes'] . '">' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
           </div>
        </div>';
        
        return $html;
    } //function

    public function _showWPEditorField($fieldname, $args = array(), $editor_opt = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getWPEditorField($fieldname, $args, $editor_opt), $allowed_tags);
    } //function

    protected function _getImageUploaderField($fieldname, $args = array()) {
        $options = array_merge(array(
            'height' => 'auto',
            'note1' => '',
            'echo' => true,
            'validator' => '',
            'insert_element' => false,
            'button' => 'Upload',
            ), $args);
        $this->_fields[$fieldname] = array('validator' => $options['validator']);
        $db_fieldname = $fieldname;
        //$value = ($this->_data->$db_fieldname) ? $this->_data->$db_fieldname : (($args['default']) ? $args['default'] : '');        
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        } //if

        $field_id = $fieldname . (isset($options['repeater']) && $options['repeater'] ? '-' . $options['subname'] . '-' . $options['index'] : '');
        $html = '<div class="std-form-line field-' . $fieldname . ' ' . $options['container_classes'] . '">
            <label>' . $options['label'] . '</label>
            <div class="grp">
                <div class="uploader">
                    <input id="' . $el_id . '" name="' . $name . '[url]" type="text" value="' . (isset($value['url']) ? $value['url'] : '') . '" />
                    <input class="button media-uploader" rel="' .  $el_id . '" type="button" value="' . $value['button'] . '" />
                    <input type="text" placeholder="Caption" name="' . $name . '[caption]" value="' . (isset($value['caption']) ? $value['caption'] : '') . '" />
                    <input type="text" placeholder="Alt" name="' . $name . '[alt]" value="' . (isset($value['alt']) ? $value['alt'] : '') . '" />
                </div>
                <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                <span class="error"></span>
                <span class="error-msg"></span>'.
                (($options['deletable']) ? '<a class="delete-repeater-item button" href="#">X</a>' : '').
                '</div>' .
                ($options['insert_element'] ? 
                '<div class="add-el-btns">
                    <input type="button" style="margin-left: 5px;" data-el-type="image" class="insert-element add-image button" rel="' . $fieldname . '" value="+ Image" />
                    <input type="button" style="margin-left: 5px;" data-el-type="header" class="insert-element button" rel="' . $fieldname . '" value="+ Header" />
                    <input type="button" style="margin-left: 5px;" data-el-type="content" class="insert-element button" rel="' . $fieldname . '" value="+ Content" />
                    <input type="button" style="margin-left: 5px;" data-el-type="highlight" class="insert-element button" rel="' . $fieldname . '" value="+ Hightlight" />
                    <input type="button" style="margin-left: 5px;" data-el-type="note" class="insert-element button" rel="' . $fieldname . '" value="+ Note" />
                    <input type="button" style="margin-left: 5px;" data-el-type="html" class="insert-element button" rel="' . $fieldname . '" value="+ Code" />
                </div>' : '') . '
            </div>';
        return $html;
    }

    protected function _showImageUploaderField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getImageUploaderField($fieldname, $args), $allowed_tags);
    }

    protected function _getMediaUploaderField($fieldname, $args = array()) {
        $options = array_merge(array(
            'height' => 'auto',
            'note1' => '',
            'echo' => true,
            'validator' => '',
            'button' => 'Upload',
            ), $args);
        $this->_fields[$fieldname] = array('validator' => $options['validator']);
        $db_fieldname = $fieldname;
        //$value = ($this->_data->$db_fieldname) ? $this->_data->$db_fieldname : (($args['default']) ? $args['default'] : '');        
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];  
        } //if
        $field_id = $fieldname . (isset($options['repeater']) && $options['repeater'] ? '-' . $options['subname'] . '-' . $options['index'] : '');
        $html = '<div class="std-form-line field-media-uploader field-' . $fieldname . ' ' . $field_id . ' ' . $options['container_classes'] . '">
            <label>' . $options['label'] . '</label>
            <div class="grp">
                <div class="uploader">
                    <input id="' . $el_id . '" class="url" name="' . $name . '" type="text" value="' . $value . '" />
                    <input class="button media-uploader" rel="' .  $el_id . '" type="button" value="' . $options['button'] . '" />
                </div>
                <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                <span class="error"></span>
                <span class="error-msg"></span>'.
                (($options['deletable']) ? '<a class="delete-repeater-item button" href="#">X</a>' : '').
                '</div>
            </div>';
        return $html;
    } //function

    protected function _showMediaUploaderField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getMediaUploaderField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getFileUploaderField($fieldname, $args = array()) {
        $options = array_merge(array(
            'height' => 'auto',
            'note1' => '',
            'echo' => true,
            'validator' => '',
            'button' => 'Upload',
            ), $args);
        $this->_with_file = true;
        $this->_fields[$fieldname] = array('validator' => $options['validator']);
        $db_fieldname = $fieldname;
        //$value = ($this->_data->$db_fieldname) ? $this->_data->$db_fieldname : (($args['default']) ? $args['default'] : '');        
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $name_default =  $this->_id . '[' . $db_fieldname . '-default]';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $name.= '[' . $options['index'] . '][' . $options['subname'] . '-default]';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        } //if

        $field_id = $fieldname . (isset($options['repeater']) && $options['repeater'] ? '-' . $options['subname'] . '-' . $options['index'] : '');
        $attributes = array();
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key1 => $value1) {
                $attributes []= $key1 . '="' . $value1 . '"';        
            }
        }
        $upload_folder_info = wp_upload_dir();
        $strip_value = stripslashes($value);
        $html = '<div class="std-form-line field-upload field-' . $fieldname . ' ' . $field_id . ' ' . $options['container_classes'] . '">
            <label>' . $options['label'] . '</label>
            <div class="grp">
                <input type="file" title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="' . $options['type'] . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '" value="' . $strip_value . '" name="' . $name  . '" ' . implode(' ', $attributes) . ' />
                <button class="upload-btn">Upload Photo</button>
                <input type="hidden" value="' . $strip_value . '" name="' . $name_default  . '" class="' . $fieldname . '-default" />
                <button class="clear" rel="' . $el_id . '"><i class="fas fa-times"></i> Clear</button>
                <span class="file-upload-preview" rel="' . $el_id . '" style="display: block">
                    <a href="' . $strip_value . '" target="_blank">
                        <img src="' . $strip_value . '?t=' . time() . '" class="file-img-preview" alt="Preview of ' . $options['label'] . '" style="display: ' . ($strip_value ? 'block' : 'none') . ';"/>
                    </a>                    
                </span>
                <i rel="' . $el_id . '" ' . (!$strip_value ? 'style="display: none"' : '') . 'class="fas fa-sync rotate' . ($args['pre_rotate'] ? ' pre-rotate' : '') . '" data-url="' . $strip_value . '"></i>
                <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                <span class="error"></span>
                <span class="error-msg"></span>'.
                (($options['deletable']) ? '<a class="delete-repeater-item button" href="#">X</a>' : '').
                '</div>
            </div>';
        return $html;
    } //function

    protected function _getFilesUploaderField($fieldname, $args = array()) {
        $options = array_merge(array(
            'height' => 'auto',
            'note1' => '',
            'echo' => true,
            'validator' => '',
            'button' => 'Upload',
            'multiple' => false,
            ), $args);
        $this->_with_file = true;
        $this->_fields[$fieldname] = array('validator' => $options['validator']);
        $db_fieldname = $fieldname;
        //$value = ($this->_data->$db_fieldname) ? $this->_data->$db_fieldname : (($args['default']) ? $args['default'] : '');        
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $name_default =  $this->_id . '[' . $db_fieldname . '-default]';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));
        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }

            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $name.= '[' . $options['index'] . '][' . $options['subname'] . '-default]';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        } //if
        $field_id = $fieldname . (isset($options['repeater']) && $options['repeater'] ? '-' . $options['subname'] . '-' . $options['index'] : '');
        $attributes = array();
        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key1 => $value1) {
                $attributes []= $key1 . '="' . $value1 . '"';        
            }
        }
        $upload_folder_info = wp_upload_dir();
        $strip_value = stripslashes($value);
        $html = '<div class="std-form-line field-upload field-' . $fieldname . ' ' . $field_id . ' ' . $options['container_classes'] . '">
            <label>' . $options['label'] . '</label>
            <div class="grp">
                <input type="file" ' . ($options['multiple'] ? 'multiple="multiple"' : '') . ' title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="' . $options['type'] . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '" value="' . $strip_value . '" name="' . $name  . '" ' . implode(' ', $attributes) . ' />
                <button class="upload-btn">Upload Photo</button>
                <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                <span class="error"></span>
                <span class="error-msg"></span>'.
                (($options['deletable']) ? '<a class="delete-repeater-item button" href="#">X</a>' : '').
                '
                    <div class="preview-files" rel="' . $el_id . '" style="display: block" style="border: solid #ccc 1px; border-radius: 7px; padding: 7px;"></div>
                </div>
            </div>';
        return $html;
    } //function

    protected function _showFileUploaderField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getMediaUploaderField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getTopLeftBottomRightField($fieldname, $args = array()) {
        $options = array_merge(array(
            'height' => 'auto',
            'note1' => '',
            'echo' => true,
            'validator' => '',
            'button' => 'Upload',
            'multiple' => false,
            'placeholder' => '',
            'deletable' => '',
            ), $args);

        $this->_with_file = true;
        $this->_fields[$fieldname] = array('validator' => $options['validator']);

        $db_fieldname = $fieldname;
        //$value = ($this->_data->$db_fieldname) ? $this->_data->$db_fieldname : (($args['default']) ? $args['default'] : '');        
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $name_default =  $this->_id . '[' . $db_fieldname . ']';

        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }

            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        } //if

        $field_id = $fieldname . (isset($options['repeater']) && $options['repeater'] ? '-' . $options['subname'] . '-' . $options['index'] : '');
        $attributes = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $key1 => $value1) {
                $attributes []= $key1 . '="' . $value1 . '"';        
            }
        }

        $upload_folder_info = wp_upload_dir();
        $strip_value = stripslashes($value);

        $labels = array('Top', 'Right', 'Bottom', 'Left');

        if (isset($options['subname']) && $options['subname'] == 'border-radius') {
            $labels = array('Top Left', 'Top Right', 'Bottom Left', 'Bottom Right');
        }

        $html = '<div class="std-form-line field-toprightbottomleft field-' . $fieldname . ' ' . $field_id . '">
            <label>' . $options['label'] . '<a href="#">Clear</a></label>
            <div class="grp">
                <span>
                    <input title="top" type="number" title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-1" value="' . $strip_value . '" name="' . $name  . '[1]" ' . implode(' ', $attributes) . ' />
                    <em>' . $labels[0] . '</em>
                </span>
                <span>
                    <input title="right" type="number" title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-2" value="' . $strip_value . '" name="' . $name  . '[2]" ' . implode(' ', $attributes) . ' />
                    <em>' . $labels[1] . '</em>
                </span>
                <span>    
                    <input title="bottom" type="number" title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-3" value="' . $strip_value . '" name="' . $name  . '[3]" ' . implode(' ', $attributes) . ' />
                    <em>' . $labels[2] . '</em>
                </span>
                <span>
                    <input title="left" type="number" title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" ' . (($options['placeholder']) ? 'placeholder="' . $options['placeholder'] . '"' : '') . ' id="' . $el_id . '-4" value="' . $strip_value . '" name="' . $name  . '[4]" ' . implode(' ', $attributes) . ' />
                    <em>' . $labels[3] . '</em>
                </span>

                <div>' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '</div>
                <span class="error"></span>
                <span class="error-msg"></span>'.
                (($options['deletable']) ? '<a class="delete-repeater-item button" href="#">X</a>' : '').
                '
                    <div class="preview-files" rel="' . $el_id . '" style="display: block" style="border: solid #ccc 1px; border-radius: 7px; padding: 7px;"></div>
                </div>
            </div>';
        return $html;
    } //function

    protected function _showTopLeftBottomRightField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getTopLeftBottomRightField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getButtonField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => '', 'default' => '', 'subname' => '', 'type' => 'text'), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $attributes = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes []= $k . '="' . $v . '"';  
            }
        }

        $html = '<div class="std-form-line field-' . $fieldname . (isset($options['type'])  && $options['type'] == 'hidden' ? ' hide-field' : '') . (isset($options['width']) ? ' half' : '') . ' field-button">
                    <label>' . $options['label'] . '</label>
                    <div class="grp ' . (isset($options['classes']) ? $options['classes'] : '') . '">
                        <input title="' . $options['label'] . '" class="std-input text" type="' . $options['type'] . '" placeholder="Label" id="' . $el_id . '-label" name="' . $name  . '[label]" value="' . ($value['label']) . '" ' . implode(' ', $attributes) . ' />
                        
                        <input title="' . $options['label'] . '" class="std-input url" type="' . $options['type'] . '" placeholder="URL" id="' . $el_id . '-link" name="' . $name  . '[link]" value="' . ($value['link']) . '" ' . implode(' ', $attributes) . ' />
                        
                        <select name="' . $name  . '[target]" id="' . $el_id . '-target" class="type">
                            <option value="_self" ' . ($value['target'] == '_self' ? 'selected="selected"' : '') . '>_self</option>
                            <option value="_blank" ' . ($value['target'] == '_blank' ? 'selected="selected"' : '') . '>_blank</option>
                            <option value="_parent" ' . ($value['target'] == '_parent' ? 'selected="selected"' : '') . '>_parent</option>
                            <option value="_top" ' . ($value['target'] == '_top' ? 'selected="selected"' : '') . '>_top</option>
                        </select>
                        ' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';

        return $html;
    } //function

    protected function _showButtonField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getButtonField($fieldname, $args), $allowed_tags);
    } //function

    protected function _getPairInputField($fieldname, $args = array()) {
        $options = array_merge(array('label' => $fieldname, 'note1' => '', 'placeholder' => '', 'default' => '', 'subname' => '', 'type' => 'text'), $args);
        $db_fieldname = $fieldname;
        $value = $this->_getValue($db_fieldname, (isset($args['default']) ? $args['default'] : ''));
        
        $name =  $this->_id . '[' . $db_fieldname . ']';
        $el_id = ((isset($args['id']) ? $args['id'] : $fieldname));

        if (isset($options['repeater']) && $options['repeater']) {
            if (isset($value) && is_array($value) && 
                isset($value[$options['index']]) && 
                is_array($value[$options['index']]) && 
                isset($value[$options['index']][$options['subname']])) {
                $value = $value[$options['index']][$options['subname']];
            }
            else if (isset($args['default']) && $args['default']){
                $value = $args['default'];
            }
            else {
                $value = '';
            }
            $name.= '[' . $options['index'] . '][' . $options['subname'] . ']';
            $el_id = ((isset($args['id']) ? $args['id'] : $fieldname)) . '-' . $options['index'] . '-' . $options['subname'];
        }

        $attributes = array();

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes []= $k . '="' . $v . '"';  
            }
        }

        $placeholder1 = $options['placeholder1'] ? $options['placeholder1'] : '';
        $placeholder2 = $options['placeholder2'] ? $options['placeholder2'] : '';

        if (!is_array($value)) {
            $value = array('label' => '', 'value' => '');
        }

        $html = '<div class="std-form-line field-' . $fieldname . (isset($options['type'])  && $options['type'] == 'hidden' ? ' hide-field' : '') . (isset($options['width']) ? ' half' : '') . ' field-pairtext">
                    <label>' . $options['label'] . '</label>
                    <div class="grp">
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="' . $options['type'] . '" placeholder="' . $placeholder1 . '" id="' . $el_id . '-label" name="' . $name  . '[label]" value="' . stripslashes($value['label']) . '" ' . implode(' ', $attributes) . ' />
                        <input title="' . $options['label'] . '" class="std-input ' . (isset($options['classes']) ? $options['classes'] : '') . '" type="' . $options['type'] . '" placeholder="' . $placeholder2 . '" id="' . $el_id . '-link" name="' . $name  . '[value]" value="' . stripslashes($value['value']) . '" ' . implode(' ', $attributes) . ' />
                        ' . (($options['note1']) ? '<span class="important-note">' . $options['note1'] . '</span>' : '') . '
                        <span class="error"></span>
                        <span class="error-msg"></span>
                    </div>
                </div>';

        return $html;
    } //function

    protected function _showPairInputField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getPairInputField($fieldname, $args), $allowed_tags);
    } //function   

    protected function _showPostsField($fieldname, $args = array()) {
        $allowed_tags = self::__getAllowedTagsForFormField();      
        echo wp_kses($this->_getPostsField($fieldname, $args), $allowed_tags);
    } //function        

    public function addScript() {
        $ajax_url = admin_url('admin-ajax.php');
    }    

    public static function __getAllowedTags() {
        $allowed_tags = array('form' => array('id' => array(), 'class' => array(), 'rel' => array(), 'name' => array(), 'method' => array()),
                              'div' => array('class' => array(), 'id' => array(), 'rel' => array()),
                              'span' => array('class' => array(), 'id' => array(), 'rel' => array()),
                              'label' => array('class' => array(), 'id' => array(), 'rel' => array()),
                              'input' => array('class' => array(), 'id' => array(), 'rel' => array(), 'type' => array(), 'placeholder' => array(), 'name' => array(), 'value' => array()),
                              'textarea' => array('class' => array(), 'id' => array(), 'rel' => array(), 'placeholder' => array(), 'name' => array()),
                              'select' => array('class' => array(), 'id' => array(), 'rel' => array(), 'placeholder' => array(), 'name' => array()),
                              'option' => array('class' => array(), 'id' => array(), 'rel' => array(), 'placeholder' => array(), 'name' => array(), 'value' => array()),
                              'button' => array('class' => array(), 'id' => array(), 'rel' => array(), 'type' => array()),
                              'i' => array('class' => array(), 'id' => array(), 'rel' => array()),
                              'a' => array('class' => array(), 'id' => array(), 'rel' => array(), 'href' => array(), 'target' => array()),
                              );

        return $allowed_tags;
    }

    abstract protected function showFormElements(); 
    abstract protected function getFormElements();
} //end of class
// added container class to textarea and media uploader
/**********************************************************************************
Version 1.0.7 
- add note1 to radio button
- added width to some of the input text and select fields
***********************************************************************************/
// loading form via ajax will not work. load the form normally and just replaced it by the form generated by ajax call
