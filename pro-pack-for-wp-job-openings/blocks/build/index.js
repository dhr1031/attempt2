/******/ (function() { // webpackBootstrap
/*!****************************!*\
  !*** ./blocks/src/main.js ***!
  \****************************/
var addFilter = wp.hooks.addFilter;
var _wp$element = wp.element,
  Fragment = _wp$element.Fragment,
  useEffect = _wp$element.useEffect;
var _ref = wp.blockEditor || wp.editor,
  InspectorControls = _ref.InspectorControls;
var __ = wp.i18n.__;
var _wp$components = wp.components,
  PanelBody = _wp$components.PanelBody,
  TextControl = _wp$components.TextControl,
  ToggleControl = _wp$components.ToggleControl,
  SelectControl = _wp$components.SelectControl;
wp.hooks.addAction('after_awsm_block_job_listing', 'awsm_job_block/job_listing_options', function (block_job_listing, props) {
  var _props$attributes = props.attributes,
    position_filling = _props$attributes.position_filling,
    featured_image_size = _props$attributes.featured_image_size,
    setAttributes = props.setAttributes;
  var intermediate_image_sizes = awsmJobsAdmin.awsm_featured_image_block;
  useEffect(function () {
    if (intermediate_image_sizes.length > 0 && typeof featured_image_size === "undefined") {
      var _intermediate_image_s;
      // Default to the first available size
      setAttributes({
        featured_image_size: (_intermediate_image_s = intermediate_image_sizes[0]) === null || _intermediate_image_s === void 0 ? void 0 : _intermediate_image_s.value
      });
    }
  }, [intermediate_image_sizes, featured_image_size, setAttributes]);
  var options = intermediate_image_sizes.map(function (image) {
    return {
      label: image.text,
      // Ensure these keys match your data structure
      value: image.value
    };
  });
  var add_options_to_block = wp.element.createElement(Fragment, null, wp.element.createElement(ToggleControl, {
    label: __("Hide Jobs Filled", "wp-job-openings"),
    checked: position_filling,
    onChange: function onChange(position_filling) {
      return setAttributes({
        position_filling: position_filling
      });
    }
  }), " ", wp.element.createElement(SelectControl, {
    label: __("Feature image size", "wp-job-openings"),
    value: featured_image_size,
    options: options,
    onChange: function onChange(value) {
      setAttributes({
        featured_image_size: value
      });
    }
  }));
  block_job_listing.push(add_options_to_block);
});
/******/ })()
;
//# sourceMappingURL=index.js.map