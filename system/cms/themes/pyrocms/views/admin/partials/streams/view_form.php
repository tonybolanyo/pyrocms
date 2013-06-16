<?php echo form_open(uri_string()); ?>

	<!-- .nav-tabs -->
	<ul class="nav nav-tabs padded no-padding-bottom grayLightest-bg" data-persistent-tabs="<?php echo $stream->stream_namespace.'_'.$stream->stream_slug; ?>-view">
		
		<li class="active">
			<a href="#view-general" data-toggle="tab">
				<span>General</span>
			</a>
		</li>
		<li>
			<a href="#view-columns" data-toggle="tab">
				<span>Columns</span>
			</a>
		</li>
		<li>
			<a href="#view-search-fields" data-toggle="tab">
				<span>Search Fields</span>
			</a>
		</li>
		<li>
			<a href="#view-advanced-filters" data-toggle="tab">
				<span>Advanced Filters</span>
			</a>
		</li>

	</ul>


	<!-- Tab Content -->
	<section class="tab-content view-editor" data-view="<?php echo $view->id; ?>" data-stream="<?php echo $view->stream_id; ?>">


		<!-- Careful.. With power comes responsibility -->
		<?php if (isset($view->is_locked) and $view->is_locked == 'yes'): ?>
			<div class="alert margin no-margin-top"><?php echo lang('streams:editing_locked_view'); ?></div>
		<?php endif; ?>



		<!-- Tab Pane -->
		<div class="tab-pane active padding-right padding-left" id="view-general">	
			<fieldset>
				<ul>

					<li class="row-fluid input-row">
						<label class="span3" for="view_title"><?php echo lang('streams:label.title');?> TITLE<span>*</span></label>
						<div class="input span9">
							<?php

							if (substr($view->title, 0, 5) === 'lang:')
							{
								echo '<p><em>'.$this->lang->line(substr($view->title, 5)).'</em></p>';
								echo form_hidden('title', $view->title);
							}
							else
							{
								echo form_input('title', $view->title, 'maxlength="100" id="title" autocomplete="off"');
							}

							?></div>

					</li>

					<?php if (property_exists($view, 'order_by')): ?>

					<li class="row-fluid input-row">
						<label class="span3" for="order_by"><?php echo lang('streams:label.order_by');?> ORDER BY</label>
						<div class="input span9"><?php echo form_dropdown('order_by', $stream_fields_dropdown, $view->order_by, 'id="order_by"');?></div>
					</li>

					<?php endif; ?>

					<?php if (property_exists($view, 'sort')): ?>

					<li class="row-fluid input-row">
						<label class="span3" for="sort"><?php echo lang('streams:label.sort');?> SORT</label>
						<div class="input span9"><?php echo form_dropdown('sort', array('ASC' => 'ASC', 'DESC' => 'DESC'), $view->sort, 'id="sort"');?></div>
					</li>

					<?php endif; ?>

					<?php if (property_exists($view, 'limit')): ?>

					<li class="row-fluid input-row">
						<label class="span3" for="limit"><?php echo lang('streams:label.limit');?> LIMIT</label>
						<div class="input span9"><?php echo form_dropdown('limit', array(10 => 10, 25 => 25, 50 => 50, 100 => 100), $view->limit, 'id="limit"');?></div>
					</li>

					<?php endif; ?>
			
				</ul>
			</fieldset>

		</div>
		<!-- /Tab Pane -->


		<!-- Tab Pane -->
		<div class="tab-pane padding-right padding-left" id="view-columns">

			<section class="row-fluid">

				<fieldset class="view-columns span6">
					
					<ul>
						<li>

							<?php foreach ($stream_fields as $stream_field): ?>
							<label>
								<input type="checkbox" class="show-column" data-assignment="<?php echo $stream_field->assign_id; ?>">
								<?php echo lang_label($stream_field->field_name); ?>
							</label>
							<?php endforeach; ?>

						</li>
					</ul>

				</fieldset>


				<fieldset class="dd column-order span6">

					<ul class="dd-list">
						
						<!--<li class="dd-item" data-assignment="<?php //echo $view_stream_field->assign_id; ?>">
							<div class="dd-handle dd3-handle"></div>
							<div class="dd3-content">
								<?php //echo lang_label($view_stream_field->stream_field->field_name); ?>
							</div>
						</li>-->
						
					</ul>

				</fieldset>

			</section>

		</div>
		<!-- /Tab Pane -->


		<!-- Tab Pane -->
		<div class="tab-pane" id="view-search-fields">	

			<fieldset>
				
				<ul>
					<li>

						Search Filters

					</li>
				</ul>

			</fieldset>

		</div>
		<!-- /Tab Pane -->


		<!-- Tab Pane -->
		<div class="tab-pane" id="view-advanced-filters">	

			<fieldset>
				
				<ul>
					<li>

						The Advanced Filters

					</li>
				</ul>

			</fieldset>

		</div>
		<!-- /Tab Pane -->


		<div class="btn-group padded">
			<button type="submit" name="btnAction" value="save" class="btn blue"><span><?php echo lang('buttons:save'); ?></span></button>	
			<?php if ($cancel_uri): ?>
				<a href="<?php echo site_url($cancel_uri); ?>" class="btn gray cancel"><?php echo lang('buttons:cancel'); ?></a>
			<?php endif; ?>
		</div>

	</section>
	<!-- /Tab Content -->


<?php echo form_close();?>