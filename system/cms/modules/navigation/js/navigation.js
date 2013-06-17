(function($) {
	$(function() {

		// Sortable links
		$('.dd.links-list-wrapper').nestable();


		// View link details
		$('.dd.links-list-wrapper ul li a').on('click', function(){

			var id = $(this).attr('alt');
			
			$(this).closest('ul').find('li').removeClass('highlighted');
			$(this).closest('.dd3-content').addClass('highlighted');

			// Load the details box in
			$(this).closest('.box-content').find('.link-details').load(SITE_URL + 'admin/navigation/ajax_link_details/' + id);
			
			return false;
		});


		// Load edit via ajax
		$(document).on('click', '.link-details a.btn.ajax', function(){

			// Load the form
			$(this).closest('.link-details').load($(this).attr('href'), '', function(){
				
				// Update Chosen
				pyro.chosen();
			});
			return false;
		});


		// Submit edit form via ajax
		$(document).on('submit', 'form#nav-edit', function(e){
			
			e.preventDefault();
			
			$.post(SITE_URL + 'admin/navigation/edit/' + $('input[name="link_id"]').val(), $('#nav-edit').serialize(), function(message){

				// if message is simply "success" then it's a go. Refresh!
				if (message == 'success')
				{
					window.location.href = window.location
				}
				else
				{
					$('.notification').remove();
					$('div#content-body').prepend(message);
					// Fade in the notifications
					$(".notification").fadeIn();
				}
			});
		});


		// Load edit via ajax
		$(document).on('click', '.box-toolbar a.btn.ajax.add', function(){
			
			// Make sure we load it into the right one
			var id = $(this).attr('rel');

			// If we're creating a new one
			// remove the selected icon from link in the tree
			if ($(this).hasClass('add')) {
				$(this).closest('.box').find('.dd3-content').removeClass('highlighted');
			}

			// Load the form
			$(this).closest('.box').find('.link-details').load($(this).attr('href'), '', function(){
				
				// Update Chosen
				pyro.chosen();
			});

			return false;
		});


		// Pick a rule type, show the correct field
		$('input[name="link_type"]').live('change', function(){
			
			// Hide other choices
			$(this).closest('form').find('.navigation-choice-explanation').hide();


			$(this).closest('form').find('#navigation-' + $(this).val())

			// Show only the selected type
			.show()

			// Reset values when switched
			.find('input:not([value="http://"]), select').val('');

		// Trigger default checked
		}).filter(':checked').change();

	});
})(jQuery);