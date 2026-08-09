import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, ColorPalette,  __experimentalColorGradientControl as ColorGradientControl, MediaUpload, MediaUploadCheck, InnerBlocks, RichText, BlockControls, 
    AlignmentControl } from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, RadioControl, TextControl, Button} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
    const { content, alignment } = attributes;
    const blockProps = useBlockProps({
        // style: { textAlign: alignment } // Dynamically updates the preview in the editor
    });

    const onChangeAlignment = ( newAlignment ) => {
        // Fallback to 'left' if the user deselects the current alignment option
        setAttributes( { alignment: newAlignment === undefined ? 'left' : newAlignment } );
    };

	return (
        <>
            <BlockControls>
                <AlignmentControl
                    value={ alignment }
                    onChange={ onChangeAlignment }
                />
            </BlockControls>		
			<div { ...blockProps } > 			
				{/* 1. Added dynamic background style here using your gradient attribute */}                   
					{ __( 'This is the post Excerpt, page Subtext, or term description. This is typically 30-55 words.', 'my-basic-block' ) }
			</div>
		</>
	);
}