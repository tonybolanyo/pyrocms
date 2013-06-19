<?php echo form_open_multipart(uri_string(), 'class="streams_view_form"'); ?>


	<!-- Name -->
	<fieldset class="padded no-padding-bottom">

		<h4 class="no-margin padding-bottom">Name</h4>


		<!-- The Value -->
		<input type="text" name="name" value=""/>

	</fieldset>
	<!-- /Choose Columns -->


	<hr/>


	<!-- Choose Columns -->
	<fieldset class="padding-left padding-right">

		<h4 class="no-margin padding-bottom">Choose Columns</h4>


		<?php foreach ($stream_fields as $slug=>$stream_field): ?>
		<label>
			<input type="checkbox" class="show-column" <?php echo (isset($_GET[$stream->stream_slug.'-column']) and in_array($slug, $_GET[$stream->stream_slug.'-column'])) ? 'checked="checked"' : null; ?> data-column="<?php echo $slug; ?>">
			<?php echo lang_label($stream_field->field_name); ?>
		</label>
		<?php endforeach; ?>

	</fieldset>
	<!-- /Choose Columns -->


	<hr/>


	<!-- Column Order -->
	<fieldset class="padding-left padding-right">

		<h4 class="no-margin padding-bottom">Column Order</h4>


		<!-- Define their ordering -->
		<div class="well dd column-order">

			<ul class="dd-list">
				
				<?php if (isset($_GET[$stream->stream_slug.'-column'])): ?>
				<?php foreach ($columns = $_GET[$stream->stream_slug.'-column'] as $column): ?>
				<li class="dd-item" data-column="<?php echo $column; ?>">
					<div class="dd-handle">
						<?php echo lang_label($stream_fields->$column->field_name); ?>
					</div>
					<input type="hidden" name="<?php echo $stream->stream_slug; ?>-column[]" value="<?php echo $column; ?>"/>
				</li>
				<?php endforeach; ?>
				<?php endif; ?>

				<li class="dd-item empty" style="<?php if ($this->input->get($stream->stream_slug.'-column')) echo 'display: none;' ?>">
					Please choose some columns first.
				</li>
				
			</ul>

		</div>

	</fieldset>
	<!-- /Column Order -->


	<hr/>


	<!-- Order By -->
	<fieldset class="padding-left padding-right">

		<h4 class="no-margin padding-bottom">Order By</h4>


		<!-- Order By Options -->
		<select name="order-<?php echo $stream->stream_slug; ?>" class="no-margin">
			<?php foreach ($stream_fields as $slug => $stream_field): ?>
			<option <?php echo ($this->input->get('order-'.$stream->stream_slug) == $slug ? 'selected="selected"' : null); ?> value="<?php echo $slug; ?>"><?php echo lang_label($stream_field->field_name); ?></option>
			<?php endforeach; ?>
		</select>

		<select name="sort-<?php echo $stream->stream_slug; ?>" class="no-margin">
			<option <?php echo ($this->input->get('sort-'.$stream->stream_slug) ? 'selected="selected"' : null); ?> value="ASC">ASC</option>
			<option <?php echo ($this->input->get('sort-'.$stream->stream_slug) ? 'selected="selected"' : null); ?> value="DESC">DESC</option>
		</select>

	</fieldset>
	<!-- /Order By -->


	<hr/>


	<!-- Search Fields -->
	<fieldset class="padding-left padding-right">

		<h4 class="no-margin padding-bottom">Search Fields</h4>
	
		<?php echo form_multiselect('search-columns', $stream_fields_dropdown, array()); ?>

	</fieldset>
	<!-- /Search Fields -->


	<hr/>


	<!-- Advanced Filters -->
	<fieldset class="padding-left padding-right advanced-filters">

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

	</fieldset>
	<!-- /Advanced Filters -->


	<hr/>


	<?php if ($method == 'edit'){ ?><input type="hidden" value="<?php echo $entry->id;?>" name="row_edit_id" /><?php } ?>

	<!-- Save / Actions -->
	<div class="btn-group padding-left padding-right">
		<button type="submit" name="btnAction" value="save" class="btn"><span><?php echo lang('buttons:save'); ?></span></button>	
		<a href="<?php echo site_url(isset($return) ? $return : 'admin/'.$this->module_details['slug']); ?>" class="btn"><?php echo lang('buttons:cancel'); ?></a>
	</div>

<?php echo form_close();?>