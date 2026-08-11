<?php

class Hero_Metabox extends DHB\Flowy\MetaBox {
    protected function _showForm($post) {
        $post_id = $post->ID;

        $form = new Hero_Form('dphb', array('type' => 'metabox'));

        $bg_image = get_post_meta($post_id, '_dphb_hero_bg_image', true);          
        $subtext = get_post_meta($post_id, '_dphb_hero_subtext', true);    

        $form->setValues(array('bg_image' => $bg_image, 'subtext' => $subtext));
                
        echo $form->getForm();       
    }

    public function _saveMetadata($post_id) {
        $bg_image = sanitize_text_field($_POST['dphb']['bg_image']);
        $subtext = sanitize_text_field($_POST['dphb']['subtext']);

        update_post_meta($post_id, '_dphb_hero_bg_image', $bg_image);          
        update_post_meta($post_id, '_dphb_hero_subtext', $subtext);          
    }
} // class