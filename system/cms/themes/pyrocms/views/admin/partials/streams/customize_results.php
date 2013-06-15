<!-- Controls -->
<div class="row-fluid">
<div class="span12 text-right">

		<section class="btn-group text-right">
			
			<!-- Open our customization modal -->
			<a href="#customize-<?php echo $stream->stream_slug; ?>-results" data-toggle="modal" class="btn btn-small">
				Customize Results <i class="icon-cogs"></i>
			</a>


			<!-- Save our current options - as a view -->
			<a href="#" class="btn btn-small">Save as view</a>

		</section>

</div>
</div>
<!-- /Controls -->



<!-- MODAL: Customize Results -->
<div id="customize-<?php echo $stream->stream_slug; ?>-results" class="modal customize-results hide fade" tabindex="-1" role="dialog" data-height="500">

	
	<!-- Our Header -->
	<div class="modal-header">

		<button type="button" class="close" data-dismiss="modal">×</button>
		
		<h3 id="myModalLabel">Customize Results</h3>

	</div>

	
	<!-- Body Content -->
	<div class="modal-body no-padding">


		<!-- Tabs -->
		<ul class="nav nav-tabs padded no-padding-bottom grayLightest-bg">

			<!-- Choose what columns, from all of them, that you want to see -->
			<li class="active">
				<a href="#choose-<?php echo $stream->stream_slug; ?>-columns" data-toggle="tab">Choose Columns</a>
			</li>

			<!-- As you add columns - reorder them as you wish -->
			<li>
				<a href="#<?php echo $stream->stream_slug; ?>-column-order" data-toggle="tab">Column Order</a>
			</li>

			<!-- How do we want to sort all this? -->
			<li>
				<a href="#sort-<?php echo $stream->stream_slug; ?>-by" data-toggle="tab">Sort by</a>
			</li>

		</ul>
		<!-- /Tabs -->



		<!-- Tab Content (for above tabs) -->
		<div class="tab-content padded no-padding-top">
			
			
			<!-- Choose what columns to diplay -->
			<div class="tab-pane active choose-stream-columns" id="choose-<?php echo $stream->stream_slug; ?>-columns">

				<!-- The Value -->
				<input type="hidden" class="stream-columns-input" name="<?php echo $stream->stream_slug; ?>-columns" value="<?php echo $this->input->get($stream->stream_slug.'-columns'); ?>"/>

				<?php foreach ($stream_fields as $slug=>$stream_field): ?>
				<label>
					<input type="checkbox" class="show-column" <?php echo (in_array($slug, explode('|', $this->input->get($stream->stream_slug.'-columns')))) ? 'checked="checked"' : null; ?> data-column="<?php echo $slug; ?>">
					<?php echo lang_label($stream_field->field_name); ?>
				</label>
				<?php endforeach; ?>
			</div>


			<!-- Define their ordering -->
			<div class="tab-pane dd column-order" id="<?php echo $stream->stream_slug; ?>-column-order">

				<ul class="dd-list">
					
					<?php if ($this->input->get($stream->stream_slug.'-columns')): ?>
					<?php foreach (explode('|', $this->input->get($stream->stream_slug.'-columns')) as $column): ?>
					<li class="dd-item" data-column="<?php echo $column; ?>">
						<div class="dd-handle">
							<?php echo lang_label($stream_fields->$column->field_name); ?>
						</div>
					</li>
					<?php endforeach; ?>
					<?php endif; ?>
					
				</ul>

			</div>


			<!-- Sort by... -->
			<div class="tab-pane" id="sort-<?php echo $stream->stream_slug; ?>-by">

				<select name="order-<?php echo $stream->stream_slug; ?>" class="skip no-margin">
					<?php foreach ($stream_fields as $slug => $stream_field): ?>
					<option <?php echo ($this->input->get('order-'.$stream->stream_slug) == $slug ? 'selected="selected"' : null); ?> value="<?php echo $slug; ?>"><?php echo lang_label($stream_field->field_name); ?></option>
					<?php endforeach; ?>
				</select>

				<select name="sort-<?php echo $stream->stream_slug; ?>" class="skip no-margin">
					<option <?php echo ($this->input->get('sort-'.$stream->stream_slug) ? 'selected="selected"' : null); ?> value="ASC">ASC</option>
					<option <?php echo ($this->input->get('sort-'.$stream->stream_slug) ? 'selected="selected"' : null); ?> value="DESC">DESC</option>
				</select>

			</div>


		</div>
		<!-- /Tab Content -->

	</div>
	<!-- Body Content -->
	

	<!-- Modal Footer -->
	<div class="modal-footer">
		<button class="btn btn-primary" data-dismiss="modal">Ok</button>
	</div>

</div>
<!-- /MODAL: Customize Results -->