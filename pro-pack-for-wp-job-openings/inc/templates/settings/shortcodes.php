<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$taxonomy_objects = get_object_taxonomies( 'awsm_job_openings', 'objects' );

$custom_posts = array(
	'posts_per_page'   => -1,
	'post_type'        => 'awsm_job_openings',
	'post_status'      => array( 'publish' ),
	'suppress_filters' => false,
);
$job_posts    = get_posts( $custom_posts );
?>
<div id="settings-awsm-settings-shortcodes" class="awsm-admin-settings">
	<div class="awsm-settings-col-left">
		<?php do_action( 'before_awsm_settings_main_content', 'shortcodes' ); ?>
		<?php $this->display_subtabs( 'shortcodes' ); ?>

		<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-general-shortcodes-options-container">
			<div class="awsm-form-section">
				<table width="100%" class="awsm-settings-shortcodes-table form-table" data-type="awsmjobs" data-section="awsm-general-shortcodes-nav-subtab">
					<thead>
						 <tr>
						   <th scope="row" colspan="2" class="awsm-form-head-title">
								   <h2><?php esc_html_e( 'Generate shortcode', 'pro-pack-for-wp-job-openings' ); ?></h2>
							</th>
						</tr>
					</thead>
					<tbody>
						<?php do_action( 'before_awsm_shortcodes_settings' ); ?>
						<tr>
							<th scope="row"><?php echo esc_html__( 'Job listing', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
								<ul class="awsm-list-inline">
									<li>
										<label for="awsm_jobs_listing_all">
											 <input type="radio" name="awsm_jobs_listing" value="all_jobs" id="awsm_jobs_listing_all" class="awsm-check-toggle-control awsm-shortcodes-job-listing-control" data-toggle-target="#awsm-jobs-filters-container" checked>
											<?php echo esc_html__( 'All Jobs', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
									<li>
										<label for="awsm_jobs_listing_filtered">
											<input type="radio" name="awsm_jobs_listing" id="awsm_jobs_listing_filtered" value="filter_jobs" class="awsm-check-toggle-control awsm-shortcodes-job-listing-control" data-toggle="true" data-toggle-target="#awsm-jobs-filters-container">
											<?php echo esc_html__( 'Filtered list of jobs', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
								</ul>
								<div id="awsm-jobs-filters-container" class="awsm-hide">
									<br />
									<fieldset>
									<ul class="awsm-check-list">
										<li>
											<p class="description"><?php echo esc_html__( 'Check the options only if you want to filter the listing by specification(s).', 'pro-pack-for-wp-job-openings' ); ?></p>
										</li>
										<?php
										foreach ( $taxonomy_objects as $spec => $spec_details ) :
											?>
											<li>
											<div class="awsm-shortcodes-filter-item">
												<label for="awsm_jobs_filter_by_<?php echo esc_attr( $spec ); ?>">
													<input type="checkbox" id="awsm_jobs_filter_by_<?php echo esc_attr( $spec ); ?>" value="yes" class="awsm-check-toggle-control" data-toggle="true" data-toggle-target="#awsm_jobs_filter_<?php echo esc_attr( $spec ); ?>">
													<?php
														/* translators: %s: specification label */
														printf( esc_html__( 'Filter by %s', 'pro-pack-for-wp-job-openings' ), esc_html( $spec_details->label ) );
													?>
												</label>
												<p id="awsm_jobs_filter_<?php echo esc_attr( $spec ); ?>" class="awsm-hide">
													<select class="awsm-shortcodes-filters-select-control" multiple="multiple" style="width: 100%;" data-filter="<?php echo esc_attr( $spec ); ?>">
												<?php
													$spec_terms = get_terms( $spec, 'orderby=name&hide_empty=1' );
												if ( ! empty( $spec_terms ) ) {
													foreach ( $spec_terms as $spec_term ) {
														echo sprintf( '<option value="%1$s" data-slug="%3$s">%2$s</option>', esc_attr( $spec_term->term_id ), esc_html( $spec_term->name ), esc_attr( $spec_term->slug ) );
													}
												}
												?>
													</select>
												</p>
											</div>
										</li>
											<?php
											endforeach;
										?>
									</ul>
									<div id="awsm-jobs-other-filters-container" class="awsm-hide">
										<br />
										<ul class="awsm-check-list">
											<li>
												<p>
													<label for="awsm_jobs_enable_other_filters">
														<input type="checkbox" id="awsm_jobs_enable_other_filters" value="partial"><?php echo esc_html__( 'Enable other job filters', 'pro-pack-for-wp-job-openings' ); ?>
													</label>
												</p>
												<p class="description"><?php echo esc_html__( 'Checking this option enables other applicable filters (partial support).', 'pro-pack-for-wp-job-openings' ); ?></p>
											</li>
										</ul>
									</div>
									</fieldset>
								</div>
							</td>
						</tr>
						<tr id="awsm-jobs-enable-filters-container">
							<th scope="row"><?php echo esc_html__( 'Filters', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
								<label for="awsm_jobs_enable_filters"><input type="checkbox" id="awsm_jobs_enable_filters" value="yes" checked /><?php echo esc_html__( 'Enable job filters', 'pro-pack-for-wp-job-openings' ); ?></label>
								<p class="description"><?php echo esc_html__( 'Checking this option enables filter option', 'pro-pack-for-wp-job-openings' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="awsm_jobs_listings"><?php echo esc_html__( 'Number of jobs to show', 'pro-pack-for-wp-job-openings' ); ?></label></th>
							<td>
								<input type="text" class="small-text"  id="awsm_jobs_listings" />
								<p class="description"><?php echo esc_html__( 'Default Number of Job Listings to display', 'pro-pack-for-wp-job-openings' ); ?></p>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Job Status', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
								<ul class="awsm-list-inline">
									<li>
										<label for="awsm_jobs_status_default">
											<input type="radio" name="awsm_jobs_status" value="default" id="awsm_jobs_status_default" class="awsm-shortcodes-job-status-control" checked>
											<?php echo esc_html__( 'Default', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
									<li>
										<label for="awsm_jobs_status_active">
											<input type="radio" name="awsm_jobs_status" id="awsm_jobs_status_active" class="awsm-shortcodes-job-status-control" value="publish">
											<?php echo esc_html__( 'Active', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
									<li>
										<label for="awsm_jobs_status_expired">
											<input type="radio" name="awsm_jobs_status" id="awsm_jobs_status_expired" class="awsm-shortcodes-job-status-control" value="expired">
											<?php echo esc_html__( 'Expired', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
								</ul>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="awsm_jobs_orderby"><?php echo esc_html__( 'Order by', 'pro-pack-for-wp-job-openings' ); ?></label></th>
							<td>
								<select id="awsm_jobs_orderby">
									<option value="date" selected><?php echo esc_html_x( 'Date', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?></option>
									<option value="ID"><?php echo esc_html_x( 'ID', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?></option>
									<option value="modified"><?php echo esc_html_x( 'Modified Date', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?></option>
									<option value="name"><?php echo esc_html_x( 'Post Slug', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?></option>
									<option value="rand"><?php echo esc_html_x( 'Random', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?></option>
									<option value="title"><?php echo esc_html_x( 'Title', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html__( 'Order', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
								<ul class="awsm-list-inline">
									<li>
										<label for="awsm_jobs_order_asc">
											<input type="radio" name="awsm_jobs_order" value="ASC" class="awsm-shortcodes-job-order-control" id="awsm_jobs_order_asc">
											<?php echo esc_html_x( 'Ascending', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
									<li>
										<label for="awsm_jobs_order_desc">
											<input type="radio" name="awsm_jobs_order" value="DESC" class="awsm-shortcodes-job-order-control" id="awsm_jobs_order_desc" checked />
											<?php echo esc_html_x( 'Descending', 'shortcode', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
								</ul>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php echo esc_html__( 'Pagination', 'pro-pack-for-wp-job-openings' ); ?>
							</th>
							<td>
								<label for="awsm_jobs_pagination">
									<input type="checkbox" id="awsm_jobs_pagination" value="yes" checked />
									<?php echo esc_html__( 'Enable "Load More"', 'pro-pack-for-wp-job-openings' ); ?>
								</label>
								<p class="description"><?php echo esc_html__( 'Unchecking this option disables pagination for the listing', 'pro-pack-for-wp-job-openings' ); ?></p>
							</td>
						</tr>
						<?php do_action( 'after_awsm_shortcodes_settings' ); ?>
					</tbody>
				</table>
			</div><!-- .awsm-form-section -->
		</div><!-- .awsm-form-section-main -->

		<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-form-shortcodes-options-container" style="display: none">
			<div class="awsm-form-section">
				<table width="100%" class="awsm-settings-shortcodes-table form-table" data-type="awsm_application_form" data-section="awsm-form-shortcodes-nav-subtab">
					<thead>
						<tr>
							<th scope="row" colspan="2" class="awsm-form-head-title">
									<h2><?php esc_html_e( 'Application Form', 'pro-pack-for-wp-job-openings' ); ?></h2>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr id="awsm-jobs-form-shortcodes-container">
							<th scope="row"><?php esc_html_e( 'Job', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
							<select class="awsm-shortcodes-select-job-control">
								<option value=""><?php esc_html_e( 'Select a job', 'pro-pack-for-wp-job-openings' ); ?></option>
								<?php
								if ( ! empty( $job_posts ) ) {
									foreach ( $job_posts as $job_post ) {
										echo sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $job_post->ID ), esc_html( $job_post->post_title ) );
									}
								}
								?>
							</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-job_specs-shortcodes-options-container" style="display: none">
			<div class="awsm-form-section">
				<table width="100%" class="awsm-settings-shortcodes-table form-table" data-type="awsmjob_specs" data-section="awsm-job_specs-shortcodes-nav-subtab">
					<thead>
						<tr>
							<th scope="row" colspan="2" class="awsm-form-head-title">
								<h2><?php esc_html_e( 'Job Specifications', 'wp-job-openings' ); ?></h2>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr id="awsm-jobs-job_specs-shortcodes-container">
							<th scope="row"><?php esc_html_e( 'Job', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
							<select class="awsm-shortcodes-select-job-control">
								<option value=""><?php esc_html_e( 'Select a job', 'pro-pack-for-wp-job-openings' ); ?></option>
								<?php
								if ( ! empty( $job_posts ) ) {
									foreach ( $job_posts as $job_post ) {
										echo sprintf( '<option value="%1$s">%2$s</option>', esc_attr( $job_post->ID ), esc_html( $job_post->post_title ) );
									}
								}
								?>
							</select>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<div class="awsm-form-section-main awsm-sub-options-container" id="awsm-jobs_stats-shortcodes-options-container" style="display: none">
			<div class="awsm-form-section">
				<table width="100%" class="awsm-settings-shortcodes-table form-table" data-type="awsmjobs_stats" data-section="awsm-jobs_stats-shortcodes-nav-subtab">
					<thead>
						<tr>
							<th scope="row" colspan="2" class="awsm-form-head-title">
								<h2><?php esc_html_e( 'Jobs Count', 'wp-job-openings' ); ?></h2>
							</th>
						</tr>
					</thead>
					<tbody>
						<tr id="awsm-jobs-jobs_stats-shortcodes-container">
							<th scope="row"><?php esc_html_e( 'Job Status', 'pro-pack-for-wp-job-openings' ); ?></th>
							<td>
								<ul class="awsm-list-inline">
									<li>
										<label for="awsm_jobs_stats_default">
											<input type="radio" name="awsm_jobs_stats" value="default" id="awsm_jobs_stats_default" class="awsm-shortcodes-jobs-stats-control" checked>
											<?php echo esc_html__( 'Default', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
									<li>
										<label for="awsm_jobs_stats_active">
											<input type="radio" name="awsm_jobs_stats" id="awsm_jobs_stats_active" value="publish" class="awsm-shortcodes-jobs-stats-control">
											<?php echo esc_html__( 'Active', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
									<li>
										<label for="awsm_jobs_stats_expired">
											<input type="radio" name="awsm_jobs_stats" id="awsm_jobs_stats_expired" value="expired" class="awsm-shortcodes-jobs-stats-control">
											<?php echo esc_html__( 'Expired', 'pro-pack-for-wp-job-openings' ); ?>
										</label>
									</li>
								</ul>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>

		<?php do_action( 'after_awsm_settings_main_content', 'shortcodes' ); ?>
		<div class="awsm-form-footer">
			<button class="button button-primary button-large" id="awsm-jobs-generate-shortcode"><?php echo esc_html__( 'Generate Shortcode', 'pro-pack-for-wp-job-openings' ); ?></button>
			<input type="hidden" id="awsmp-jobs-general-prev-value">
			<input type="hidden" id="awsmp-jobs-form-prev-value">
			<input type="hidden" id="awsmp-jobs-job_specs-prev-value">
			<input type="hidden" id="awsmp-jobs-jobs_stats-prev-value">
		</div><!-- .awsm-form-footer -->
	</div>

	<div class="awsm-settings-col-right">
		<div class="awsm-settings-shortcodes-aside">
			<h3><?php echo esc_html__( 'Your shortcode', 'pro-pack-for-wp-job-openings' ); ?></h3>
			<div class="awsm-settings-shortcodes-wrapper">
				<?php printf( '<p><code>%1$s</code></p>', esc_html( '[awsmjobs]' ) ); ?>
			</div>
		</div><!-- .awsm-settings-aside -->
		<?php
		printf(
			'<button id="awsm-copy-clip" type="button" data-clipboard-text="%1$s" class="button">%2$s</button>
			',
			esc_attr( '[awsmjobs]' ),
			esc_html__( 'Copy', 'wp-job-openings' )
		);
		?>
	</div><!-- .awsm-settings-col-right -->
</div><!-- .awsm-admin-settings -->

