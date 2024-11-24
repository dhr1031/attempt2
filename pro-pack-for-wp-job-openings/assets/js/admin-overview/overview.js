/* global awsmJobsAdmin, awsmProJobsAdmin, awsmJobsOverview, awsmJobsAdminOverview, awsmProJobsAdminOverview, Chart, ChartDataLabels */

'use strict';

jQuery(document).ready(function($) {

	/*================ Charts ================*/

	// Applications analytics chart - control handler
	var isApplicationExists = awsmJobsAdminOverview.analytics_data && 'data' in awsmJobsAdminOverview.analytics_data && awsmJobsAdminOverview.analytics_data.data.length > 0;
	if (typeof awsmJobsOverview !== 'undefined' && 'renderAnalyticsChart' in awsmJobsOverview && typeof awsmJobsOverview.renderAnalyticsChart === 'function' && isApplicationExists) {
		$('#awsm-jobs-overview-analytics-chart-control').on('change', function() {
			var $elem = $(this);
			var option = $elem.find('option:selected').val();
			var wpData = [
				{ name: 'nonce', value: awsmProJobsAdmin.nonce },
				{ name: 'awsm_overview_analytics_widget_option', value: option },
				{ name: 'action', value: 'awsm_jobs_overview_applications_analytics_data' }
			];
			$.ajax({
				url: awsmJobsAdmin.ajaxurl,
				type: 'POST',
				beforeSend: function() {
					$('#awsm-jobs-overview-applications-analytics .awsm-jobs-overview-chart-wrapper').addClass('awsm-jobs-overview-chart-loading');
					$('#awsm-jobs-overview-applications-analytics #awsm-jobs-overview-analytics-chart-control').prop('disabled', true);
				},
				data: $.param(wpData),
				dataType: 'json'
			})
				.done(function(response) {
					if (response) {
						var analyticsData = response.data;
						var chartData = {
							labels: analyticsData.labels,
							data: analyticsData.data
						};
						awsmJobsOverview.renderAnalyticsChart(false, chartData);
					}
				}).always(function() {
					$('#awsm-jobs-overview-applications-analytics .awsm-jobs-overview-chart-wrapper').removeClass('awsm-jobs-overview-chart-loading');
					$('#awsm-jobs-overview-applications-analytics #awsm-jobs-overview-analytics-chart-control').prop('disabled', false);
				});
		});
	} else {
		$('#awsm-jobs-overview-applications-analytics #awsm-jobs-overview-analytics-chart-control').prop('disabled', true);
	}

	// Applications by status chart
	var ctx = $('#awsm-jobs-overview-applications-by-status-chart');
	var data = {
		labels: awsmProJobsAdminOverview.applications_by_status_data.labels,
		datasets: [ {
			label: awsmJobsAdminOverview.i18n.chart_label,
			data: awsmProJobsAdminOverview.applications_by_status_data.data,
			backgroundColor: awsmProJobsAdminOverview.applications_by_status_data.colors,
			hoverOffset: 4,
			borderColor: '#fff',
			hoverBorderWidth: 0
		} ]
	};
	var options = {
		layout: {
			padding: {
				left: 10,
				right: 0
			}
		},
		plugins: {
			legend: {
				position: 'right',
				labels: {
					padding: 20,
					usePointStyle: true
				}
			},
			datalabels: {
				color: '#fff',
				font: {
					size: 14,
					weight: 'bold'
				},
				formatter: function(value, context) {
					var meta = context.chart.getDatasetMeta(0);
					var total = meta.total;
					if (value > 0) {
						var percentage = parseFloat(((value / total) * 100).toFixed(2));
						return percentage + '%';
					} else {
						return '';
					}
				}
			}
		}
	};

	var statusChart = null;
	function renderStatusChart(reRender) {
		reRender = typeof reRender !== 'undefined' ? reRender : false;
		if (reRender && statusChart) {
			statusChart.destroy();
			statusChart = null;
		}
		if (! statusChart) {
			statusChart = new Chart(ctx, {
				type: 'doughnut',
				data: data,
				options: options,
				plugins: [ ChartDataLabels ]
			});
		}
	}

	if (awsmProJobsAdminOverview.applications_by_status_data && 'total_applications' in awsmProJobsAdminOverview.applications_by_status_data && awsmProJobsAdminOverview.applications_by_status_data.total_applications > 0) {
		renderStatusChart();

		$('.awsm-jobs-overview-mb-wrapper .meta-box-sortables').on('sortstop', function(e, ui) {
			if (ui.item.attr('id') === 'awsm-jobs-overview-applications-by-status') {
				renderStatusChart(true);
			}
		});
		$('#awsm-jobs-overview-applications-by-status .handle-order-higher, #awsm-jobs-overview-applications-by-status .handle-order-lower' ).on('focus', function() {
			renderStatusChart(true);
		});
	}
});
