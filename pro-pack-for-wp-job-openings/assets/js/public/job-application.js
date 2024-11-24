/* global awsmJobs, awsmJobsPublic, awsmProJobsPublic, Dropzone, grecaptcha, elementorFrontend */

'use strict';

if (typeof Dropzone !== 'undefined') {
	Dropzone.autoDiscover = false;
}

jQuery(function($) {



	var awsmProJobs = window.awsmProJobs = window.awsmProJobs || {};

	awsmProJobs.validateForm = function($form, options) {
		options = typeof options !== 'undefined' ? options : {};
		var defaultOptions = {
			errorElement: 'div',
			errorClass: 'awsm-job-form-error',
			errorPlacement: function(error, element) {
				error.appendTo(element.parents('.awsm-job-form-group'));
			}
		};
		var validatorOptions = $.extend({}, defaultOptions, options);
		var validator = $form.validate(validatorOptions);
		return validator;
	};

	/**
	 * Escape HTML - based on mustache.js
	 *
	 * http://github.com/janl/mustache.js
	 */
	var entities = {
		'&': '&amp;',
		'<': '&lt;',
		'>': '&gt;',
		'"': '&quot;',
		// eslint-disable-next-line quotes
		"'": '&#39;',
		'/': '&#x2F;',
		'`': '&#x60;',
		'=': '&#x3D;'
	};
	function escapeHTML(string) {
		return String(string).replace(/[&<>"'/`=]/g, function(s) {
			return entities[s];
		});
	}

	function isDateFieldSupported() {
		var input = document.createElement('input');
		input.setAttribute('type', 'date');
		var dateStringVal = 'yyyy-mm-dd';
		input.setAttribute('value', dateStringVal);
		return (input.value !== dateStringVal);
	}

	$('.awsm-job-form-iti-wrapper .awsm-job-form-field').each(function(index) {
		countrySelect(index, $(this));
	});

	function countrySelect(index, $input) {
		if (typeof awsmProJobs.iti == 'undefined') {
			awsmProJobs.iti = {};
		}
		var $parent = $input.parent('.awsm-job-form-iti-wrapper');
		var defaultCountry = $parent.data('defaultCountry');
		awsmProJobs.iti[index] = window.intlTelInput($input.get(0), {
			separateDialCode: true,
			initialCountry: defaultCountry,
			utilsScript: awsmProJobsPublic.iti.utils_url,
			customContainer: 'awsm-job-form-control'
		});
	}

	function awsmDropDown($elem) {
		if ('selectric' in awsmJobsPublic.vendors && awsmJobsPublic.vendors.selectric) {
			$elem.selectric({
				onInit: function(select, selectric) {
					var id = select.id;
					if (selectric && selectric.elements && selectric.elements.input) {
						var $input = $(selectric.elements.input);
						$(select).attr('id', 'selectric-' + id);
						$input.attr('id', id);
					}
				},
				arrowButtonMarkup: '<span class="awsm-selectric-arrow-drop">&#x25be;</span>',
				customClass: {
					prefix: 'awsm-selectric',
					camelCase: false
				}
			});
		}
	}

	$('.awsm-jobs-pro-custom-form-content').on('click', '.awsm-jobs-pro-application-form-btn', function(e) {
		e.preventDefault();
		var $elem = $(this);
		var targetURL = $elem.data('url');
		if (typeof targetURL !== 'undefined' && targetURL.length > 0) {
			var target = $elem.data('target');
			if (typeof target !== 'undefined' && target.length > 0) {
				window.open(targetURL, target, 'noopener,noreferrer');
			} else {
				window.location.href = targetURL;
			}
		}
	});

	if ('datepicker' in awsmProJobsPublic) {
		if (! isDateFieldSupported() || awsmProJobsPublic.datepicker === 'custom') {
			$('.awsm-job-form-date-group .awsm-job-form-field').flatpickr({
				altInput: true,
				altFormat: 'F j, Y',
				dateFormat: 'Y-m-d'
			});
		}
	}

	awsmProJobs.dzUploadHandler = function($fileControl, $hiddenInputContainer) {
		$hiddenInputContainer = typeof $hiddenInputContainer !== 'undefined' ? $hiddenInputContainer : 'body';

		var fileUploadData = {};
		$fileControl.each(function() {
			var $elem = $(this);
			var $form = $elem.parents('.awsm-application-form');
			var $wrapper = $elem.parent('.awsm-job-form-group');
			var $field = $wrapper.find('input[type="hidden"]');
			var $submitBtn = $form.find('.awsm-application-submit-btn');
			var jobId = $form.find('input[name="awsm_job_id"]').val();
			var fieldId = $field.attr('name');
			var maxFiles = Number($elem.data('maxFiles'));
			var maxSize = Number($elem.data('fileSize'));
			var i18n = awsmProJobsPublic.i18n.file_upload;

			fileUploadData[jobId] = fileUploadData[jobId] || {};

			var dzOptions = {
				url: awsmJobsPublic.ajaxurl,
				acceptedFiles: $elem.data('accept'),
				hiddenInputContainer: $hiddenInputContainer,
				addRemoveLinks: true,
				maxFiles: maxFiles,
				maxFilesize: (Number(maxSize) / (1024 * 1024)).toFixed(2),
				parallelUploads: 1,
				dictCancelUpload: i18n.cancel_upload,
				dictUploadCanceled: i18n.upload_canceled,
				dictCancelUploadConfirmation: i18n.cancel_upload_confirmation,
				dictRemoveFile: i18n.remove_file,
				dictMaxFilesExceeded: i18n.max_files,
				dictInvalidFileType: i18n.invalid_file_type,
				dictFileTooBig: i18n.file_size
			};
			var awsmDropZone = new Dropzone($elem.get(0), dzOptions);

			awsmDropZone.on('sending', function(file, xhr, formData) {
				$submitBtn.prop('disabled', true);
				$(file.previewElement).find('.dz-upload').attr('data-uploadtext', i18n.uploading);

				var acceptedFiles = [];
				$.each(awsmDropZone.files, function(index, file) {
					if (file.accepted && 'awsmJobsFileName' in file) {
						acceptedFiles.push(file.awsmJobsFileName);
					}
				});

				var data = [
					{
						name: 'action',
						value: 'awsm_applicant_form_file_upload'
					},
					{
						name: 'job_id',
						value: jobId
					},
					{
						name: 'field_id',
						value: fieldId
					},
					{
						name: 'accepted_files',
						value: JSON.stringify(acceptedFiles)
					}
				];
				$.each(data, function(index, field) {
					if ('name' in field && 'value' in field) {
						formData.append(field.name, field.value);
					}
				});
			});

			awsmDropZone.on('addedfile', function() {
				if (maxFiles > 1 && awsmDropZone.files.length > 1) {
					$elem.animate({
						scrollTop: $elem.prop('scrollHeight')
					}, 1200);
				}
			});

			awsmDropZone.on('success', function(file, response) {
				$submitBtn.prop('disabled', false);
				$elem.removeClass('awsm-form-drag-and-drop-invalid-control');

				if ('data' in response) {
					file.awsmJobsFileName = response.data;
					fileUploadData[jobId][fieldId] = fileUploadData[jobId][fieldId] || [];
					fileUploadData[jobId][fieldId].push(response.data);
					$field.val(JSON.stringify(fileUploadData[jobId][fieldId]));
				}
			});

			awsmDropZone.on('removedfile', function(file) {
				if ('awsmJobsFileName' in file) {
					var fileIndex = fileUploadData[jobId][fieldId].indexOf(file.awsmJobsFileName);
					if (fileIndex !== -1) {
						fileUploadData[jobId][fieldId].splice(fileIndex, 1);
						var fileData = file.awsmJobsFileName;
						if (fileData && typeof fileData === 'object') {
							var filePath = fileData.file;
							var fileTitle = fileData.title;			
							$.ajax({
								url: awsmJobsPublic.ajaxurl,
								type: 'POST',
								data: {
									action: 'awsm_applicant_form_remove_file',
									'file': filePath,
									'title': fileTitle
								},
								dataType: 'json'
							});
						}
						$field.val(JSON.stringify(fileUploadData[jobId][fieldId]));
					}
				}
			});

			$form.on('awsmjobs_application_submitted', function() {
				awsmDropZone.removeAllFiles(true);
			});
		});

		if ($fileControl.length > 0) {
			var $applicationForm = $('.awsm-application-form');
			$applicationForm.on('submit', function(event) {
				event.preventDefault();
				var $form = $(this);
				$form.find('.awsm-form-drag-and-drop-file-control').each(function() {
					var $elem = $(this);
					var required = Number($elem.data('required'));
					if (required === 1) {
						var $wrapper = $elem.parent('.awsm-job-form-group');
						var $field = $wrapper.find('input[type="hidden"]');
						var fieldVal = $field.val().trim().replace(/[[\]]/g, '');
						if (fieldVal.length === 0) {
							$elem.addClass('awsm-form-drag-and-drop-invalid-control');
						}
					}
				});
			});
		}
	};

	awsmProJobs.dzUploadHandler($('.awsm-form-drag-and-drop-file-control'));

	// ========== Job Aplication Form Handler ==========
	if (typeof awsmJobs !== 'undefined' && 'submitApplication' in awsmJobs && typeof awsmJobs.submitApplication === 'function') {
		var submitApplication = awsmJobs.submitApplication;
		awsmJobs.submitApplication = function($form, data) {
			data = typeof data !== 'undefined' ? data : {};
			data.fields = [];
			if ($('.awsm-job-form-iti-wrapper').length > 0) {
				$('.awsm-job-form-iti-wrapper .awsm-job-form-field').each(function(i) {
					var $field = $(this);
					if (typeof awsmProJobs.iti !== 'undefined' && i in awsmProJobs.iti) {
						var number = awsmProJobs.iti[i].getNumber();
						if (number.length > 0) {
							var countryData = awsmProJobs.iti[i].getSelectedCountryData();
							var countryCode = awsmProJobsPublic.iti.show_country_code ? '(' + countryData.iso2.toUpperCase() + ') ' : '';
							var fieldVal = awsmProJobs.iti[i].isValidNumber() ? countryCode + number : '-1';
							data.fields.push({
								name: $field.attr('name').replace('iti_', ''),
								value: fieldVal
							});
						}
					}
				});
			}
			if ('recaptcha' in awsmProJobsPublic && awsmProJobsPublic.recaptcha.site_key && typeof grecaptcha !== 'undefined') {
				grecaptcha.ready(function() {
					grecaptcha.execute(awsmProJobsPublic.recaptcha.site_key, {action: awsmProJobsPublic.recaptcha.action}).then(function(token) {
						data.fields.push({
							name: 'g-recaptcha-response',
							value: token
						});
						submitApplication($form, data);
					});
				});
			} else {
				submitApplication($form, data);
			}
		};
	}

	// ========== Job Aplication Form - Repeater Fields ==========
	if ($('.awsm-application-form .awsm-job-form-repeater-group').length > 0) {
		if ($('.awsm-application-form .awsm-job-form-repeater-group[data-required="true"]').length > 0) {
			$('.awsm-application-form').each(function() {
				var $form = $(this);
				if ($form.find('.awsm-job-form-repeater-group[data-required="true"]').length > 0) {
					$form.find('.awsm-application-submit-btn').prop('disabled', true);
				}
			});
		}

		awsmProJobs.formValidator = null;
		var repeaterIndex = {};
		$('.awsm-application-form').on('click', '.awsm-job-form-repeater-add-control', function() {
			var $btn = $(this);
			var $controls = $btn.parents('.awsm-job-form-repeater-controls');
			var $form = $btn.parents('.awsm-application-form');
			var $repeaterItem = $btn.parents('.awsm-job-form-repeater-item');
			var $repeaterGroup = $repeaterItem.parents('.awsm-job-form-repeater-group');
			$btn.prop('disabled', true);
			$repeaterGroup.addClass('awsm-job-form-repeater-group-active');

			// Validate repeater fields.
			if (! awsmProJobs.formValidator) {
				$form.data('validator', null);
				$form.off('validate');
			} else {
				awsmProJobs.formValidator.destroy();
			}
			awsmProJobs.formValidator = awsmProJobs.validateForm($form, {
				ignore: ':not(.awsm-job-form-repeater-group-active .awsm-job-form-field:visible)'
			});
			var proceed = $form.valid();
			if (proceed) {
				var formData = $repeaterItem.find('.awsm-job-form-field').serializeArray();
				var filteredFormData = $.grep(formData, function(fieldData) {
					return fieldData.value.length > 0;
				});

				if (filteredFormData.length > 0) {
					var entryFields = $repeaterGroup.data('entries').split(',');
					var maxItems = $repeaterGroup.data('max');
					var $item = $repeaterItem.clone();
					var itemName = $repeaterItem.data('name');
					var itemIndex = $repeaterItem.data('index');
					var itemId = itemName + '[' + itemIndex + ']';

					if (! (itemName in repeaterIndex)) {
						repeaterIndex[itemName] = 0;
					}
					repeaterIndex[itemName]++;
					var newItemIndex = repeaterIndex[itemName];
					var groupIdPrefix = 'awsm-job-form-repeater-item-' + itemName;
					var groupId = groupIdPrefix + '-' + newItemIndex;
					var newItemId = itemName + '[' + newItemIndex + ']';

					$item.find('label').each(function() {
						var $label = $(this);
						var labelFor = $label.attr('for');
						$label.attr('for', labelFor.replace(itemId, newItemId));
					});

					$item.find('.awsm-job-form-field').each(function() {
						var $field = $(this);
						var fieldId = $field.attr('id').replace(itemId, newItemId);
						var fieldName = $field.attr('name').replace(itemId, newItemId);
						$field.attr('id', fieldId);
						$field.attr('name', fieldName);
					});

					var entryContent = '<div class="awsm-job-form-repeater-entry-item-group" data-target="' + groupId + '"><div class="awsm-job-form-repeater-entry-item">';
					$.each(entryFields, function(entryIndex, entryField) {
						var entryFieldName = itemName + '[0][' + entryField + ']';
						var $labeWrapper = $repeaterItem.find('[name="' + entryFieldName + '"]').parents('.awsm-job-form-group').find('label').first().clone();
						$labeWrapper.find('span').remove();
						var label = $labeWrapper.text();
						var fieldVal = '';
						$.each(formData, function(index, formField) {
							if (formField.name === entryFieldName) {
								fieldVal = escapeHTML(formField.value);
							}
						});
						var newFieldName = newItemId + '[' + entryField + ']';
						entryContent += '<div class="awsm-job-form-repeater-entry-item-row"><div class="awsm-job-form-repeater-entry-item-column">' + label.trim() + '<span>:</span></div><div class="awsm-job-form-repeater-entry-item-column" data-name="' + newFieldName  + '">' + fieldVal + '</div></div>';
						if ($item.find('select[name="' + newFieldName + '"]').length > 0) {
							var $selectControl = $item.find('select[name="' + newFieldName + '"]');
							$selectControl.find('option').removeAttr('selected');
							$selectControl.find('option[value="' + fieldVal + '"]').attr('selected', 'selected');
						}
					});
					entryContent += '</div>';
					entryContent += '<div class="awsm-job-form-repeater-entry-item-controls"><a href="#" class="awsm-job-form-repeater-edit-control">' + awsmProJobsPublic.i18n.repeater.edit + '</a><a href="#" class="awsm-job-form-repeater-remove-control">' + awsmProJobsPublic.i18n.repeater.remove + '</a></div>';
					entryContent += '</div>';
					$repeaterGroup.find('.awsm-job-form-repeater-entries').append(entryContent);
					$repeaterItem.find('.awsm-job-form-field').each(function() {
						var $field = $(this);
						if ($field.filter('.awsm-job-select-control').length > 0) {
							$field.filter('.awsm-job-select-control').prop('selectedIndex', 0).selectric('refresh');
						}
						$field.filter('.awsm-job-form-control:not(.awsm-job-select-control)').val('');
						$field.filter('.awsm-job-form-options-control').removeAttr('checked');
					});

					var $mainRepeaterGroup = $repeaterGroup.find('.awsm-job-form-repeater-item[data-index="0"]');
					if ($repeaterGroup.find('.awsm-job-form-repeater-entry-item-group').length === 1) {
						$repeaterGroup.find('.awsm-job-form-repeater-entries').after('<div class="awsm-job-form-repeater-extra-controls"><button type="button" class="awsm-job-form-repeater-add-more-control awsm-jobs-primary-button">' + awsmProJobsPublic.i18n.repeater.add_more + '</button></div>');
					} else {
						$repeaterGroup.find('.awsm-job-form-repeater-extra-controls').removeClass('awsm-job-hide');
						if (typeof maxItems !== 'undefined' && $repeaterGroup.find('.awsm-job-form-repeater-entry-item-group').length === maxItems) {
							$repeaterGroup.find('.awsm-job-form-repeater-extra-controls').addClass('awsm-job-hide');
						}
					}
					$mainRepeaterGroup.slideUp(function() {
						$mainRepeaterGroup.addClass('awsm-job-hide').removeAttr('style');
						$controls.find('.awsm-job-form-repeater-cancel-control').removeClass('awsm-job-hide');
						$('html, body').animate({
							scrollTop: $repeaterGroup.find('.awsm-job-form-repeater-entries .awsm-job-form-repeater-entry-item-group').last().offset().top
						}, 750);
					});

					$item.find('.awsm-job-select-control').each(function() {
						var $dropDownElem = $(this);
						var $dropDown = $dropDownElem.clone();
						var $dropDownId = $dropDown.attr('id');
						$dropDown.attr('id', $dropDownId.replace('selectric-', ''));
						var $groupWrapper = $dropDownElem.parents('.awsm-job-form-group');
						$groupWrapper.find('.awsm-selectric-wrapper').remove();
						$groupWrapper.append($dropDown);
					});
					awsmDropDown($item.find('.awsm-job-select-control'));

					$item.find('.awsm-job-form-repeater-controls').html('<button type="button" class="awsm-job-form-repeater-update-control awsm-jobs-primary-button">' + awsmProJobsPublic.i18n.repeater.update + '</button>');
					$item.addClass('awsm-job-hide').attr('data-index', newItemIndex).data('index', newItemIndex);
					$item.attr('id', groupId);
					$repeaterGroup.append($item);

					// Remove the disabled attribute from submit button.
					$form.find('.awsm-application-submit-btn').prop('disabled', false);

					// Reset form validation excluding repeater fields.
					awsmProJobs.formValidator.destroy();
					awsmProJobs.formValidator = awsmProJobs.validateForm($form, {
						ignore: ':not(.awsm-job-form-field:visible), .awsm-job-form-repeater-group .awsm-job-form-field'
					});

					$repeaterGroup.removeClass('awsm-job-form-repeater-group-active');

					$form.trigger('awsmjobs_repeater_add_action', [ $btn, $form ]);
				}
			}

			setTimeout(function() {
				$btn.prop('disabled', false);
			}, 400);
		});

		$('.awsm-application-form').on('click', '.awsm-job-form-repeater-add-more-control', function() {
			var $btn = $(this);
			var $controls = $btn.parents('.awsm-job-form-repeater-extra-controls');
			var $form = $btn.parents('.awsm-application-form');
			var $wrapper = $btn.parents('.awsm-job-form-repeater-group');
			var $mainRepeaterGroup = $wrapper.find('.awsm-job-form-repeater-item[data-index="0"]');
			$controls.addClass('awsm-job-hide');
			$mainRepeaterGroup.removeClass('awsm-job-hide');
			$('html, body').animate({
				scrollTop: $mainRepeaterGroup.offset().top
			}, 750);

			$form.trigger('awsmjobs_repeater_add_more_action', [ $btn, $form ]);
		});

		$('.awsm-application-form').on('click', '.awsm-job-form-repeater-cancel-control', function() {
			var $btn = $(this);
			var $form = $btn.parents('.awsm-application-form');
			var $repeaterItem = $btn.parents('.awsm-job-form-repeater-item');
			var $repeaterGroup = $repeaterItem.parents('.awsm-job-form-repeater-group');

			var animated = false;
			$('html, body').animate({
				scrollTop: $repeaterGroup.offset().top
			}, 750, function() {
				if (! animated) {
					setTimeout(function() {
						$repeaterItem.addClass('awsm-job-hide');
						$repeaterGroup.find('.awsm-job-form-repeater-extra-controls').removeClass('awsm-job-hide');
					}, 200);
					animated = true;
				}
			});

			$form.trigger('awsmjobs_repeater_cancel_action', [ $btn, $form ]);
		});

		$('.awsm-application-form').on('click', '.awsm-job-form-repeater-edit-control', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var $form = $btn.parents('.awsm-application-form');
			var $entryGroup = $btn.parents('.awsm-job-form-repeater-entry-item-group');
			var $mainFieldGroup = $entryGroup.parents('.awsm-job-form-repeater-group');
			var $repeaterGroup = $('#' + $entryGroup.data('target'));
			$mainFieldGroup.find('.awsm-job-form-repeater-entry-item-group').removeClass('awsm-job-hide');
			$entryGroup.addClass('awsm-job-hide');
			$mainFieldGroup.find('.awsm-job-form-repeater-extra-controls').addClass('awsm-job-hide');
			$mainFieldGroup.find('.awsm-job-form-repeater-item').addClass('awsm-job-hide');
			$repeaterGroup.removeClass('awsm-job-hide');
			if (awsmProJobs.formValidator) {
				awsmProJobs.formValidator.destroy();
			}
			awsmProJobs.formValidator = awsmProJobs.validateForm($form, {
				ignore: ':not(.awsm-job-form-repeater-group-active .awsm-job-form-field:visible)'
			});
			$('html, body').animate({
				scrollTop: $repeaterGroup.offset().top
			}, 750);

			$form.trigger('awsmjobs_repeater_edit_action', [ $btn, $form ]);
		});

		$('.awsm-application-form').on('click', '.awsm-job-form-repeater-update-control', function() {
			var $btn = $(this);
			var $form = $btn.parents('.awsm-application-form');
			var $repeaterItem = $btn.parents('.awsm-job-form-repeater-item');
			var $repeaterGroup = $repeaterItem.parents('.awsm-job-form-repeater-group');
			var repeaterItemId = $repeaterItem.attr('id');

			$btn.prop('disabled', true);
			$repeaterGroup.addClass('awsm-job-form-repeater-group-active');
			if (awsmProJobs.formValidator) {
				var proceed = $form.valid();
				if (proceed) {
					var formData = $repeaterItem.find('.awsm-job-form-field').serializeArray();
					$.each(formData, function(index, formField) {
						var $entryItemColumn = $('.awsm-job-form-repeater-entry-item-column[data-name="' + formField.name + '"]');
						if ($entryItemColumn.length > 0) {
							$entryItemColumn.text(formField.value);
						}
					});
					$repeaterGroup.find('.awsm-job-form-repeater-extra-controls').removeClass('awsm-job-hide');
					$('.awsm-job-form-repeater-entry-item-group[data-target="' + repeaterItemId + '"]').removeClass('awsm-job-hide');
					$repeaterItem.addClass('awsm-job-hide');

					$('html, body').animate({
						scrollTop: $('.awsm-job-form-repeater-entry-item-group[data-target="' + repeaterItemId + '"]').offset().top
					}, 750);

					// Reset form validation excluding repeater fields.
					awsmProJobs.formValidator.destroy();
					awsmProJobs.formValidator = awsmProJobs.validateForm($form, {
						ignore: ':not(.awsm-job-form-field:visible), .awsm-job-form-repeater-group .awsm-job-form-field'
					});

					$repeaterGroup.removeClass('awsm-job-form-repeater-group-active');

					$form.trigger('awsmjobs_repeater_update_action', [ $btn, $form ]);
				}
			}

			setTimeout(function() {
				$btn.prop('disabled', false);
			}, 400);
		});

		$('.awsm-application-form').on('click', '.awsm-job-form-repeater-remove-control', function(e) {
			e.preventDefault();
			var $btn = $(this);
			var $form = $btn.parents('.awsm-application-form');
			var $entryGroup = $btn.parents('.awsm-job-form-repeater-entry-item-group');
			var $mainFieldGroup = $entryGroup.parents('.awsm-job-form-repeater-group');
			var $repeaterGroup = $('#' + $entryGroup.data('target'));
			$entryGroup.slideUp(function() {
				$entryGroup.remove();
				$repeaterGroup.remove();
				if ($mainFieldGroup.find('.awsm-job-form-repeater-entry-item-group').length === 0) {
					var $mainRepeaterGroup = $mainFieldGroup.find('.awsm-job-form-repeater-item[data-index="0"]');
					$mainFieldGroup.find('.awsm-job-form-repeater-extra-controls').remove();
					$mainRepeaterGroup.find('.awsm-job-form-repeater-cancel-control').addClass('awsm-job-hide');
					$mainRepeaterGroup.removeClass('awsm-job-hide');
					if ($mainFieldGroup.data('required')) {
						$form.find('.awsm-application-submit-btn').prop('disabled', true);
					}
				}

				$form.trigger('awsmjobs_repeater_remove_action', [ $form ]);
			});
		});
	}

	// ========== Form Submit Confirmation ==========
	awsmProJobs.formSubmitConfirmationHandler = function($form) {
		$form.on('awsmjobs_application_submitted', function(e, response) {
			if ('data' in response) {
				$.ajax({
					url: awsmJobsPublic.ajaxurl,
					type: 'POST',
					data: {
						action: 'awsm_applicant_attachments_handler',
						'r_id': response.data.id
					},
					dataType: 'json'
				});
				if ('type' in response.data && (response.data.type === 'page' || response.data.type === 'redirect_url')) {
					var $form = $(this);
					$form.parents('.awsm-job-form-inner').find('.awsm-application-message').remove();
					window.location = response.data.url;
				}
			}
		});
	};

	awsmProJobs.formSubmitConfirmationHandler($('.awsm-application-form'));

	/**
	 * ========== Third-party support ==========
	 */

	// Elementor Popup - Application Form
	$(window).on('elementor/frontend/init', function() {
		var elModalSupport = setInterval(function() {
			if (typeof elementorFrontend !== 'undefined') {
				clearInterval(elModalSupport);
				$.each(elementorFrontend.documentsManager.documents, function(id, document) {

					// Check if this is a popup document.
					if (document.getModal) {

						// Remove the auto-generated application form if popup exists.
						if (document.$element.find('.awsm-application-form').length > 0) {
							$('.awsm-job-single-wrap .awsm-job-form').remove();
						} else {
							if (document.$element.find('.awsm-jobs-pro-custom-form-content.awsm-job-hide').length > 0) {
								$('body').addClass('awsm-jobs-pro-form-disabled');
							} else if (document.$element.find('.awsm-jobs-pro-custom-form-content').length > 0) {
								$('.awsm-job-single-wrap .awsm-jobs-pro-custom-form-content').remove();
							}
						}
						document.getModal().on('show', function() {
							var $form = document.$element.find('.awsm-application-form');

							// Check if WP Job Openings application form exists.
							if ($form.length > 0) {

								// Validation.
								awsmProJobs.validateForm($form);

								// Form submit handler.
								$form.on('submit', function(event) {
									event.preventDefault();
									var $form = $(this);
									var proceed = $form.valid();
									if (proceed) {
										awsmJobs.submitApplication($form);
									}
								});

								// Handle dropdown fields.
								if ($form.find('.awsm-job-select-control').length > 0) {
									$form.find('.awsm-job-select-control').each(function() {
										var $elem = $(this);
										var $dropDown = $elem.clone();
										var $groupWrapper = $elem.parents('.awsm-job-form-group');
										$groupWrapper.find('.awsm-selectric-wrapper').remove();
										$groupWrapper.append($dropDown);
									});

									awsmDropDown($form.find('.awsm-job-select-control'));
								}

								// Handle drag and drop file upload.
								awsmProJobs.dzUploadHandler($form.find('.awsm-form-drag-and-drop-file-control'), document.$element.get(0));

								// Form Submit Confirmation.
								awsmProJobs.formSubmitConfirmationHandler($form);
							}
						});
					}
				});
			}
		}, 100);
	});
});
