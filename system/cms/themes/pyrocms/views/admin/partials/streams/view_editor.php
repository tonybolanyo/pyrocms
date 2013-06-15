<!-- .nav-tabs -->
<ul class="nav nav-tabs padded no-padding-bottom grayLightest-bg" data-persistent-tabs="<?php echo $stream->stream_namespace.'_'.$stream->stream_slug; ?>-view">
	
	<li class="active">
		<a href="#view-columns" data-toggle="tab">
			<span>Columns</span>
		</a>
	</li>
	<li>
		<a href="#view-column-order" data-toggle="tab">
			<span>Column Order</span>
		</a>
	</li>
	<li>
		<a href="#view-filters" data-toggle="tab">
			<span>Filters</span>
		</a>
	</li>
	<li>
		<a href="#view-additional-filters" data-toggle="tab">
			<span>Additional Filters</span>
		</a>
	</li>

</ul>


<section class="tab-content">


	<!-- Careful.. With power comes responsibility -->
	<?php if (isset($view->is_locked) and $view->is_locked == 'yes'): ?>
		<div class="alert margin no-margin-top"><?php echo lang('streams:editing_locked_view'); ?></div>
	<?php endif; ?>


	<!-- Tab Pane -->
	<div class="tab-pane active view-columns padding-right padding-left" id="view-columns">	

		<fieldset>
			
			<ul>
				<li>

					<?php foreach ($assignments as $assignment): ?>
					<label>
						<input type="checkbox" class="show-column" data-assignment="<?php echo $assignment->assign_id; ?>">
						<?php echo lang_label($assignment->field_name); ?>
					</label>
					<?php endforeach; ?>

				</li>
			</ul>

		</fieldset>

	</div>
	<!-- /Tab Pane -->


	<!-- Tab Pane -->
	<div class="tab-pane dd column-order padding-right padding-left" id="view-column-order">

		<fieldset>

			<ul class="dd-list">
				
				<?php foreach ($view->assignments as $view_assignment): ?>
				<li class="dd-item" data-assignment="<?php echo $view_assignment->id; ?>">
					<div class="dd-handle">
						<?php echo lang_label($view_assignment->assignment->field_name); ?>
					</div>
				</li>
				<?php endforeach; ?>
				
			</ul>

		</fieldset>

	</div>
	<!-- /Tab Pane -->


	<!-- Tab Pane -->
	<div class="tab-pane" id="view-filters">	

		<fieldset>
			
			<ul>
				<li>

					The Filters

				</li>
			</ul>

		</fieldset>

	</div>
	<!-- /Tab Pane -->


	<!-- Tab Pane -->
	<div class="tab-pane" id="view-additional-filters">	

		<fieldset>
			
			<ul>
				<li>

					The Additional Filters

				</li>
			</ul>

		</fieldset>

	</div>
	<!-- /Tab Pane -->


	<div class="btn-group padded">
		<a href="<?php echo site_url($return); ?>" class="btn btn-primary">Done</a>
	</div>

</section>