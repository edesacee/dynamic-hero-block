import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, ColorPalette,  __experimentalColorGradientControl as ColorGradientControl, MediaUpload, MediaUploadCheck, InnerBlocks, RichText} from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, RadioControl, TextControl, Button} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { widthNum, widthUnit, gradient, mediaId, mediaUrl } = attributes;

    const getSliderLimits = () => {
        switch ( widthUnit ) {
            case 'px':  return { min: 100, max: 1600, step: 10 };
            case 'em':  return { min: 1,   max: 150,   step: 0.5 };
            case '%':
            default:    return { min: 10,  max: 100,  step: 5 };
        }
    };

    const limits = getSliderLimits();

	// Use block props to apply the style dynamically to the wrapper
	const blockProps = useBlockProps( {
		style: { background: `url(${mediaUrl}) center/cover no-repeat` }
	} );

    const availableUnits = [
        { value: 'px', label: 'px', default: 400 },
        { value: '%', label: '%', default: 100 },
        { value: 'em', label: 'em', default: 10 }
    ];

    // Handle image selection
    const onSelectMedia = ( media ) => {
    	const thumbnailUrl = media.sizes?.thumbnail?.url || media.url;

        setAttributes( {
            mediaId: media.id,
            mediaUrl: media.url,
        } );
    };

    // Handle image removal
    const onRemoveMedia = () => {
        setAttributes( {
            mediaId: 0,
            mediaUrl: '',
        } );
    };

    const HERO_CONTAINER = [
        [ 'core/group', { 
            className: 'dphb-group', 
            layout: { 
                type: 'constrained',
                contentSize: '500px',
                justifyContent: 'left'
            },
            style: {
                spacing: {
                  padding: {
                    left: '35px',   // Left padding value
                    right: '35px'   // Right padding value
                  }
                },
                dimensions : {
                    minHeight: '400px'
                }
            },            
            templateLock: false,
            
            // Highlight-start
            // Localized block setting override
            settings: {
                dimensions: {
                    minHeight: true // Disables the minimum height panel entirely
                }                              
            }
            // Highlight-end
        }, [
            [ 'create-block/dynamic-hero-title-el', {} ],
            [ 'create-block/dynamic-hero-text-el', {} ]
        ] ]
    ];

	return (
		<>
			<InspectorControls>
                <PanelBody title="Layout Settings" initialOpen={false}>
					<h3>{ __( 'Fallback Background Image', 'dynhero' ) }</h3>
		            <MediaUploadCheck>
		                <MediaUpload
		                    onSelect={ onSelectMedia }
		                    allowedTypes={ [ 'image' ] }
		                    value={ mediaId }
		                    render={ ( { open } ) => (
		                        <div className="image-picker-container">
		                            { ! mediaId ? (
		                                <Button 
		                                    variant="secondary" 
		                                    onClick={ open }
		                                >
		                                    { __( 'Select Image', 'dynhero' ) }
		                                </Button>
		                            ) : (
		                                <>
		                                    <img src={ mediaUrl } alt="" />
		                                    <div className="button-actions">
		                                        <Button variant="secondary" onClick={ open }>
		                                            { __( 'Replace Image', 'dynhero' ) }
		                                        </Button>
		                                        <Button variant="link" isDestructive onClick={ onRemoveMedia }>
		                                            { __( 'Remove Image', 'dynhero' ) }
		                                        </Button>
		                                    </div>
		                                </>
		                            ) }
		                        </div>
		                    ) }
		                />
		            </MediaUploadCheck>		                
		            <hr />
					<ColorGradientControl
					    label={ __( 'Background Overlay', 'dynhero' ) }
					    gradientValue={ gradient }
                        gradients={[]}
					    onGradientChange={ ( currentGradient ) => setAttributes( { gradient: currentGradient } ) }				    
					/> 
                    <hr />
                    {/* The Visual Slider Control */}
                    <RangeControl
                        label={ __( 'Mesuring Unit', 'dynhero' ) + `(${widthUnit})` }
                        value={ widthNum }
                        onChange={ ( val ) => setAttributes( { widthNum: val } ) }
                        min={ limits.min }
                        max={ limits.max }
                        step={ limits.step }
                    />
                    {/* The Unit Changer Dropdown */}
                    <RadioControl
                        label={ __( 'Mesuring Unit', 'dynhero' ) }
                        selected={ widthUnit }
                        options={ [
                            { label: __('Percentage (%)', 'dynhero' ), value: '%' },
                            { label: __('Pixels (px)', 'dynhero' ), value: 'px' },
                            { label: __('Em Units (em)', 'dynhero' ), value: 'em' },
                        ] }
                        onChange={ ( unit ) => {
                            // Automatically reset value to a safe unit default
                            const defaults = { '%': 80, 'px': 1200, 'em': 80 };
                            setAttributes( { widthUnit: unit, widthNum: defaults[unit] } );
                        } }
                    />
                </PanelBody>	             
			</InspectorControls>

			<div { ...blockProps } > 
				{/* 1. Added dynamic background style here using your gradient attribute */}
				<div 
					className='overlay' 
					style={ { background: gradient } }
				>
		            <div style={ { width: `${ widthNum }${ widthUnit }`, margin: '0 auto' } }>
                        <InnerBlocks 
                            template={HERO_CONTAINER} 
                            templateLock="insert"
                        />                        			
		            </div>				
	            </div>
			</div>
		</>
	);
}