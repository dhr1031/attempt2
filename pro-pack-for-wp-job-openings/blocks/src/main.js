const { addFilter } = wp.hooks;
const { Fragment, useEffect } = wp.element;
const { InspectorControls } = wp.blockEditor || wp.editor;
const { __ } = wp.i18n;
const { PanelBody, TextControl, ToggleControl, SelectControl } = wp.components;

wp.hooks.addAction( 'after_awsm_block_job_listing', 'awsm_job_block/job_listing_options', function(block_job_listing,props){
    const {
        attributes: { position_filling,featured_image_size },
        setAttributes
    } = props;

    const intermediate_image_sizes = awsmJobsAdmin.awsm_featured_image_block; 

    useEffect(() => {
        if (intermediate_image_sizes.length > 0 && typeof featured_image_size === "undefined") {
            // Default to the first available size
            setAttributes({ featured_image_size: intermediate_image_sizes[0]?.value });
        }
    }, [intermediate_image_sizes, featured_image_size, setAttributes]);

    const options = intermediate_image_sizes.map(image => ({
        label: image.text,  // Ensure these keys match your data structure
        value: image.value
    }));

    const add_options_to_block = <Fragment><ToggleControl label={__("Hide Jobs Filled", "wp-job-openings")} checked={position_filling} onChange={(position_filling) => setAttributes({ position_filling })} /> <SelectControl
    label={__("Feature image size", "wp-job-openings")} value={featured_image_size} options={options} onChange={(value) => { setAttributes({ featured_image_size: value });  }}/></Fragment>; 
    
    block_job_listing.push(add_options_to_block);   
});
