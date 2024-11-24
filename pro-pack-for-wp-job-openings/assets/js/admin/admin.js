/* global awsmJobsAdmin, awsmProJobsAdmin, jsPDF, tinyMCE */

'use strict';

jQuery(document).ready(function($) { 

	var applicationId = $('#awsm-pro-application-id').val();

	/*================ Applicant Mail Meta Tabs ================*/

	$('.awsm-applicant-meta-mail-container').on('click', '.awsm-jobs-applicant-mail-header', function() {
		$(this).parent().toggleClass('open');
	});

	$('ul.awsm-applicant-meta-mail-tabs a').on('click', function(e) {
		e.preventDefault();
		var $currentTab = $(this);
		if (! $currentTab.closest('li').hasClass('tabs')) {
			var tabPanelId = $currentTab.attr('href');
			$('ul.awsm-applicant-meta-mail-tabs li').removeClass('tabs');
			$currentTab.closest('li').addClass('tabs');
			$('.awsm-applicant-meta-mail-tabs-panel').hide();
			$(tabPanelId).fadeIn();
		}
	});

	/*================ Settings ================*/

	$('.awsm-settings-tab-wrapper').css('visibility', 'visible');

	/*================ Form Builder ================*/

	var $fbOptionsWrapper = $('.awsm-job-form-builder-container');
	var tmplTagfieldRegEx = new RegExp('^([a-z0-9]+(-|_))*[a-z0-9]+$');

	// Handle default form.
	if ($('#awsm-job-form-builder-container-default').length > 0) {
		$('#delete-action').remove();
	}

	$('#awsm-jobs-form-builder').sortable({
		items: '.awsm-jobs-form-element-main',
		axis: 'y',
		handle: '.awsm-jobs-form-element-head',
		cursor: 'grabbing'
	});

	$fbOptionsWrapper.on('click', '.awsm-jobs-form-element-head-title', function() {
		$(this).parents('.awsm-jobs-form-element-main').toggleClass('open');
	});

	$fbOptionsWrapper.on('click', '.awsm-jobs-form-element-close', function() {
		$(this).parents('.awsm-jobs-form-element-main').removeClass('open');
	});

	$fbOptionsWrapper.parents('#post').on('submit', function(e) {
		if ($fbOptionsWrapper.is(':visible')) {
			var isValid = true;
			var $fbFooter = $fbOptionsWrapper.find('.awsm-jobs-form-builder-footer');

			// Remove the active errors before checking.
			$('.awsm-jobs-error-container').remove();

			// Validate unique fields.
			var uniqueFields = { resume: [], photo: [] };
			$('.awsm-builder-field-select-control').each(function(index) {
				var fieldType = $(this).val();
				if (fieldType === 'resume' || fieldType === 'photo') {
					uniqueFields[fieldType].push(index);
				}
			});
			var errorTemplate = wp.template('awsm-pro-fb-error');
			if (uniqueFields.resume.length > 1 || uniqueFields.photo.length > 1) {
				isValid = false;

				var error = '';
				if (uniqueFields.resume.length > 1) {
					error += errorTemplate({ isFieldType: true, fieldType: 'resume' });
				}
				if (uniqueFields.photo.length > 1) {
					error += errorTemplate({ isFieldType: true, fieldType: 'photo' });
				}
				$fbFooter.append(error);
			}

			// Validate template tags.
			$('.awsm-jobs-form-builder-template-tag').each(function() {
				var tmplKey = $(this).val();
				if (tmplKey.length > 0 && ! tmplTagfieldRegEx.test(tmplKey)) {
					isValid = false;

					var templateData = { invalidKey: true };
					$fbFooter.append(errorTemplate(templateData));
				}
			});
			if (! isValid) {
				e.preventDefault();
				$('html, body').animate({
					scrollTop: $fbFooter.offset().top
				}, 600);
			}
		}
	});

	$fbOptionsWrapper.on('click', '.awsm-add-form-field-row', function(e) {
		e.preventDefault();
		var $wrapper = $('#awsm-jobs-form-builder');
		var next = $wrapper.data('next');
		var fbTemplate = wp.template('awsm-pro-fb-settings');
		var templateData = { index: next };
		$wrapper.data('next', next + 1);
		$wrapper.append(fbTemplate(templateData));
	});

	$fbOptionsWrapper.on('change', '.awsm-builder-field-select-control', function() {
		var $elem = $(this);
		var $fieldGroup = $elem.parents('.awsm-jobs-form-element-content');
		var optionValue = $elem.val();
		var index = $elem.data('index');
		var isRepeater = $elem.find(':selected').data('repeater');
		index = typeof index !== 'undefined' ? index : 0;
		var optionsTemplate = null;
		var templateData = {};

		// Handle Form Builder field other options.
		var $target = $elem.parents('.awsm-jobs-form-builder-type-wrapper').find('.awsm-job-fb-options-container');
		if (optionValue === 'select' || optionValue === 'checkbox' || optionValue === 'radio') {
			optionsTemplate = wp.template('awsm-pro-fb-field-options');
			templateData = { index: index };
			$target.html(optionsTemplate(templateData));
			$target.removeClass('hidden');
		} else if (optionValue === 'file' || optionValue === 'photo' || optionValue === 'resume') {
			optionsTemplate = wp.template('awsm-pro-fb-file-options');
			templateData = {
				index: index,
				fieldType: optionValue
			};
			$target.html(optionsTemplate(templateData));
			$target.removeClass('hidden');
		} else if (optionValue === 'section') {
			optionsTemplate = wp.template('awsm-pro-fb-section-field-options');
			templateData = {
				index: index,
				fieldType: optionValue
			};
			$target.html(optionsTemplate(templateData));
			$target.removeClass('hidden');
		} else if (optionValue === 'tel') {
			optionsTemplate = wp.template('awsm-pro-fb-iti-options');
			templateData = {
				index: index,
				fieldType: optionValue
			};
			$target.html(optionsTemplate(templateData));
			$target.removeClass('hidden');
		} else {
			$target.html('');
			$target.addClass('hidden');
		}

		// Handle label and required settings.
		if (optionValue === 'section' || isRepeater === true) {
			if (optionValue === 'section') {
				$fieldGroup.find('.awsm-job-fb-label-wrapper').addClass('hidden').find('input').prop('required', false);
			} else {
				$fieldGroup.find('.awsm-job-fb-label-wrapper').removeClass('hidden').find('input').prop('required', true);
			}
			$fieldGroup.find('.awsm-job-fb-required-wrapper').addClass('hidden');
		} else {
			$fieldGroup.find('.awsm-job-fb-label-wrapper').removeClass('hidden').find('input').prop('required', true);
			$fieldGroup.find('.awsm-job-fb-required-wrapper').removeClass('hidden');
		}

		// Handle Template Tag.
		$target = $fieldGroup.find('.awsm-job-fb-template-key');
		if (optionValue === 'resume' || optionValue === 'photo' || optionValue === 'file' || optionValue === 'section') {
			$target.html('');
			$target.addClass('hidden');
		} else {
			optionsTemplate = wp.template('awsm-pro-fb-template-tag');
			templateData = { index: index };
			$target.html(optionsTemplate(templateData));
			$target.removeClass('hidden');
		}

		// Handle Placeholder.
		var $placeholderOption = $fieldGroup.find('.awsm-job-fb-placeholder-option');
		if (optionValue === 'text' || optionValue === 'email' || optionValue === 'number' || optionValue === 'tel' || optionValue === 'textarea') {
			optionsTemplate = wp.template('awsm-pro-fb-placeholder-field-options');
			templateData = { index: index };
			$placeholderOption.html(optionsTemplate(templateData));
			$placeholderOption.removeClass('hidden');
		} else {
			$placeholderOption.html('');
			$placeholderOption.addClass('hidden');
		}
	});

	$fbOptionsWrapper.on('change', '.awsm-jobs-form-builder-iti-control', function() {
		var $elem = $(this);
		var $defaultWrapper = $elem.parent('p').next('.awsm-jobs-form-builder-iti-default-wrapper');
		if ($elem.is(':checked')) {
			$defaultWrapper.removeClass('hidden');
		} else {
			$defaultWrapper.addClass('hidden');
		}
	});

	$fbOptionsWrapper.on('click', '.awsm-form-field-remove-row', function(e) {
		e.preventDefault();
		var $deleteBtn = $(this);
		$deleteBtn.parents('.awsm-jobs-form-element-main').remove();
	});

	$fbOptionsWrapper.on('keyup blur', '.awsm_jobs_form_builder_new_label', function() {
		var $element = $(this);
		var title = $element.val();
		var $row = $element.parents('.awsm-jobs-form-element-content');
		if (title.length > 0) {
			title = $.trim(title).replace(/\s+/g, '-').toLowerCase();
			if (tmplTagfieldRegEx.test(title)) {
				$row.find('.awsm-jobs-form-builder-template-tag').val(title);
			}
		}
	});

	// Handle form submission errors.
	$('.post-type-awsm_job_form #publishing-action input[type="submit"]').on('click', function() {
		var $form = $('form#post');
		if ($form.get(0).checkValidity() === false) {
			$('.awsm-jobs-form-builder-error').removeClass('awsm-hidden');
			$form.find('.awsm-jobs-form-element-main').addClass('open');
		} else {
			$('.awsm-jobs-form-builder-error').addClass('awsm-hidden');
		}
	});

	// Handle multiple forms settings.
	var $multiFormsOptionsWrapper = $('#awsm-builder-form-options-container');

	$multiFormsOptionsWrapper.on('click', '.awsm-jobs-forms-list-actions .awsm-jobs-forms-list-duplicate-action', function(e) {
		e.preventDefault();
		var $listWrapper = $(this).parents('tr');
		var $formTable = $listWrapper.parents('.awsm-jobs-forms-list-table');
		var formId = $listWrapper.data('id');
		if (! $formTable.hasClass('awsm-jobs-fb-actions-in-progress')) {
			$formTable.addClass('awsm-jobs-fb-actions-in-progress');
			var wpData = [
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
				{ name: 'awsm_form_id', value: formId },
				{ name: 'awsm_fb_action', value: 'duplicate' },
				{ name: 'action', value: 'awsm_jobs_form_builder_actions' }
			];
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				data: $.param(wpData),
				dataType: 'json'
			})
				.done(function(response) {
					if (response && 'duplicate' in response && 'data' in response) {
						var formData = response.data;
						var fbActionsTemplate = wp.template('awsm-pro-fb-actions');
						var templateData = {
							id: formData.id,
							title: formData.title,
							status: formData.status,
							statusText: formData.status_text,
							editLink: formData.edit_link,
							fieldsCount: formData.fields_count
						};
						$listWrapper.after(fbActionsTemplate(templateData));
					}
				}).always(function() {
					$formTable.removeClass('awsm-jobs-fb-actions-in-progress');
				});
		}
	});

	$multiFormsOptionsWrapper.on('click', '.awsm-jobs-forms-list-actions .awsm-jobs-forms-list-delete-action', function(e) {
		e.preventDefault();
		var $listWrapper = $(this).parents('tr');
		var $formTable = $listWrapper.parents('.awsm-jobs-forms-list-table');
		var formId = $listWrapper.data('id');
		var confirm = window.confirm(awsmProJobsAdmin.i18n.delete_confirmation);
		if (confirm) {
			$formTable.addClass('awsm-jobs-fb-actions-in-progress');
			var wpData = [
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
				{ name: 'awsm_form_id', value: formId },
				{ name: 'awsm_fb_action', value: 'delete' },
				{ name: 'action', value: 'awsm_jobs_form_builder_actions' }
			];
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				data: $.param(wpData),
				dataType: 'json'
			})
				.done(function(response) {
					if (response && 'delete' in response && response.delete) {
						$listWrapper.remove();
					}
				}).always(function() {
					$formTable.removeClass('awsm-jobs-fb-actions-in-progress');
				});
		}
	});

	$('#settings-awsm-settings-form').on('click', '.awsm-nav-subtab', function(e) {
		e.preventDefault();
		var $currentSubtab = $(this);
		var currentSubtabId = $currentSubtab.attr('id');
		if (currentSubtabId === 'awsm-builder-form-nav-subtab') {
			$(e.delegateTarget).find('.awsm-form-footer input[type="submit"]').addClass('awsm-hidden');
		} else {
			$(e.delegateTarget).find('.awsm-form-footer input[type="submit"]').removeClass('awsm-hidden');
		}
	});

	/*================ Form Notifications: Switch ================*/

	$('.awsm-form-notifications-switch').on('change', function() {
		var $control = $(this);
		var option = $control.data('metakey');
		var optionValue = $control.val();
		var formId = $control.data('formid');
		if (! $control.is(':checked')) {
			optionValue = '';
		}
		var optionsData = {
			action: 'form_notifications_switch',
			nonce: awsmJobsAdmin.nonce,
			option: option,
			'option_value': optionValue,
			'form_id': formId
		};
		$.ajax({
			url: awsmJobsAdmin.ajaxurl,
			data: optionsData,
			type: 'POST'
		}).fail(function(xhr) {
			// eslint-disable-next-line no-console
			console.log(xhr);
		});
	});

	/*================ Mail Handling ================*/

	var $msgContainer = $('.awsm-applicant-mail-message');
	var successClass = 'awsm-success-message';
	var errorClass = 'awsm-error-message';

	function getApplicantMailEditor() {
		var editor = null;
		if (typeof tinyMCE !== 'undefined') {
			editor = tinyMCE.get('awsm_mail_meta_applicant_content');
			if (editor && editor.isHidden()) {
				editor = null;
			}
		}
		return editor;
	}

	$('#awsm_mail_meta_applicant_template').on('change', function() {
		var templateKey = $(this).val();
		if (typeof templateKey !== 'undefined' && templateKey.length > 0) {
			$msgContainer.hide();
			var wpData = [
				{ name: 'awsm_application_id', value: applicationId },
				{ name: 'awsm_template_key', value: templateKey },
				{ name: 'action', value: 'awsm_job_et_data' }
			];
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'GET',
				data: $.param(wpData),
				dataType: 'json'
			}).done(function(data) {
				if (data) {
					$('#awsm_mail_meta_applicant_subject').val(data.subject);
					var mailEditor = getApplicantMailEditor();
					if (mailEditor !== null) {
						mailEditor.setContent(data.content);
					} else {
						$('#awsm_mail_meta_applicant_content').val(data.content);
					}
				}
			});
		}
	});

	$('#awsm-applicant-meta-new-mail').on('click', '#awsm_applicant_mail_btn', function(e) {
		e.preventDefault();

		var $errorFields = $('.awsm-form-control.error');
		if ($errorFields.length === 0) {
			$msgContainer.removeClass(successClass + ' ' + errorClass).hide();
			var fieldSelector = '.awsm-applicant-mail-field';
			var $submitBtn = $(this);
			var submitBtnText = $submitBtn.text();
			var submitBtnResText = $submitBtn.data('responseText');
			$submitBtn.prop('disabled', true).text(submitBtnResText);
			var wpData = $('#awsm-applicant-meta-new-mail').find(fieldSelector).serializeArray();
			var mailEditor = getApplicantMailEditor();
			var mailContent = '';
			if (mailEditor !== null) {
				mailContent = mailEditor.getContent();
			} else {
				mailContent = $('#awsm_mail_meta_applicant_content').val();
			}
			wpData.push(
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
				{ name: 'awsm_application_id', value: applicationId },
				{ name: 'awsm_mail_meta_applicant_content', value: mailContent },
				{ name: 'action', value: 'awsm_applicant_mail' }
			);
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				data: $.param(wpData),
				dataType: 'json'
			})
				.done(function(response) {
					if (response) {
						var className = 'awsm-default-message';
						var msg = '';
						var msgArray = [];
						if (response.error.length > 0) {
							className = errorClass;
							msgArray = response.error;
						} else {
							if (response.success.length > 0) {
								$('#awsm_jobs_no_mail_wrapper').remove();
								className = successClass;
								msgArray = response.success;
								$('#awsm_mail_meta_applicant_template').val(null).trigger('change');
								$('#awsm_mail_meta_applicant_subject').val('');
								$('#awsm_mail_meta_applicant_content').val('');
								if (mailEditor !== null ) {
									mailEditor.setContent('');
								}
								var mailData = response.content;
								var mailTemplate = wp.template('awsm-pro-applicant-mail');
								var templateData = {
									author: mailData.author,
									'date_i18n': mailData.date_i18n,
									subject: mailData.subject,
									content: mailData.content
								};
								$('#awsm-jobs-applicant-mails-container').prepend(mailTemplate(templateData));
							}
						}
						$(msgArray).each(function(index, value) {
							msg += '<p>' + value + '</p>';
						});
						$msgContainer.addClass(className).html(msg).fadeIn();
					}
				})
				.always(function() {
					$submitBtn.prop('disabled', false).text(submitBtnText);
				});
		} else {
			$errorFields.next('label.error').addClass('awsm-job-form-error');
		}
	});

	$('.awsm-add-mail-templates').on('click', function(e) {
		e.preventDefault();
		var $wrapper = $('#awsm-repeatable-mail-templates');
		var next = $wrapper.data('next');
		$wrapper.find('.awsm-acc-head').removeClass('on');
		$wrapper.find('.awsm-acc-content').slideUp('normal');
		var template = wp.template('awsm-pro-notification-settings');
		var templateData = { index: next };
		$wrapper.find('.awsm-mail-templates-acc-section').append(template(templateData));
		$wrapper.find('[data-required="required"]').prop('required', true);
		$wrapper.data('next', next + 1);
		if ('wp_editor_settings' in awsmProJobsAdmin) {
			var defaultSettings = wp.editor.getDefaultSettings();
			var tinymceSettings = $.extend({}, defaultSettings.tinymce, awsmProJobsAdmin.wp_editor_settings.tinymce);
			wp.editor.initialize('awsm-jobs-pro-mail-content-' + next, {
				mediaButtons: true,
				tinymce: tinymceSettings,
				quicktags: awsmProJobsAdmin.wp_editor_settings.quicktags
			});
		}
	});

	$('#awsm-repeatable-mail-templates').on('click', '.awsm-remove-mail-template', function(e) {
		e.preventDefault();
		var $deleteBtn = $(this);
		$deleteBtn.parents('.awsm-acc-main').remove();
	});

	$('#awsm-repeatable-mail-templates').on('keyup blur', '.awsm-jobs-pro-mail-template-name', function() {
		var $nameControl = $(this);
		var templateName = $nameControl.val();
		var $header = $nameControl.parents('.awsm-acc-main');
		var $titleElem = $header.find('.awsm-jobs-pro-mail-template-title');
		var title = templateName.length > 0 ? templateName : $titleElem.text();
		$titleElem.text(title);
		$header.find('.awsm-jobs-pro-mail-template-subtitle').fadeIn();
	});

	/*================ Advanced: Manage Application Status ================*/

	var tlData = { 'а': 'a', 'А': 'a', 'б': 'b', 'Б': 'B', 'в': 'v', 'В': 'V', 'ґ': 'g', 'г': 'g', 'Г': 'G', 'д': 'd', 'Д': 'D', 'е': 'e', 'Е': 'E', 'є': 'ye', 'э': 'e', 'Э': 'E', 'и': 'i', 'і': 'i', 'ї': 'yi', 'й': 'i', 'И': 'I', 'Й': 'I', 'к': 'k', 'К': 'K', 'л': 'l', 'Л': 'L', 'м': 'm', 'М': 'M', 'н': 'n', 'Н': 'N', 'о': 'o', 'О': 'O', 'п': 'p', 'П': 'P', 'р': 'r', 'Р': 'R', 'с': 's', 'С': 'S', 'т': 't', 'Т': 'T', 'у': 'u', 'У': 'U', 'ф': 'f', 'Ф': 'F', 'х': 'h', 'Х': 'H', 'ц': 'c', 'ч': 'ch', 'Ч': 'CH', 'ш': 'sh', 'Ш': 'SH', 'щ': 'sch', 'Щ': 'SCH', 'ж': 'zh', 'Ж': 'ZH', 'з': 'z', 'З': 'Z', 'Ъ': '\'', 'ь': '\'', 'ъ': '\'', 'Ь': '\'', 'ы': 'i', 'Ы': 'I', 'ю': 'yu', 'Ю': 'YU', 'я': 'ya', 'Я': 'Ya', 'ё': 'yo', 'Ё': 'YO', 'Ц': 'TS' };

	function transliterate(text) {
		var chars = text.split('');
		return chars.map(function(char) {
			return (char in tlData) ? tlData[char] : char;
		}).join('');
	}

	$('#settings-awsm-settings-manage-status').sortable({
		items: '.awsm-acc-main-sortable-item',
		axis: 'y',
		handle: '.awsm-acc-drag-control',
		cursor: 'grabbing'
	});

	$('#settings-awsm-settings-manage-status').on('click', '.awsm-add-application-status', function(e) {
		e.preventDefault();
		var $wrapper = $('#awsm-repeatable-application-status');
		var next = $wrapper.data('next');
		$wrapper.find('.awsm-acc-head').removeClass('on');
		$wrapper.find('.awsm-acc-content').slideUp('normal');
		var template = wp.template('awsm-manage-application-status-settings');
		var templateData = { index: next };
		$wrapper.find('.awsm-application-status-acc-section').append(template(templateData));
		$('#awsm-jobs-manage-application-status-key-' + next).parents('.awsm-row').find('.awsm-jobs-manage-application-status-key').addClass('awsm-jobs-manage-application-status-key-new');
		$wrapper.data('next', next + 1);
		$wrapper.find('.awsm-acc-main').last().find('.awsm-jobs-colorpicker-field').wpColorPicker();
	});

	$('#awsm-repeatable-application-status').on('change', '.awsm-jobs-manage-application-mail-on-status', function() {
		var $control = $(this);
		var $templateControlGroup = $control.parents('.awsm-acc-content').find('.awsm-jobs-status-mail-template-group');
		if (! $control.is(':checked')) {
			$templateControlGroup.addClass('awsm-hide');
			$templateControlGroup.find('.awsm-jobs-manage-application-mail-template').prop('required', false);
		} else {
			$templateControlGroup.removeClass('awsm-hide');
			$templateControlGroup.find('.awsm-jobs-manage-application-mail-template').prop('required', true);
		}
	});

	function updateStatusKey($elem, isDefault) {
		isDefault = typeof isDefault !== 'undefined' ? isDefault : true;
		var key = $elem.val();
		var $row = $elem.parents('.awsm-row');
		if (key.length > 0) {
			var keyLength = isDefault ? 15 : 20;
			key = key.trim().replace(/[\s-]+/g, '-').toLowerCase();
			key = transliterate(key).substring(0, keyLength);
			if (isDefault) {
				key = 'appl-' + key;
			}
			if (tmplTagfieldRegEx.test(key)) {
				$row.find('.awsm-jobs-manage-application-status-key-new').val(key);
			}
		}
	}

	$('.awsm-job-settings-advaced-container').on('keyup blur', '.awsm-jobs-manage-application-status-label', function() {
		updateStatusKey($(this));
	});

	$('.awsm-job-settings-advaced-container').on('keyup blur', '.awsm-jobs-manage-application-status-key-new', function() {
		updateStatusKey($(this), false);
	});

	/*================ Application Notes ================*/

	function renderApplicationNotes(note) {
		note = $.trim(note);
		if (note.length > 0) {
			var wpData = [
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
				{ name: 'awsm_application_id', value: applicationId },
				{ name: 'awsm_application_notes', value: note },
				{ name: 'action', value: 'awsm_job_pro_notes' }
			];
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				data: $.param(wpData),
				beforeSend: function() {
					$('#awsm_application_notes').prop('disabled', true);
					$('.awsm-jobs-application-notes-list').addClass('awsm-jobs-loading');
				},
				dataType: 'json'
			})
				.done(function(response) {
					if (response) {
						if (response.update === true) {
							var notesData = response.notes_data;
							var notesTemplate = wp.template('awsm-pro-notes');
							var templateData = {
								index: notesData.index,
								time: notesData.time,
								'date_i18n': notesData.date_i18n,
								author: notesData.username,
								content: note
							};
							$('.awsm-jobs-application-notes-list').prepend(notesTemplate(templateData));
							$('#awsm_application_notes').val('');
						}
					}
				})
				.always(function() {
					$('.awsm-jobs-application-notes-list').removeClass('awsm-jobs-loading');
					$('#awsm_application_notes').prop('disabled', false);
				});
		}
	}

	$('#awsm_application_notes').on('keypress', function(e) {
		if (e.which == 13) {
			e.preventDefault();
			var note = $(this).val();
			renderApplicationNotes(note);
		}
	});

	var isRemovable = true;
	$('.awsm-jobs-application-notes-list').on('click', '.awsm-jobs-note-remove-btn', function(e) {
		e.preventDefault();
		var $noteList = $(this).parents('li.awsm-jobs-note');
		var index = $noteList.data('index');
		var time = $noteList.data('time');
		if (isRemovable) {
			isRemovable = false;
			var wpData = [
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
				{ name: 'awsm_application_id', value: applicationId },
				{ name: 'awsm_note_key', value: index },
				{ name: 'awsm_note_time', value: time },
				{ name: 'action', value: 'awsm_job_pro_remove_note' }
			];
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				data: $.param(wpData),
				beforeSend: function() {
					$('.awsm-jobs-application-notes-list').addClass('awsm-jobs-loading');
				},
				dataType: 'json'
			})
				.done(function(response) {
					if (response) {
						if (response.delete === true) {
							$noteList.slideUp(function() {
								$(this).remove();
								var $lists = $('li.awsm-jobs-note');
								var notesCount = $lists.length;
								$lists.each(function(i) {
									var index = notesCount - (i + 1);
									$(this).data('index', index);
									$(this).attr('data-index', index);
								});
								isRemovable = true;
							});
						}
					}
				})
				.fail(function() {
					isRemovable = true;
				})
				.always(function() {
					$('.awsm-jobs-application-notes-list').removeClass('awsm-jobs-loading');
				});
		}
	});

	/*================ Application Export ================*/

	var $exportError = $('.awsm-jobs-export-wrapper .awsm-jobs-error-container');
	if ( $exportError.length > 0 ) {
		var exportURL = $('#awsm-jobs-export-applications form').attr('action');
		window.history.replaceState({}, '', exportURL);
		$exportError.delay(2000).fadeOut('slow', function() {
			$exportError.remove();
		});
	}

	$('.awsm-application-export-by-toggle-control').on('change', function() {
		var exportBy = $(this).val();
		$('.awsm-application-export-by-row').addClass('awsm-hide');
		$('.awsm-application-export-by-' + exportBy + '-row').removeClass('awsm-hide');
		if (exportBy === 'application-form') {
			$('#awsm-jobs-export-applications #awsm-job-id').prop('required', false);
		} else {
			$('#awsm-jobs-export-applications #awsm-job-id').prop('required', true);
		}
	});

	$('.awsm-jobs-export-wrapper .awsm-jobs-export-button').on('click', function(e) {
		var $form = $(this).parents('form');
		var $error = $form.find('.awsm-jobs-error-container');
		if ($error.length > 0) {
			e.preventDefault();
			$form.find('.awsm-jobs-error-container').fadeOut('slow');
			var url = $form.attr('action');
			window.history.replaceState({}, '', url);
			$form.get(0).submit();
		}
	});

	var currentDate = $('.awsm-jobs-export-wrapper input[name="awsm_date_from"]').first().attr('max');
	$('.awsm-jobs-export-wrapper').on('change', 'input[name="awsm_date_to"]', function(e) {
		var dateTo = $(this).val();
		var time = new Date(dateTo).getTime();
		var $dateFrom = $(e.delegateTarget).find('input[name="awsm_date_from"]');
		if (dateTo.length > 0) {
			$dateFrom.prop('required', true);
		} else {
			$dateFrom.prop('required', false);
		}

		// Set the max attribute for the date from field.
		if (! isNaN(time)) {
			$dateFrom.attr('max', dateTo);
		} else {
			$dateFrom.attr('max', currentDate);
		}
	});

	$('.awsm-jobs-export-wrapper').on('change', 'input[name="awsm_date_from"]', function(e) {
		var dateFrom = $(this).val();
		var time = new Date(dateFrom).getTime();
		var $dateTo = $(e.delegateTarget).find('input[name="awsm_date_to"]');
		if (dateFrom.length > 0) {
			$dateTo.prop('required', true);
		} else {
			$dateTo.prop('required', false);
		}

		// Set the max attribute for the date to field.
		if (! isNaN(time)) {
			$dateTo.attr('min', dateFrom);
		} else {
			$dateTo.removeAttr('min');
		}
	});

	/*================ Shortcode Generator ================*/

	function getSubTabId($currentTab) {
		return $currentTab.text().trim().toLowerCase().replace(/\s+/g, '_');
	}

	$('.awsm-shortcodes-filters-select-control').awsmSelect2({
		tags: true,
		tokenSeparators: [ ',' ],
		theme: 'awsm-job',
		dropdownCssClass: 'awsm-select2-dropdown-control',
		createTag: function() {
			return null;
		}
	});

	$('#settings-awsm-settings-shortcodes').on('change', '.awsm-shortcodes-filter-item .awsm-check-toggle-control', function() {
		var specsTotal = $('.awsm-shortcodes-filter-item .awsm-check-toggle-control').length;
		var activeSpecsCount = $('.awsm-shortcodes-filter-item .awsm-check-toggle-control:checked').length;
		if (activeSpecsCount > 0 && activeSpecsCount < specsTotal) {
			$('#awsm-jobs-other-filters-container').removeClass('awsm-hide');
		} else {
			$('#awsm-jobs-other-filters-container').addClass('awsm-hide');
			$('#awsm_jobs_enable_other_filters').prop('checked', false);
		}
	});

	$('#settings-awsm-settings-shortcodes').on('change', '.awsm-shortcodes-job-listing-control', function() {
		var $target = $('#awsm-jobs-enable-filters-container');
		if ($('#awsm_jobs_listing_all').is(':checked')) {
			$target.removeClass('awsm-hide');
		} else {
			$target.addClass('awsm-hide');
		}
	});

	$('#settings-awsm-settings-shortcodes').on('click', '.awsm-nav-subtab', function() {
		$('.awsm-settings-shortcodes-aside').addClass('awsm-hide');
		$('#awsm-copy-clip').addClass('awsm-hide');
 
		/* var $currentTab = $(this);
		var targetTabId = $currentTab.data('target');
		var $targetTable = $(targetTabId);
		var prevVal = '';
		if ($targetTable.length > 0) {
			var table = $targetTable.find('[data-section="' + $currentTab.get(0).id + '"]');
			var type = table.data('type');
			var shortcodeContent = '[' + type + ']'; 
			prevVal = $('#awsmp-jobs-' + getSubTabId($currentTab) + '-prev-value').val(); console.log('test');
			if (prevVal !== '') {
				shortcodeContent = prevVal; 
			}
		}
		$('.awsm-settings-shortcodes-wrapper code').text(shortcodeContent);
		$('#awsm-copy-clip'). attr('data-clipboard-text', shortcodeContent);  */
	});

	$('#settings-awsm-settings-shortcodes').on('change', '#awsm_jobs_orderby', function() {
		var orderBy = $(this).val();
		if (orderBy === 'rand') {
			$('.awsm-shortcodes-job-order-control').parents('tr').addClass('awsm-hide');
		} else {
			$('.awsm-shortcodes-job-order-control').parents('tr').removeClass('awsm-hide');
		}
	});

	function generateShortcode() {
		var $currentTab = $('#settings-awsm-settings-shortcodes').find('.awsm-nav-subtab.current');
		var targetTabId = $currentTab.data('target');
		var $targetTable = $(targetTabId);
		if ($targetTable.length > 0) {
			var subTabId = getSubTabId($currentTab);
			var table = $targetTable.find('[data-section="' + $currentTab.get(0).id + '"]');
			var type = table.data('type');
			var shortcodeContent = '[' + type; 

			if (type === 'awsm_application_form' || type === 'awsmjob_specs') {
				var jobId = $('#awsm-jobs-' + subTabId + '-shortcodes-container .awsm-shortcodes-select-job-control').val();
				
				if( jobId !== '' ){
					$('.awsm-settings-shortcodes-aside').removeClass('awsm-hide');
					$('#awsm-copy-clip').removeClass('awsm-hide');
				}else{
					$('.awsm-settings-shortcodes-aside').addClass('awsm-hide');
					$('#awsm-copy-clip').addClass('awsm-hide');
				} 

				shortcodeContent += typeof jobId !== 'undefined' && jobId ? ' id="' + jobId + '"' : '';
			} else if (type === 'awsmjobs_stats') {
				var jobStatus = $('.awsm-shortcodes-jobs-stats-control:checked').val();

				if( jobStatus != '' ){
					$('.awsm-settings-shortcodes-aside').removeClass('awsm-hide');
					$('#awsm-copy-clip').removeClass('awsm-hide');
				}else{
					$('.awsm-settings-shortcodes-aside').addClass('awsm-hide');
					$('#awsm-copy-clip').addClass('awsm-hide');
				}

				shortcodeContent += typeof jobStatus !== 'undefined' && jobStatus && jobStatus !== 'default' ? ' status="' + jobStatus + '"' : '';
			} else {

				$('.awsm-settings-shortcodes-aside').removeClass('awsm-hide');
				$('#awsm-copy-clip').removeClass('awsm-hide');

				var enableFilter = $('#awsm_jobs_enable_filters:checked').val();
				var listings = $('#awsm_jobs_listings').val();
				var status = $('.awsm-shortcodes-job-status-control:checked').val();
				var orderBy = $('#awsm_jobs_orderby').val();
				var order = $('.awsm-shortcodes-job-order-control:checked').val();
				var pagination = $('#awsm_jobs_pagination:checked').val();
				var isFilteredListing = $('#awsm_jobs_listing_filtered').is(':checked');

				if (isFilteredListing) {
					if ($('#awsm_jobs_enable_other_filters').is(':checked')) {
						shortcodeContent += ' filters="partial"';
					}
				} else {
					shortcodeContent += typeof enableFilter === 'undefined' || enableFilter !== 'yes' ? ' filters="no"' : '';
				}
				shortcodeContent +=
					typeof listings !== 'undefined' && parseInt(listings, 10) > 0 ?
						' listings="' + parseInt(listings, 10) + '"' :
						'';
				shortcodeContent +=
					typeof status !== 'undefined' && status !== 'default' ?
						' status="' + status + '"' :
						'';
				shortcodeContent +=
					typeof orderBy !== 'undefined' && orderBy !== 'date' ?
						' orderby="' + orderBy + '"' :
						'';
				shortcodeContent +=
					typeof order !== 'undefined' && order !== 'DESC' ?
						' order="' + order + '"' :
						'';
				shortcodeContent += typeof pagination === 'undefined' || pagination !== 'yes' ? ' loadmore="no"' : '';
				if (isFilteredListing) {
					var attrs = [];
					$('.awsm-shortcodes-filters-select-control').each(function() {
						var $elem = $(this);
						var value = $elem.val();
						var filter = $elem.data('filter');
						var enableSpec = $elem
							.parents('.awsm-shortcodes-filter-item')
							.find('.awsm-check-toggle-control:checked')
							.val();
						if (value !== null && typeof filter !== 'undefined' && enableSpec === 'yes') {
							attrs.push(filter + ':' + value.join(' '));
						}
					});
					if (attrs.length > 0) {
						shortcodeContent += ' specs="' + attrs.join(',') + '"';
					}
				}
			}
		}
		shortcodeContent += ']';
		$('#awsmp-jobs-' + subTabId + '-prev-value').val(shortcodeContent);

		$('.awsm-settings-shortcodes-wrapper code').text(shortcodeContent);
		$('#awsm-copy-clip').attr('data-clipboard-text', shortcodeContent);
	}

	$('#settings-awsm-settings-shortcodes').on('click', '#awsm-jobs-generate-shortcode', generateShortcode );

	/*================ Custom Application Form ================*/

	$('.awsm-pro-application-form-section').on('change', '#awsm-pro-application-form', function(e) {
		e.preventDefault();
		var selectValue = $(this).val();
		var $wrapper = $(e.delegateTarget);
		$wrapper.find('.awsm-pro-application-form-option :input').val('').prop('required', false);
		$wrapper.find('.awsm-wpjo-form-group').removeClass('awsm-last-visible-group');
		$wrapper.find('.awsm-pro-application-form-option').parent('.awsm-wpjo-form-group').addClass('awsm-hide');
		if (selectValue === 'custom_form' || selectValue === 'custom_button') {
			var $optionWrapper = $wrapper.find('.awsm-pro-application-form-option-' + selectValue);
			$optionWrapper.find(':input').prop('required', true);
			$optionWrapper.parent('.awsm-wpjo-form-group').removeClass('awsm-hide');
		}
		$wrapper.find('.awsm-wpjo-form-group:visible:last').addClass('awsm-last-visible-group');
	});

	/*================ Submit Form Confirmation ================*/

	$('.awsm-pro-form-submit-confirmation-section').on('change', '#awsm-pro-form-submit-confirmation-type', function(e) {
		e.preventDefault();
		var optionValue = $(this).val();
		$('.awsm-form-submit-confirmation-option').addClass('awsm-hide').find('input,textarea,select').attr('required', false);
		$('#awsm-form-submit-confirmation-option-' + optionValue).removeClass('awsm-hide').find('input,textarea,select').attr('required', true);
	});

	/*================ Print Application ================*/

	$('.awsm-applicant-print-control').click(function(e) {
		e.preventDefault();
		var $elem = $(this);
		$elem.prop('disabled', true);
		$('.awsm-applicant-print-message').addClass('awsm-hidden');
		var action = $(this).data('action');
		var applicationId = $(this).data('application');
		var wpData = [
			{ name: 'nonce', value: awsmProJobsAdmin.nonce },
			{ name: 'awsm_application_id', value: applicationId },
			{ name: 'action', value: 'awsm_job_print_data' }
		];
		$.ajax({
			url: awsmJobsAdmin.ajaxurl,
			type: 'POST',
			data: $.param(wpData),
			dataType: 'json'
		}).done(function(data) {
			if ('error' in data) {
				$('.awsm-applicant-print-message').removeClass('awsm-hidden');
			} else {
				var logo = data.options.logo;
				var font = data.options.font;
				var titleFontSize = data.options.title_font_size;
				var fontSize = data.options.font_size;
				var titleColor = data.options.title_color;
				var subtitleColor = data.options.subtitle_color;
				var textColor = data.options.text_color;
				var showLines = data.options.show_lines;
				var lineColor = data.options.line_color;
				var lineWidth = data.options.line_width;
				// eslint-disable-next-line no-inner-declarations
				function stars(type, per, fn) {
					var estar;
					if (type === 0) {
						estar = '<svg width="21" height="19" viewBox="0 0 21 19" xmlns="http://www.w3.org/2000/svg" version="1.1" preserveAspectRatio="xMinYMin"><path fill="#FFFFFF" fill-rule="evenodd" stroke="#BDC4CC" d="M94.5,1.12977573 L91.7461602,6.70966466 L85.5883871,7.60444146 L90.0441936,11.9477793 L88.9923204,18.0806707 L94.5,15.1851121 L100.00768,18.0806707 L98.9558064,11.9477793 L103.411613,7.60444146 L97.2538398,6.70966466 L94.5,1.12977573 Z" transform="translate(-84)"></path></svg>';
					} else {
						estar = '<svg width="21" height="19" viewBox="0 0 21 19" xmlns="http://www.w3.org/2000/svg" version="1.1" preserveAspectRatio="xMinYMin"><path fill="#FFCD00" fill-rule="evenodd" d="M52.5 15.75L46.328 18.995 47.507 12.122 42.514 7.255 49.414 6.253 52.5 0 55.586 6.253 62.486 7.255 57.493 12.122 58.672 18.995z" transform="translate(-42)"></path></svg>';
					}
					var esvg = new Blob([ estar ], { type: 'image/svg+xml' });
					var DOMURL = self.URL || self.webkitURL || self;
					var eurl = DOMURL.createObjectURL(esvg);
					var eimg = new Image();
					eimg.onload = function() {
						var ecs = document.createElement('canvas');
						ecs.width = ((eimg.width + 2) * 5 - 6) * per / 100;
						ecs.height = eimg.height + 4;
						var ectx = ecs.getContext('2d');
						for (var i = 0; i < 5; i++) {
							ectx.drawImage(eimg, 2 + i * eimg.width, 2);
						}
						var epng = ecs.toDataURL('image/png');
						if (typeof fn == 'function') {
							fn(epng);
						}
						DOMURL.revokeObjectURL(eurl);
					};
					eimg.src = eurl;
				}

				window.jsPDF = window.jspdf.jsPDF;
				var doc = new jsPDF();

				var applicantPhoto = false;
				// eslint-disable-next-line no-inner-declarations
				function savePDF(doc) {
					var pageSize = doc.internal.pageSize.height;
					var groups = [];
					for (var ij in data.label) {
						if (
							ij != 'notes' &&
							ij != 'awsm_application_id' &&
							ij != 'awsm_application_date' &&
							ij != 'application_status' &&
							ij != 'awsm_apply_for' &&
							ij != 'rating' &&
							ij != 'awsm_job_id' &&
							ij != 'awsm_applicant_photo'
						) {
							groups[groups.length] = [
								{ text: data.label[ij], type: 'bold' },
								{ text: data.value[ij], type: 'normal' }
							];
						}
					}
					var notes = [];
					for (var jn in data.value.notes) {
						notes[notes.length] = {
							text: data.value.notes[jn].content,
							info: data.value.notes[jn].author + ', ' + data.value.notes[jn].date_i18n
						};
					}
					var sx = 63;
					if (! applicantPhoto) {
						sx = 17;
					}
					var sy = 60;
					var verticalLineStart = 50;
					var multiPageText = [];
					var scaleFactor = doc.internal.scaleFactor;
					var lineHeightFactor = doc.getLineHeightFactor();
					var yFactor = Math.max((fontSize * lineHeightFactor - fontSize * (lineHeightFactor - 1)) / scaleFactor, 0);

					doc.setDrawColor(lineColor);
					doc.setLineWidth(lineWidth);
					if (showLines) {
						doc.line(15, sy - 10, 192, sy - 10);
					}

					var splittxt;
					var elemY;
					var rsy;
					var splitLastIndex;
					var splitIndex;
					var textValTemp = [];

					for (var li in groups) {
						for (var line in groups[li]) {
							var text = groups[li][line].text;
							var isMultiText = Array.isArray(text);
							var isTextWithLink = (typeof text === 'string') && (text.indexOf('{link}:') === 0);
							
							if (isMultiText) {
								isTextWithLink = (typeof text[0] === 'string') && (text[0].indexOf('{link}:') === 0);
							}
							if (isTextWithLink) {
								if (isMultiText) {
									$.each(text, function(index, textVal) {
										var parts = textVal.split(':{label}:');
										if (parts.length === 2) {
											textValTemp[index] =  parts[1];
										}
										text[index] = textVal.replace('{link}:', '');
									});
								} else {
									text = text.replace('{link}:', '');
								}
							}
							
							if (isMultiText) {
								splittxt = doc.splitTextToSize(textValTemp, 135, {
									fontSize: fontSize
								});
								doc.setFont(font, groups[li][line].type);
							
								elemY = doc.getTextDimensions(splittxt, {
									fontSize: fontSize
								}).h;
								elemY += 7;
							} else {
								splittxt = doc.splitTextToSize(text, 135, {
									fontSize: fontSize
								});
								doc.setFont(font, groups[li][line].type);
							
								elemY = doc.getTextDimensions(splittxt, {
									fontSize: fontSize
								}).h;
								elemY += 2;
							}

							var generateText = function(text, x, y, splittedText) {
								splittedText = typeof splittedText !== 'undefined' ? splittedText : '';
								if (isTextWithLink) {
									if (isMultiText) {
										var linkY = y;
										$.each(text, function(index, textVal) {
											var parts = textVal.split(':{label}:');
											if (parts.length === 2) {
												var link = parts[0].replace('{link}:', '');
												var label = parts[1];
												doc.textWithLink(label, x, linkY, {
													url: link
												});										
												var linkElemY = doc.getTextDimensions(label, {
													fontSize: fontSize
												}).h + 2;
												
												linkY += linkElemY;
											}
										});
									} else {
										var parts = text.split(':{label}:');
										if (parts.length === 2) {
											var link = parts[0].replace('{link}:', '');
											var label = parts[1];
											doc.textWithLink(label, x, y, {
												url: link
											});
										}
									}
								} else {
									doc.text(x, y, splittedText);									
								}
							};
							if ((sy + elemY) > pageSize && multiPageText.length === 0) {
								rsy = (sy + elemY) - pageSize;
								splitLastIndex = Math.ceil((rsy - 2) / yFactor);
								if (splittxt.length > splitLastIndex) {
									splitIndex = splittxt.length - splitLastIndex;
									multiPageText = splittxt.splice(splitIndex);
								}
							}

							if ((sy + 25) >= pageSize || multiPageText.length > 0) {
								generateText(text, sx, sy, splittxt);
								if (showLines && applicantPhoto) {
									doc.line(58, verticalLineStart, 58, pageSize - 5);
								}
								doc.addPage();
								sy = 25;

								if (multiPageText.length > 0) {
									elemY = 0;
									if (! isTextWithLink) {
										doc.text(sx, sy, multiPageText);
									}
									sy = sy + doc.getTextDimensions(multiPageText, {
										fontSize: fontSize
									}).h + 2;
									multiPageText = [];
								}
								verticalLineStart = 20;
							} else {
								generateText(text, sx, sy, splittxt);
							}
							sy = sy + elemY;
						}

						if (showLines && (Number(li) !== groups.length - 1)) {
							if (applicantPhoto) {
								doc.line(sx - 5, sy - 2, 192, sy - 2);
							} else {
								doc.line(15, sy - 2, 192, sy - 2);
							}
						}
						sy = sy + yFactor;
						if (showLines && (Number(li) === groups.length - 1)) {
							doc.line(15, sy, 192, sy);
							if (applicantPhoto) {
								doc.line(58, verticalLineStart, 58, sy);
							}
						}
					}

					sx = 15;
					sy += yFactor * 3;
					if (notes.length > 0) {
						if ((sy + 25) >= pageSize) {
							doc.addPage();
							sy = 25;
						}
						multiPageText = [];
						doc.setFontSize(fontSize);
						doc.setFont(font, 'bold');
						doc.text(sx, sy, data.label.notes);
						sy = sy + yFactor + 7;
						doc.setFont(font, 'normal');
						for (var gi in notes) {
							splittxt = doc.splitTextToSize(notes[gi].text, 170, {
								fontSize: fontSize
							});
							doc.setFontSize(fontSize);
							doc.setTextColor(textColor);
							elemY = doc.getTextDimensions(splittxt, {
								fontSize: fontSize
							}).h + 3;
							if ((sy + elemY) > pageSize && multiPageText.length === 0) {
								rsy = (sy + elemY) - pageSize;
								splitLastIndex = Math.ceil((rsy - 3) / yFactor);
								if (splittxt.length > splitLastIndex) {
									splitIndex = splittxt.length - splitLastIndex;
									multiPageText = splittxt.splice(splitIndex);
								}
							}

							doc.text(sx, sy, splittxt);
							if ((sy + 25) >= pageSize || multiPageText.length > 0) {
								doc.addPage();
								sy = 25;

								if (multiPageText.length > 0) {
									elemY = 0;
									doc.text(sx, sy, multiPageText);
									sy = sy + doc.getTextDimensions(multiPageText, {
										fontSize: fontSize
									}).h + 3;
									multiPageText = [];
								}
							}
							sy = sy + elemY;

							splittxt = doc.splitTextToSize(notes[gi].info, 170, {
								fontSize: 10
							});
							doc.setFontSize(10);
							doc.setTextColor(subtitleColor);
							doc.text(sx, sy, splittxt);
							sy = sy + doc.getTextDimensions(splittxt, {
								fontSize: 10
							}).h + 7;

							if (showLines && (Number(gi) !== notes.length - 1)) {
								doc.line(sx, sy - 7, 192, sy - 7);
							}
						}
					}
					if (action === 'download') {
						doc.save(data.value.awsm_application_id + '-' + data.value.awsm_applicant_name + '.pdf');
					} else if (action === 'print') {
						window.open(URL.createObjectURL(doc.output('blob')));
					}
				}

				var logoOffset = 0;
				if (logo.length > 0) {
					var logoImg = document.createElement('img');
					applicantPhoto.crossOrigin = 'anonymous';
					logoImg.src = logo;
					var logoWidth = 20;
					logoOffset = logoWidth + 5;
					doc.addImage(logoImg, 'JPEG', 15, 20, logoWidth, 0);
				}
				if ('awsm_applicant_photo' in data.value) {
					applicantPhoto = data.value.awsm_applicant_photo;
					if (applicantPhoto.indexOf('data:') === -1 || applicantPhoto.indexOf('base64') === -1) {
						applicantPhoto = document.createElement('img');
						applicantPhoto.crossOrigin = 'anonymous';
						applicantPhoto.src = data.value.awsm_applicant_photo;
					}
				}

				doc.setFont(font, 'bold');
				doc.setFontSize(titleFontSize);
				doc.setTextColor(titleColor);

				doc.text(15 + logoOffset, 25, data.value.awsm_applicant_pdf_title);
				if (applicantPhoto) {
					doc.addImage(applicantPhoto, 'JPEG', 17, 55, 35, 0);
				}
				doc.setFontSize(fontSize);
				doc.setFont(font, 'normal');
				doc.setTextColor(subtitleColor);
				doc.text(
					15 + logoOffset,
					31,
					data.value.awsm_application_date + data.value.awsm_application_user_ip
				);
				doc.setTextColor(textColor);
				var statusY = 41;
				doc.text(15 + logoOffset, statusY, data.value.application_status + '');
				if (data.value.rating) {
					stars(0, 100, function(u) {
						var totalCharWidth = doc.getCharWidthsArray(data.value.application_status + ' ').reduce(function(accumulator, currentValue) {
							return accumulator + currentValue;
						}, 0);
						var sf =
							totalCharWidth *
							doc.internal.getFontSize() /
							doc.internal.scaleFactor;
						doc.addImage(u, 'JPEG', 15 + sf + logoOffset, (statusY - 5));
						var ratingPercentage = Number(data.value.rating) / 5 * 100;

						stars(1, ratingPercentage, function(u) {
							doc.addImage(u, 'JPEG', 15 + sf + logoOffset, (statusY - 5));
							savePDF(doc);
						});
					});
				} else {
					savePDF(doc);
				}
			}
			$elem.prop('disabled', false);
		});
	});

	/*================ Delete or Move Status ================*/
	var deleteStatus;
	var oldStatus;
	var currentStatusLabel;
	var currentStatusKey;
	var existingText = $(".awsm-jobs-delete-status-confirm-msg p").html();

	$('#awsm-repeatable-application-status').on('click', '.awsm-remove-application-status', function(e) {
		e.preventDefault();
		var $deleteBtn = $(this);
		var $wrapper = $deleteBtn.parents('.awsm-acc-main');
		$('.awsm-jobs-delete-status-confirm-msg').removeClass('awsm-hide');

		oldStatus = $deleteBtn.data('old-status');
		if (oldStatus.length > 0) {
			deleteStatus = $deleteBtn.data('status');
			var statusLabel = $deleteBtn.data('status-label');
			currentStatusLabel = statusLabel;
			currentStatusKey = oldStatus;
			var updatedText = existingText + "<strong>'" + statusLabel + "'</strong>?";
			$('.awsm-jobs-continue-status-btn').attr('data-status', statusLabel);
			$('.awsm-jobs-continue-status-btn').attr('data-status-key', oldStatus);
			$(".awsm-jobs-delete-status-confirm-msg").html(updatedText);
			$("#awsm-application-old-status").val(oldStatus);
			$("#awsm-application-new-status-label").val(statusLabel);
			var wpData = [
				{ name: 'awsm_job_old_status', value: oldStatus },
				{ name: 'action', value: 'awsm_job_delete_status' },
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
			];
	
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				data: $.param(wpData),
				beforeSend: function() {
					$('.awsm-job-settings-advaced-container').addClass('awsm-jobs-loading');
				},
				dataType: 'json'
			}).done(function(response) {
				if (response.status_count > 0) {
					if (response.count > 0) {
						$('html').addClass('awsm-jobs-delete-status-popup-on');
						$('.awsm-jobs-delete-status-modal-popup-wrapper').removeClass('awsm-hide');
						$('.awsm-jobs-delete-status-continue-content-in').removeClass('awsm-hide');
						$('.awsm-jobs-delete-status-confirm-content-in').addClass('awsm-hide');
					} else {
						$('html').addClass('awsm-jobs-delete-status-popup-on');
						$('.awsm-jobs-delete-status-modal-popup-wrapper').removeClass('awsm-hide');
						$('.awsm-jobs-delete-move-status-content-in').addClass('awsm-hide');
						$('.awsm-jobs-delete-status-continue-content-in').addClass('awsm-hide');
						$('.awsm-jobs-delete-status-confirm-content-in').removeClass('awsm-hide');
					}
				} else {
					$('html').addClass('awsm-jobs-delete-status-popup-on');
					$('.awsm-jobs-delete-status-modal-popup-wrapper').removeClass('awsm-hide');
					$('.awsm-jobs-no-more-status-content-in').removeClass('awsm-hide');
					$('.awsm-jobs-delete-status-confirm-msg').addClass('awsm-hide');
					$('.awsm-jobs-delete-status-confirm-actions').addClass('awsm-hide');
				}
			}).fail(function(xhr) {
				// eslint-disable-next-line no-console
				console.log(xhr);
			}).always(function() {
				$('.awsm-job-settings-advaced-container').removeClass('awsm-jobs-loading');
			});
		} else {
			$wrapper.slideUp(function() {
				$wrapper.remove();
			});
		}
	});

	var existingTextContinue = $(".awsm-jobs-move-status-modal-content p").html();
	$('.awsm-jobs-continue-status-btn').on('click', function(e) {
		e.preventDefault();
		var $elem = $(this);
		$("li #awsm_application_md_status-" + currentStatusKey).closest("li").hide();
		var updatedText = existingTextContinue + "<strong>'" + currentStatusLabel + "'</strong>";
		var $wrapper = $elem.parents('.awsm-jobs-delete-status-continue-content-in');
		$(".awsm-jobs-move-status-modal-content").html(updatedText);
		$wrapper.addClass('awsm-hide');
		$('.awsm-jobs-delete-status-modal-popup-wrapper').find('.awsm-jobs-delete-move-status-content-in').removeClass('awsm-hide');
		$('.awsm-jobs-delete-status-confirm-msg').addClass('awsm-hide');
	});

	var $selectStatusMsgContainer = $('.awsm-select-status-message');
	var $statushandle = $('.awsm-job-status-handle');

	$statushandle.on('click', function(e) {
		e.preventDefault();
		var $elem = $(this);
		var action = $elem.data('action');
		var oldAppStatus = $("#awsm-application-old-status").val();
		var wpData = [
			{ name: 'action', value: 'awsm_job_status_action' },
			{ name: 'nonce', value: awsmProJobsAdmin.nonce },
			{ name: 'status_action', value: action },
			{ name: 'old_status', value: oldAppStatus },
		];

		var $statusContent = $('.awsm-jobs-delete-move-status-content-in');
		var $checkedInput = $statusContent.find('.awsm-application-md-status:checked');
		var newStatus = $checkedInput.val();
		var selectStatus = awsmProJobsAdmin.i18n.select_status;
		if (action === 'move_status') {
			if ($checkedInput.length == 0) {
				$selectStatusMsgContainer.addClass('awsm-select-status-error-message').html(selectStatus).fadeIn();
				return false;
			} else {
				$selectStatusMsgContainer.fadeOut();
				$('.awsm-jobs-delete-move-status-content-in').removeClass('awsm-hide');
				var getStatusLabel = $("label[for='awsm_application_md_status-" + newStatus + "']");
				wpData = [
					{ name: 'awsm_application_status', value: newStatus },
					{ name: 'old_status', value: oldAppStatus },
					{ name: 'awsm_application_status_label', value: getStatusLabel.text() },
					{ name: 'action', value: 'awsm_job_status_action' },
					{ name: 'nonce', value: awsmProJobsAdmin.nonce },
					{ name: 'status_action', value: action },
				];
			}
		}
		$.ajax({
			url: awsmJobsAdmin.ajaxurl,
			type: 'POST',
			data: $.param(wpData),
			beforeSend: function() {
				$('.awsm-jobs-delete-status-modal').addClass('awsm-jobs-loading');
				$('.awsm-jobs-delete-status-modal-popup-wrapper').off("click");
			},
			dataType: 'json'
		}).done(function(data) {
			if (action === 'delete_status') {
				if (data.success) {
					var htmlContent = data.success;
					$('.awsm-jobs-delete-status-modal-popup-wrapper').addClass('awsm-hide');
					$('.awsm-job-settings-wrap .nav-tab-wrapper').before(htmlContent);
					$('#application-status-' + deleteStatus).remove();
				}
			} else {
				var htmlContent = data.success;
				$('.awsm-jobs-delete-status-modal-popup-wrapper').addClass('awsm-hide');
				$('.awsm-jobs-delete-move-status-content-in').addClass('awsm-hide');
				$('.awsm-job-settings-wrap .nav-tab-wrapper').before(htmlContent);
				$('#application-status-' + deleteStatus).remove();
			}
			$checkedInput.prop("checked", false);
			$("li #awsm_application_md_status-" + oldAppStatus).closest("li").remove();
		}).always(function() {
			$('.awsm-jobs-delete-status-modal').removeClass('awsm-jobs-loading');
		});
	});

	function hidePopupAndShowConfirmMsg(e) {
		e.preventDefault();
		$('.awsm-jobs-delete-status-confirm-msg').removeClass('awsm-hide');
		$('.awsm-jobs-delete-status-modal-popup-wrapper').addClass('awsm-hide');
		$('.awsm-jobs-delete-move-status-content-in').addClass('awsm-hide');
		$("li #awsm_application_md_status-" + currentStatusKey).closest("li").show();
		$selectStatusMsgContainer.fadeOut();
	}

	$('.awsm-jobs-status-modal-dismiss').on('click', hidePopupAndShowConfirmMsg);

	function deleteStatusPopupOutsideClick() {
		$('.awsm-jobs-delete-status-modal-popup-wrapper').on('click', function(e) {
			var container = $(".awsm-jobs-delete-status-modal-content");
			if (!container.is(e.target) && container.has(e.target).length === 0) {
				hidePopupAndShowConfirmMsg(e);
			}
		});
	}

	deleteStatusPopupOutsideClick();

	$(document).on('click', '.awsm-delete-status-notice-dismiss', function() {
		var $elem = $(this);
		$elem.parents('.awsm-status-response-notice').fadeOut('slow', function() {
			$(this).remove();
		}); 
	});

	
	/*================ Move application to another job ================*/
	$('.awsm-job-move-job-apllication').on('click', function(e) {
		e.preventDefault();
		$('#awsm-job-move-application-wrapper').slideToggle();
    });

	$('.awsm-job-move-application-btn').on('click', function(e) {
		var selectedValue = $('.awsm-job-move-application-select').val();
		if (selectedValue) {
			var move_application = confirm( awsmProJobsAdmin.i18n.move_application );
			if (!move_application) {
				e.preventDefault();
			}
		} else {
            alert('Please select a job from the dropdown.');
            e.preventDefault();
        }
	});
});
