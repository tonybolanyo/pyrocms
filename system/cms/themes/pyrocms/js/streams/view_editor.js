(function($) {
	$(function(){

		// When adding / removing a column from the view
		$('.view-columns input.show-column').on('change', function(){

			// Get started
			var columns = new Array();

			// Get checked values
			$(this).parents('div').find('input[type="checkbox"]').each(function(){ 

				// If it's checked - we want it
				if ($(this).is(':checked')) columns.push($(this).data('column'));
				
			});

			// If it's checked - we want it
			if ($(this).is(':checked'))
			{
				// If it's not in our desired columns.. remove it from sorting too
				$(this).closest('.tab-content').find('.dd.column-order').append('<li class="dd-item" data-assignment="' + $(this).data('assignment') + '"><div class="dd-handle">' + $(this).closest('label').text() + '</div></li>');
			}
			else
			{
				// If it's not in our sorting - add it
				$(this).closest('.tab-content').find('li.dd-item[data-assignment^="' + $(this).data('assignment') + '"]').remove();
			}

			// No need to change the value - the below snippet will do that when the list "changes"
			$('.dd.column-order').trigger('change');
		});


		// Make the columns sortable
		$('.dd.column-order').nestable({maxDepth: 0});
		$('.dd.column-order').on('change', function() {

			// Ready..
			var columns = new Array();

			$('.dd li').each(function(){

			// Load er up
			columns.push($(this).data('assignment'));

			});

			// Update the value
			$(this).closest('.tab-content').find('.view-columns-input').val(columns.join('|'));

		});

	});
})(jQuery);
