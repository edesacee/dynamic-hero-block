<?php

namespace dphb;

class Hero_Form extends Form {
    protected function showFormElements() {
        $allowed_tags = self::__getAllowedTagsForFormField();
        echo wp_kses($this->getFormElements(), $allowed_tags);
    }

    protected function getFormElements() {
    	$form = '';
    	
        $form .= $this->_getMediaUploaderField('bg_image', array('label' => esc_html__('Background Image', 'dynhero')));
        $form .= $this->_getStandardTextAreaField('subtext', array('label' => esc_html__('Sub Text', 'dynhero'), 'classes' => 'full-width'));

        $form .= '';



        return $form;
    } //function

    protected function __getAllPosts($type = 'post') {
        $posts = get_posts([
            'post_type'      => $type,      // Retrieves standard posts
            'post_status'    => 'publish',   // Only published content
            'numberposts'    => -1,          // Get all posts (no limit)
            'fields'         => 'all',       // Returns an array of IDs only
        ]);

        $arr_posts = array();

        if (is_array($posts)) {
            foreach ( $posts as $post) {
                $arr_posts [$post->ID] = $post->post_title;
            }    
        }

        return $arr_posts;
    }
}

