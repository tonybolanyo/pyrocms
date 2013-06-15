<?php echo form_open(uri_string()); ?>
<section class="padding-top">

	<!-- Careful.. With power comes responsibility -->
	<?php if (isset($view->is_locked) and $view->is_locked == 'yes'): ?>
		<div class="alert margin no-margin-top"><?php echo lang('streams:editing_locked_view'); ?></div>
	<?php endif; ?>


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
				<div class="input span9"><?php echo form_dropdown('order_by', $assignments_dropdown, $view->order_by, 'id="order_by"');?></div>
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
		
	<div class="btn-group padded">
		<button type="submit" name="btnAction" value="save" class="btn blue"><span><?php echo lang('buttons:save'); ?></span></button>	
		<?php if ($cancel_uri): ?>
			<a href="<?php echo site_url($cancel_uri); ?>" class="btn gray cancel"><?php echo lang('buttons:cancel'); ?></a>
		<?php endif; ?>
	</div>

</section>	
<?php echo form_close();?>