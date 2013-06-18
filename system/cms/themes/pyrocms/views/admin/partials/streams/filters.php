<?php if ( $filters != null ): ?>	
<section class="filters-wrapper">

	
	<!-- Everything is called from this magical form -->
	<form id="<?php echo $stream->stream_slug; ?>-filters" class="padded no-margin bg-grayLighadvanced-<?php echo $stream->stream_slug; ?>-filters border-bottom border-color-grayLighter" method="get">

		
		<!-- Search -->
		<?php if (isset($filters['search']) and ! empty($filters['search'])): ?>

		<?php 

			// Build our placeholder real quick
			$placeholder = array();
			foreach ($filters['search'] as $field_slug)
			{
				$placeholder[] = lang_label($stream_fields->$field_slug->field_name);
			}
		?>
		<div class="row-fluid">
			
			<div class="span10">

				<input type="hidden" name="search-<?php echo $stream->stream_slug; ?>" value="<?php echo implode('|', $filters['search']); ?>"/>

				<label class="display-inline margin-right">
					Search:
					<input type="text" class="input-xxlarge" name="search-<?php echo $stream->stream_slug; ?>-term" placeholder="<?php echo implode(', ', $placeholder); ?>" value="<?php echo $this->input->get('search-'.$stream->stream_slug.'-term'); ?>"/>
				</label>

				<button class="btn btn-primary btn-small">Search</button>
				<a href="<?php echo site_url(uri_string()); ?>" class="btn btn-small">Clear</a>

			</div>

			<div class="span2 text-right">				
				<a href="#" data-toggle="toggle" data-target=".advanced-<?php echo $stream->stream_slug; ?>-filters" data-state="<?php echo $this->input->get('filter-'.$stream->stream_slug) ? 'visible' : 'hidden'; ?>" data-collapsed-chevron="left">
					Advanced <small><i class="icon-chevron-down"></i></small>
				</a>
			</div>
			
		</div>
		<?php endif; ?>
		<!-- /Defined Filters -->


		<!-- Advanced Filters -->
		<section class="margin-top advanced-filters advanced-<?php echo $stream->stream_slug; ?>-filters" data-stream-slug="<?php echo $stream->stream_slug; ?>">

			<!-- We are filtering?? -->
			<input type="hidden" value="true" class="filtering-flag" <?php echo $this->input->get('filter-'.$stream->stream_slug) ? 'name="filter-'.$stream->stream_slug.'"' : null; ?>/>

			
			<div class="row-fluid">
			<div class="span12 text-right">
					

					<table class="table table-bordered bg-white">
						<tr class="bg-grayLighter">
							<th colspan="3">Advanced Filters</th>
							<th class="text-center" width="50">
								<a href="#" class="add-advanced-filter">Add <i class="icon-plus-sign color-green"></i></a>
							</th>
						</tr>

						<?php if ($this->input->get('filter-'.$stream->stream_slug)): ?>
						<?php $url_variables = $this->input->get(); ?>
						<?php foreach ($url_variables as $filter => $value): ?>

							<?php if (substr($filter, 0, 2) != 'f-') continue; ?>
							
							<?php $filter = substr($filter, 2); ?>
							<?php if (current(explode('-', $filter)) != $stream->stream_slug) continue; ?>
							<?php $filter = str_replace($stream->stream_slug.'-', '', $filter); ?>

							<?php $filter_output = $this->type->filter_output($stream, $stream_fields->$filter, $this->input->get('f-'.$stream->stream_slug.'-'.$filter)); ?>

							<tr>
				
								<td width="217">
									
									<select class="skip no-margin streams-field-filter-on">
										<option value="-----">-----</option>
										<?php foreach ($stream_fields as $slug => $stream_field): ?>
											<option <?php echo $slug == $filter ? 'selected="selected"' : null; ?> value="<?php echo $slug; ?>"><?php echo lang_label($stream_field->field_name); ?></option>
										<?php endforeach; ?>
									</select>

								</td>

								<td width="217" class="streams-field-filter-conditions">
									<?php echo form_dropdown($stream->stream_slug.'-'.$filter.'-f-condition', $filter_output['conditions'], $this->input->get($stream->stream_slug.'-'.$filter.'-f-condition'), 'class="skip no-margin"'); ?>
								</td>

								<td class="vertical-align-middle streams-field-filter-input">
									<?php echo $filter_output['input']; ?>
								</td>

								<td class="text-center vertical-align-middle">
									<a href="#" class="color-red remove-advanced-filter">
										<i class="icon-minus-sign"></i>
									</a>
								</td>

							</tr>

						<?php endforeach; ?>
						<?php endif; ?>
					</table>


			</div>
			</div>

			
			<?php $this->load->view('admin/partials/streams/customize_results'); ?>


		</section>
		<!-- /Advanced Filters -->


	</form>


	<!-- We need this -->
	<?php $this->load->view('admin/partials/streams/advanced_filters_data'); ?>


</section>
<?php endif; ?>