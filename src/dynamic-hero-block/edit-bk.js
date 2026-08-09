import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { useBlockProps, InspectorControls, ColorPalette,  __experimentalColorGradientControl as ColorGradientControl, MediaUpload, MediaUploadCheck, InnerBlocks, RichText} from '@wordpress/block-editor';
import { PanelBody, RangeControl, SelectControl, RadioControl, TextControl, Button} from '@wordpress/components';
import { useSelect } from '@wordpress/data';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { widthNum, widthUnit, contentWidthNum, contentWidthUnit, gradient, mediaId, mediaUrl } = attributes;
	const {         
        bodyText, bodyFontSize, bodyFontWeight, bodyLineHeight, bodyColor , bodyFontFamily
    } = attributes;

    const fontFamilies = useSelect( ( select ) => {
        const settings = select( 'core/block-editor' ).getSettings();
        
        let fonts = settings?.fontFamilies || 
                    settings?.typography?.fontFamilies || 
                    settings?.baseStyles?.typography?.fontFamilies;

        return fonts && fonts.length > 0 ? fonts : [];
    }, [] );

    const fontOptions = [
        { label: 'Select a font...', value: '' }, // Default placeholder
        ...fontFamilies.map( ( font ) => ( {
            label: font.name,
            value: font.fontFamily, // Or use font.slug if your block relies on CSS classes
        } ) )
    ];

    const sizeOptions = [
        { label: 'Small', value: '0.875rem' },
        { label: 'Normal', value: '1rem' },
        { label: 'Large', value: '1.5rem' },
        { label: 'Huge', value: '2.25rem' }
    ];

    const weightOptions = [
        { label: 'Light', value: '300' },
        { label: 'Regular', value: '400' },
        { label: 'Medium', value: '500' },
        { label: 'Bold', value: '700' }
    ];

    const getSliderLimits = () => {
        switch ( widthUnit ) {
            case 'px':  return { min: 100, max: 1200, step: 10 };
            case 'em':  return { min: 1,   max: 50,   step: 0.5 };
            case '%':
            default:    return { min: 10,  max: 100,  step: 5 };
        }
    };

    const limits = getSliderLimits();

    const getSliderContentWidthLimits = () => {
        switch ( contentWidthUnit ) {
            case 'px':  return { min: 100, max: 1200, step: 10 };
            case 'em':  return { min: 1,   max: 50,   step: 0.5 };
            case '%':
            default:    return { min: 10,  max: 100,  step: 5 };
        }
    };    

    const contentWidthlimits = getSliderContentWidthLimits();

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

    const MY_TEMPLATE = [
        [ 'core/group', { 
            className: 'my-custom-group-wrapper', 
            layout: { type: 'constrained' },
            templateLock: false,
            
            // Highlight-start
            // Localized block setting override
            settings: {
                dimensions: {
                    minHeight: false // Disables the minimum height panel entirely
                },
                spacing: {
                    padding: false, // Optional: Disables block padding if needed
                    margin: false   // Optional: Disables block margin if needed
                }
            }
            // Highlight-end
        }, [
            [ 'create-block/dynamic-page-title-block', {} ],
            [ 'create-block/dynamic-page-subtext-block', {} ],
        ] ]
    ];

	return (
		<>
			<InspectorControls>
                <PanelBody title="Layout Settings" initialOpen={false}>
					<h3>Fallback Background Image</h3>
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
					    label={ __( 'Background Overlay' ) }
					    gradientValue={ gradient }
					    onGradientChange={ ( currentGradient ) => setAttributes( { gradient: currentGradient } ) }				    
					/> 
                    <hr />
                    {/* The Visual Slider Control */}
                    <RangeControl
                        label={ `Inner Width (${widthUnit})` }
                        value={ widthNum }
                        onChange={ ( val ) => setAttributes( { widthNum: val } ) }
                        min={ limits.min }
                        max={ limits.max }
                        step={ limits.step }
                    />
                    {/* The Unit Changer Dropdown */}
                    <RadioControl
                        label="Measuring Unit"
                        selected={ widthUnit }
                        options={ [
                            { label: 'Percentage (%)', value: '%' },
                            { label: 'Pixels (px)', value: 'px' },
                            { label: 'Em Units (em)', value: 'em' },
                        ] }
                        onChange={ ( unit ) => {
                            // Automatically reset value to a safe unit default
                            const defaults = { '%': 90, 'px': 1200, 'em': 60 };
                            setAttributes( { widthUnit: unit, widthNum: defaults[unit] } );
                        } }
                    />
                    <hr />
                    {/* The Visual Slider Control */}
                    <RangeControl
                        label={ `Content Width (${contentWidthUnit})` }
                        value={ contentWidthNum }
                        onChange={ ( val ) => setAttributes( { contentWidthNum: val } ) }
                        min={ contentWidthlimits.min }
                        max={ contentWidthlimits.max }
                        step={ contentWidthlimits.step }
                    />
                    {/* The Unit Changer Dropdown */}
                    <RadioControl
                        label="Measuring Unit"
                        selected={ contentWidthUnit }
                        options={ [
                            { label: 'Percentage (%)', value: '%' },
                            { label: 'Pixels (px)', value: 'px' },
                            { label: 'Em Units (em)', value: 'em' },
                        ] }
                        onChange={ ( unit2 ) => {
                            // Automatically reset value to a safe unit default
                            const defaults = { '%': 90, 'px': 1200, 'em': 60 };
                            setAttributes( { contentWidthUnit: unit2, contentWidthNum: defaults[unit2] } );
                        } }
                    />
                </PanelBody>	

                { /* Body Settings Panel */ }
                <PanelBody title="Excerpt/Subtext Styles" initialOpen={false}>
                    <SelectControl
                        label="Font Size"
                        value={bodyFontSize}
                        options={sizeOptions}
                        onChange={(val) => setAttributes({ bodyFontSize: val })}
                    />
                    <SelectControl
                        label="Font Family"
                        value={ bodyFontFamily }
                        options={ fontOptions }
                        onChange={ ( value ) => setAttributes( { fontFamily: value } ) }
                    />
                    <SelectControl
                        label="Font Weight"
                        value={bodyFontWeight}
                        options={weightOptions}
                        onChange={(val) => setAttributes({ bodyFontWeight: val })}
                    />
                    <TextControl
                        label="Line Height"
                        value={bodyLineHeight}
                        onChange={(val) => setAttributes({ bodyLineHeight: val })}
                    />                    
                    <p style={{ marginBottom: '5px' }}>Text Color</p>
                    <ColorPalette
                        value={bodyColor}
                        onChange={(val) => setAttributes({ bodyColor: val })}
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
                        <div style={ { width: `${ contentWidthNum }${ contentWidthUnit }` } }>
                            <InnerBlocks 
                                template={MY_TEMPLATE} 
                                templateLock="insert"
                            />                        			
                        </div>
		            </div>				
	            </div>
			</div>
		</>
	);
}