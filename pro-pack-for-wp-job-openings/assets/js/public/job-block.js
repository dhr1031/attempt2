jQuery(function($) {
    $(document).on('awsmJobBlockListingsData', function(event,parsedListingsAttrs) { 
        parsedListingsAttrs.push('position_filling');
		parsedListingsAttrs.push('featured_image_size');
    });
    
    $(document).on('awsmJobBlockFiltersFormData', function(event,$wrapper,formData) {
        var position_filling = $wrapper.data('position_filling'); 
		var featured_image_size = $wrapper.data('featured_image_size'); 

		if (typeof position_filling !== 'undefined') {
			formData.push({
				name: 'position_filling',
				value: position_filling
			});
		}

		if (typeof featured_image_size !== 'undefined') {
			formData.push({
				name: 'featured_image_size',
				value: featured_image_size
			});
		}

    });

    $(document).on('awsmjobs_block_load_more', function(event,$listingsContainer,wpData) { 
        var position_filling = $listingsContainer.data('position_filling'); 
		var featured_image_size = $listingsContainer.data('featured_image_size'); 

		if (typeof position_filling !== 'undefined') {
			wpData.push({
				name: 'position_filling',
				value: position_filling
			});
		}

		if (typeof featured_image_size !== 'undefined') {
			wpData.push({
				name: 'featured_image_size',
				value: featured_image_size
			});
		}

    });
});
