<?php
/**
 * @license proprietary?
 *
 * Modified by edesacee on 11-August-2026 using Strauss.
 * @see https://github.com/BrianHenryIE/strauss
 */
namespace DHB\Flowy;

if (!defined('ABSPATH')) exit;

abstract class Ajax {
    protected $_selector;
    protected $_prefix;
    protected $_trigger;
    protected $_field_type;
    protected $_get_params_func;
    protected $_async;
    protected $_show_processing_modal = false;
    protected $_id;
    protected $_type;

    protected $_plugin_url;

    abstract public function action($data);

    public function __construct($id, $options = array()) {
        $this->_id    = $id;

        $type         = (isset($options['type'])) ? $options['type'] :'';
        // $this->_slug = isset($options['slug']) ? $options['slug'] : '';

        $library_folder = basename(dirname(__FILE__));
        $this->_prefix = strtolower($library_folder) . '-' . 'ajax-actions';

        // $this->_classes []= $this->_prefix;
        // $this->_classes []= (isset($options['class'])) ? $options['class'] : 'frm-' . uniqid();

        $class = get_class();

        $this->_type = $type;
        $this->_show_processing_modal = isset($options['show_processing_modal']) ? $options['show_processing_modal'] : false;
        $this->_selector = isset($options['selector']) ? $options['selector'] : '';
        $this->_trigger = isset($options['trigger']) ? $options['trigger'] : 'click';
        $this->_field_type = isset($options['field_type']) ? $options['field_type'] : 'input';
        $this->_get_params_func = isset($options['get_params_func']) ? $options['get_params_func'] : '';
        $this->_async = isset($options['async']) ? $options['async'] : '';

        $this->_plugin_url = $options['plugin_url'];

        if ($type == 'wp') {
            add_action('wp_footer', array($this, 'addModal'), 100);
            add_action('wp_enqueue_scripts', array($this, 'loadScripts'));
        }
        else if($type == 'admin' || $type == 'metabox'){
            add_action('admin_footer', array($this, 'addModal'));
            add_action('admin_enqueue_scripts', array($this, 'loadAdminScripts'));
        }
        else if($type == 'static'){
            $this->addModal();
        }
    } //function

    public function addModal() {
    ?>
        <!-- The Modal -->
        <div id="<?php echo esc_attr($this->_id) ?>-modal" class="ajax-action-processing modal" style="display: none">
            <!-- Modal content -->
            <div class="modal-content"  style="display: table; table-layout: fixed; background: transparent; border: 0;">     
                <!-- <span class="close">&times;</span> -->
                <div class="content" style="display: table-cell; background: transparent; vertical-align: middle;">
                    <div class="inner" style="text-align: center; max-width: 100%; width: 400px; margin: 0 auto; border-radius: 5px; background: #2A51A0; padding: 15px 20px; color: #fff">
                        <div class="fa-3x">
                            <i class="fas fa-spinner fa-spin"></i>
                        </div>
                        <div>Processing...</div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    } //function

    public function loadScripts() {
        wp_enqueue_script('flowy-ajax-scripts', $this->_plugin_url . 'scripts/ajax-scripts.js', null, time(), array('in_footer' => true));
        wp_add_inline_script('flowy-ajax-scripts', $this->getInlineScripts(), "after");
    }

    public function loadAdminScripts() {
        wp_enqueue_script('flowy-ajax-scripts', $this->_plugin_url . 'scripts/ajax-scripts.js', null, time(), array('in_footer' => true));
        wp_add_inline_script('flowy-ajax-scripts', $this->getInlineScripts(), "after");
    }


    protected function getInlineScripts() {
        $ajax_url = admin_url('admin-ajax.php');
        $inline_scripts = "";

        $inline_scripts .= "\t" . '$l = document.querySelectorAll("' .  esc_js($this->_selector) . '").length;';
        $inline_scripts .= '

        if ($l > 0) {
            if (typeof $jx === "undefined") {
                var $jx = jQuery.noConflict();
            }

            var ajaxurl = "' . esc_url($ajax_url) . '";
            ';

        $inline_scripts .= '
            $jx(function(){
                $jx("body").on("' . esc_js($this->_trigger) . '", "' . esc_js($this->_selector) . '", function(){
                    $params = $jx(this).data("params") ? $jx(this).data("params") : ""; 
                    $confirm = $jx(this).data("confirm");                

                    if ($jx("' . esc_js($this->_selector) . '").is(":disabled")) {
                        return 0;
                    }

                    $show_modal = "' . esc_js($this->_show_processing_modal) . '";

                    if ($show_modal) {
                        $jx("#' . esc_js($this->_id) . '-modal .inner").html("<div class=\"fa-3x\"><i class=\"fas fa-spinner fa-spin\"></i></div><div>Processing...</div>");
                    }

                    if ($confirm) {
                        if (!confirm($confirm)) {
                            return false;
                        }

                        $jx(this).attr("disabled", "disabled");
                        $jx(this).css("cursor", "progress");     

                        if ($show_modal) {
                            $jx("#' . esc_js($this->_id) . '-modal").fadeIn();
                        }
                    }
                    else {
                        $jx(this).attr("disabled", "disabled");
                        $jx(this).css("cursor", "progress");                    

                        if ($show_modal) {
                            $jx("#' . esc_js($this->_id) . '-modal").fadeIn();
                        }
                    }

                    if (typeof window["' . esc_js($this->_get_params_func) . '"] !== "undefined" && typeof window["' . esc_js($this->_get_params_func) . '"] === "function") {
                        $params = window["' . esc_js($this->_get_params_func) . '"]($jx(this));
                    }

                    if ("' . esc_js($this->_trigger) . '" == "change") {
                        if ($params) {
                            $params += "&";
                        } //if

                        if ("' . esc_js($this->_field_type) . '" == "checkbox") {
                            if ($jx(this).is(":checked")) {
                                $params += \'val=on\';
                            } //if
                        } //if
                        else {
                            $params += \'val=\' + $jx(this).val();   
                        } //else
                    } //else

                    var position = $jx(this).position();

                    $params += "&wp_nonce=' . esc_js(wp_create_nonce($this->_id)) . '";

                    $jx.ajax({
                        url: ajaxurl,
                        type: "POST",
                        data: "action=' . esc_js($this->_prefix) . '&" + $params + "&key_cn=" + encodeURIComponent("' . esc_js($this->swapChars(get_class($this))) . '") + "&key_id=' . esc_js($this->_id) . '",
                        dataType: "json",
                        success: function($data){
                            $jx("' . esc_js($this->_selector) . '").removeAttr("disabled");
                            $jx("' . esc_js($this->_selector) . '").css("cursor", "pointer");

                            $show_modal = "' . esc_js($this->_show_processing_modal) . '";';

    $inline_scripts .= '

                            if ($show_modal && $data["message"]) {
                                $jx("#' . esc_js($this->_id) . '-modal .inner").html($data["message"] + "<div ><span>OK</span></div>");
                            }
                            else {
                                $jx("#' . esc_js($this->_id) . '-modal").fadeOut();
                            }

                            if ($data["redirect_to"]) {
                                window.location = $data["redirect_to"];
                            }
                            else if (typeof window[$data.function] !== "undefined" && typeof window[$data.function] === "function") {
                                window[$data.function]($data);
                            } //if
                            ';

    $inline_scripts .= '
                        },
                        error: function($data, $textStatus, $errorThrown){
                        }
                    });

                    if ("' . esc_js($this->_trigger) . '" == "change" || "' . esc_js($this->_async) . '" == true) {
                        return true;
                    }
                    else {
                        return false;
                    }
                });
            });';

     $inline_scripts .= '        
        }';

        return $inline_scripts;
    }

    public static function __init() {
        $class = get_class();
        $library_folder = basename(dirname(__FILE__));
        $prefix = strtolower($library_folder) . '-' . 'ajax-actions';

        add_action('wp_ajax_' . $prefix, array($class, '__ajaxCallAjaxAction'));
        add_action('wp_ajax_nopriv_' . $prefix, array($class, '__ajaxCallNoPrivAjaxAction'));
    } //function

    public static function __ajaxCallNoPrivAjaxAction() {
        $key_id = isset($_POST['key_id']) ? sanitize_text_field(wp_unslash($_POST['key_id'])) : '';
        $nonce = isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '';

        if (!wp_verify_nonce($nonce, $key_id)) {
            echo esc_html__('You care not allowed to access this page.', 'flowy-visual-styler');
            exit;
        }

        $class_name = get_class();
        $key_cn = isset($_POST['key_cn']) ? sanitize_text_field(wp_unslash($_POST['key_cn'])) : '';
        $class = $class_name::unswapChars($key_cn);
        $obj = new $class($key_id);

        if (method_exists($obj, 'noPrivAction')) {
            $user_input = isset($_POST) ? map_deep(wp_unslash($_POST), 'sanitize_textarea_field') : '';
            $data = $obj->noPrivAction($user_input);
            $data ['function'] = str_replace('-', '', $key_id) . 'Success';
        }
        else {
            $data ['success'] = false;
            $data ['message'] = 'Not allowed.';
        }

        wp_send_json($data);
        exit;        
    }

    public static function __ajaxCallAjaxAction() {
        if(!is_user_logged_in()) {
            exit ;
        }

        $key_id = isset($_POST['key_id']) ? sanitize_text_field(wp_unslash($_POST['key_id'])) : '';
        $nonce = isset($_POST['wp_nonce']) ? sanitize_text_field(wp_unslash($_POST['wp_nonce'])) : '';

        if (!wp_verify_nonce($nonce, $key_id)) {
            echo esc_html__('You care not allowed to access this page.', 'flowy-visual-styler');
            exit;
        }

        $class_name = get_class();
        $key_cn = isset($_POST['key_cn']) ? sanitize_text_field(wp_unslash($_POST['key_cn'])) : '';
        $class = $class_name::unswapChars($key_cn);
        $obj = new $class($key_id);

        $user_input = isset($_POST) ? map_deep(wp_unslash($_POST), 'sanitize_textarea_field') : '';
        $data = $obj->action($user_input);

        $data ['function'] = str_replace('-', '', $key_id) . 'Success';

        wp_send_json($data);
        exit;
    } //function    

    public static function swapChars($text) {
        $chars1 = 'praywithoutceasing';
        $chars2 = '12345678907!@3#6$^';

        for ($i = 0; $i < strlen($text); $i++) {
            $pos = strpos($chars1, $text[$i]);

            if ($pos !== false) {
                $text[$i] = $chars2[$pos];
            }
        }

        return $text;
    } //function

    public static function unSwapChars($text) {
        $chars1 = '12345678907!@3#6$^';
        $chars2 = 'praywithoutceasing';

        for ($i = 0; $i < strlen($text); $i++) {
            $pos = strpos($chars1, $text[$i]);

            if ($pos !== false) {
                $text[$i] = $chars2[$pos];
            }
        }

        return $text;
    } //function
}

/**
 ************************************************************************

call __init();

new  Ajax_EnableDisableClient('ajax-toggle-client', array('type' => 'wp', 
                                                          'selector' => '.ajax-toggle-client', 
                                                          'trigger' => 'change', 
                                                          'field_type' => 'checkbox',
                                                          'get_params_func' => functionName
                                                    ));

function ajaxgettimezoneoptGetParams() {
    $facebook_id = $jx('#facebook_id').val();
    $facebook_name = $jx('#facebook_name').val();
    $access_token = $jx('#facebook_access_token').val();
    $facebook_guid = $jx('#facebook_guid').val();
    $loc_guid = $jx('#l').val();
    return 'facebook_id=' + $facebook_id + '&facebook_access_token=' + $access_token + '&l=' + $loc_guid + '&facebook_name=' + $facebook_name + '&facebook_guid=' + $facebook_guid;
}

function ajaxgettimezoneoptGetParams() {
    return 'a=test';
}

<input 
        type="checkbox"
        class="ajax-toggle-client" 
        data-params="c=' . $value['guid'] . '"
        data-confirm="Are you sure ...?"

/>

 ************************************************************************
 */
