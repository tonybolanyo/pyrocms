<?php if ( $filters != null ): ?>	
	
	<form id="filters" class="padded no-margin bg-grayLightest border-bottom border-color-grayLighter" method="get">


		<!-- Yes, we're filtering -->
		<div class="hidden">
			<?php echo form_hidden('filter-'.$stream->stream_slug, 'yes'); ?>
		</div>

		
		<!-- Search -->
		<?php if (isset($filters['search']) and ! empty($filters['search'])): ?>
		<div class="row-fluid">
			
			<div class="span10">

				<?php foreach ( $filters['search'] as $params ): ?>
					<span class="margin-right">
						<?php

							$name = 'f-';

							// Build the name
							if (isset($params['not']) and $params['not']) $name .= 'not-';
							if (isset($params['exact']) and $params['exact']) $name .= 'exact-';

							$name .= $params['field'];


							// Get the value
							$value = end(explode('-', $this->input->get($name)));


							// Dropdown type
							echo '<label class="display-inline">'.lang_label(isset($params['label']) ? $params['label'] : humanize($params['field'])).':&nbsp;</label>';

							if ( isset($params['options']) )
							{
								echo form_dropdown(
									$name,
									$params['options'],
									$value
									);
							}
							else
							{
								echo form_input(
									$name,
									$value
									);
							}

						?>
					</span>
				<?php endforeach; ?>

				<button class="btn btn-primary btn-small">Search</button>

			</div>

			<div class="span2 text-right">				
				<a href="#" data-toggle="toggle" data-target=".test" data-persistent="true" data-collapsed-chevron="left">
					Advanced <small><i class="icon-chevron-down"></i></small>
				</a>
			</div>
			
		</div>
		<?php endif; ?>
		<!-- /Defined Filters -->


		<!-- Additional Filters -->
		<?php if (isset($filters['additional_filters']) and ! empty($filters['additional_filters'])): ?>
		<section class="margin-top test">
			
			<div class="row-fluid">
			<div class="span12 text-right">
					

					<table class="table table-bordered bg-white">
						<tr class="bg-grayLighter">
							<th colspan="2">Additional Filters</th>
							<th class="text-center" width="50">
								<a href="#" onclick="$(this).closest('table').find('tr:nth-child(2)').clone().appendTo($(this).closest('table')); return false;">Add <i class="icon-plus-sign color-green"></i></a>
							</th>
						</tr>
						<tr>
							<td width="217">
								
								<select class="skip no-margin" onchange="$(this).closest('tr').find('.filter-input').html($('.additional-<?php echo $stream->stream_slug; ?>-filters-data').find('.'+$(this).val()).html());">
									<option value="NONE">-----</option>
									<?php foreach ($filters['additional_filters'] as $params): ?>
									<option value="<?php echo $params['field']; ?>"><?php echo lang_label(isset($params['label']) ? $params['label'] : humanize($params['field'])); ?></option>
									<?php endforeach; ?>
								</select>

							</td>
							<td class="vertical-align-middle filter-input">Select a field...</td>
							<td class="text-center vertical-align-middle">
								<a href="#" class="color-red" onclick="if ($(this).closest('table').find('tr').length == 2) return false; $(this).closest('tr').remove(); return false;"><i class="icon-minus-sign"></i></a>
							</td>
						</tr>
					</table>


			</div>
			</div>

			<?php $this->load->view('admin/partials/streams/customize_results'); ?>

		</section>
		<?php endif; ?>
		<!-- /Additional Filters -->


	</form>

	<?php if (isset($filters['additional_filters']) and ! empty($filters['additional_filters'])): ?>
		<?php $this->load->view('admin/partials/streams/additional_filters_data'); ?>
	<?php endif; ?>

<?php endif; ?>