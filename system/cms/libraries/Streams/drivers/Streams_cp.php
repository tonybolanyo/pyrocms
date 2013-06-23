<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Control Panel Driver
 *
 * Contains functions that allow for
 * constructing PyrCMS stream control
 * panel elements.
 *
 * @author  	Parse19
 * @package  	PyroCMS\Core\Libraries\Streams\Drivers
 */ 
 
class Streams_cp extends CI_Driver {

	private $CI;

	// --------------------------------------------------------------------------

	/**
	 * Constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->CI =& get_instance();
	}

	// --------------------------------------------------------------------------

	/**
	 * Entries Table
	 *
	 * Creates a table of entries.
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	[mixed - pagination, either null for no pagination or a number for per page]
	 * @param	[null - pagination uri without offset]
	 * @param	[bool - setting this to true will take care of the $this->template business
	 * @param	[array - extra params (see below)]
	 * @return	mixed - void or string
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * title	- Title of the page header (if using view override)
	 *			$extra['title'] = 'Streams Sample';
	 * 
	 * buttons	- an array of buttons (if using view override)
	 *			$extra['buttons'] = array(
	 *				'label' 	=> 'Delete',
	 *				'url'		=> 'admin/streams_sample/delete/-entry_id-',
	 *				'confirm'	= true
	 *			);
	 * columns  - an array of field slugs to display. This overrides view options.
	 * 			$extra['columns'] = array('field_one', 'field_two');
	 *
 	 * sorting  - bool. Whether or not to turn on the drag/drop sorting of entries. This defaults
 	 * 			to the sorting option of the stream.
	 *
	 * see docs for more explanation
	 */
	public function entries_table($stream_slug, $namespace_slug, $pagination = null, $pagination_uri = null, $view_override = false, $extra = array())
	{
		$CI = get_instance();
		
		// Get stream
		$stream = $this->stream_obj($stream_slug, $namespace_slug);
		if ( ! $stream) $this->log_error('invalid_stream', 'entries_table');

		// -------------------------------------
		// Get Header Fields
		// -------------------------------------
		
		$stream_fields = $CI->streams_m->get_stream_fields($stream->id);

 		// We need to make sure that stream_fields is 
 		// at least an empty object.
 		if ( ! is_object($stream_fields))
 		{
 			$stream_fields = new stdClass;
 		}

 		$stream_fields->id = new stdClass;
  		$stream_fields->created = new stdClass;
 		$stream_fields->updated = new stdClass;
 		$stream_fields->created_by = new stdClass;

  		$stream_fields->id->field_name 				= lang('streams:id');
		$stream_fields->created->field_name 		= lang('streams:created_date');
 		$stream_fields->updated->field_name 		= lang('streams:updated_date');
 		$stream_fields->created_by->field_name 		= lang('streams:created_by');

 		// -------------------------------------
		// Find offset URI from array
		// -------------------------------------
		
		if (is_numeric($pagination))
		{
			$segs = explode('/', $pagination_uri);
			$offset_uri = count($segs)+1;
	
	 		$offset = $CI->uri->segment($offset_uri, 0);

			// Calculate actual offset if not first page
			if ( $offset > 0 )
			{
				$offset = ($offset - 1) * $pagination;
			}
  		}
  		else
  		{
  			$offset_uri = null;
  			$offset = 0;
  		}

  		// -------------------------------------
		// Sorting
		// @since 2.1.5
		// -------------------------------------

		if ($stream->sorting == 'custom' or (isset($extra['sorting']) and $extra['sorting'] === true))
		{
			$stream->sorting = 'custom';

			// As an added measure of obsurity, we are going to encrypt the
			// slug of the module so it isn't easily changed.
			$CI->load->library('encrypt');

			// We need some variables to use in the sort.
			$CI->template->append_metadata('<script type="text/javascript" language="javascript">var stream_id='.$stream->id.'; var stream_offset='.$offset.'; var streams_module="'.$CI->encrypt->encode($CI->module_details['slug']).'";
				</script>');
			$CI->template->append_js('streams/entry_sorting.js');
		}
  
  		$data = array(
  			'stream'		=> $stream,
  			'stream_fields'	=> $stream_fields,
  			'buttons'		=> isset($extra['buttons']) ? $extra['buttons'] : null,
  			'filters'		=> isset($extra['filters']) ? $extra['filters'] : null,
  			'view_options'	=> isset($extra['view_options']) ? $extra['view_options'] : false,
  		);
 
  		// -------------------------------------
		// Columns
		// @since 2.1.5
		// -------------------------------------

		if (isset($extra['columns']) and is_array($extra['columns']))
		{
			$stream->view_options = $extra['columns'];
		}

 		// -------------------------------------
		// Filter API
		// -------------------------------------

		$where = array();


		// First check for simple searching
		if ($CI->input->get('search-'.$stream->stream_slug) and $CI->input->get('search-'.$stream->stream_slug.'-term'))
		{
			$search = array();

			foreach (explode('|', $CI->input->get('search-'.$stream->stream_slug)) as $filter)
			{
				$search[] = $CI->db->protect_identifiers($stream->stream_prefix.$stream->stream_slug.'.'.$filter).' LIKE "%'.urldecode($CI->input->get('search-'.$stream->stream_slug.'-term')).'%"';
			}
			
			// Add our search fragment
			$where[] = ' ( '.implode(' OR ', $search).' ) ';
		}


		// Now check for advanced filters
		if ($CI->input->get('f-'.$stream->stream_slug.'-filter'))
		{
			// Get all URL variables
			$query_string_variables = $CI->input->get();

			// Loop and process
			foreach ($query_string_variables['f-'.$stream->stream_slug.'-filter'] as $k => $filter)
			{
				// -------------------------------------
				// NICE! Now figure out the condition
				// -------------------------------------
				// is
				// isnot
				// contains
				// doesnotcontain
				// startswith
				// endswith
				// isempty
				// isnotempty
				// ........ To be continued
				// -------------------------------------

				$value = urldecode($query_string_variables['f-'.$stream->stream_slug.'-value'][$k]);

				// We really need a value unless it's a couple of specific cases
				if (empty($value) and ! in_array($query_string_variables['f-'.$stream->stream_slug.'-condition'][$k], array('isempty', 'isnotempty'))) continue;


				// What are we doing?
				switch ($query_string_variables['f-'.$stream->stream_slug.'-condition'][$k])
				{

					case 'is':

						// Referencing another field?
						if (substr($value, 0, 2) == '${')
						{
							$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' = '.$CI->db->protect_identifiers($stream->stream_prefix.$stream->stream_slug.'.'.trim(substr($value, 2, -1)));
						}
						else
						{
							$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' = "'.$value.'"';
						}
						break;

					case 'isnot':
						$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' != "'.$value.'"';
						break;

					case 'contains':
						$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' LIKE "%'.$value.'%"';
						break;

					case 'doesnotcontain':
						$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' NOT LIKE "%'.$value.'%"';
						break;

					case 'startswith':
						$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' LIKE "'.$value.'%"';
						break;

					case 'endswith':
						$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' LIKE "%'.$value.'"';
						break;

					case 'isempty':
						$where[] = ' ('.$CI->db->protect_identifiers($stream->stream_prefix.$stream->stream_slug.'.'.$filter).' IS NULL OR '.$CI->db->protect_identifiers($stream->stream_prefix.$stream->stream_slug.'.'.$filter).' = "") ';
						break;

					case 'isnotempty':
						$where[] = ' ('.$CI->db->protect_identifiers($stream->stream_prefix.$stream->stream_slug.'.'.$filter).' IS NOT NULL AND '.$CI->db->protect_identifiers($stream->stream_prefix.$stream->stream_slug.'.'.$filter).' != "") ';
						break;
					
					default:
						// is
						$where[] = $stream->stream_prefix.$stream->stream_slug.'.'.$filter.' = "'.$value.'"';
						break;
				}
			}
		}

		$filter_data = $where;

 		// -------------------------------------
		// Get Entries
		// -------------------------------------
		
		$limit = ($pagination) ? $pagination : null;
	
		$data['entries'] = $CI->streams_m->get_stream_data(
														$stream,
														$stream_fields, 
														$limit,
														$offset,
														$filter_data);


		// -------------------------------------
		// Pagination
		// -------------------------------------

		foreach ($filter_data as $filter)
		{
			$CI->db->where($filter, null, false);
		}

		$data['pagination'] = create_pagination(
									$pagination_uri,
									$CI->db->select('id')->count_all_results($stream->stream_prefix.$stream->stream_slug),
									$pagination,
									$offset_uri
								);
		
		// -------------------------------------
		// Build Pages
		// -------------------------------------
		
		// Set title
		if (isset($extra['title']))
		{
			$CI->template->title(lang_label($extra['title']));
		}

		// Append our advanced filters scripts
		$CI->template->append_js('streams/advanced_filters.js');

		// Set custom no data message
		if (isset($extra['no_entries_message']))
		{
			$data['no_entries_message'] = $extra['no_entries_message'];
		}
		
		$CI->template->append_js('streams/customize_results.js');
		
		$table = $CI->load->view('admin/partials/streams/entries', $data, true);
		
		if ($view_override)
		{
			// Hooray, we are building the template ourself.
			$CI->template->build('admin/partials/blank_section', array('content' => $table));
		}
		else
		{
			// Otherwise, we are returning the table
			return $table;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Entry Form
	 *
	 * Creates an entry form for a stream.
	 *
	 * @param	string - stream slug
	 * @param	string - stream namespace
	 * @param	mode - new or edit
	 * @param	[array - current entry data]
	 * @param	[bool - view override - setting this to true will build template]
	 * @param	[array - extra params (see below)]
	 * @param	[array - fields to skip]	 
	 * @return	mixed - void or string
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * email_notifications 	- see docs for more explanation
	 * return				- URL to return to after submission
	 * 							defaults to current URL.
	 * success_message		- Flash message to show after successful submission
	 * 							defaults to generic successful entry submission message
	 * failure_message		- Flash message to show after failed submission,
	 * 							defaults to generic failed entry submission message
	 * required				- String to show as required - this defaults to the
	 * 							standard * for the PyroCMS CP
	 * title				- Title of the form header (if using view override)
	 * no_fields_message    - Custom message when there are no fields.
	 */
	public function entry_form($stream_slug, $namespace_slug, $mode = 'new', $entry_id = null, $view_override = false, $extra = array(), $skips = array(), $tabs = false, $hidden = array(), $defaults = array())
	{
		$CI = get_instance();

		$stream = $this->stream_obj($stream_slug, $namespace_slug);
		if ( ! $stream) $this->log_error('invalid_stream', 'form');

		// Load up things we'll need for the form
		$CI->load->library(array('form_validation', 'streams_core/Fields'));
	
		if ($mode == 'edit')
		{
			if( ! $entry = $CI->row_m->get_row($entry_id, $stream, false))
			{
				$this->log_error('invalid_row', 'form');
			}
		}
		else
		{
			$entry = null;
		}

		// Get our field form elements.
		$fields = $CI->fields->build_form($stream, $mode, $entry, false, false, $skips, $extra, $defaults);

		$data = array(
					'fields' 	=> $fields,
					'tabs'		=> $tabs,
					'hidden'	=> $hidden,
					'defaults'	=> $defaults,
					'stream'	=> $stream,
					'entry'		=> $entry,
					'mode'		=> $mode);
		
		// Set title
		if (isset($extra['title']))
		{
			$CI->template->title($extra['title']);
		}
		// Set return uri
		if (isset($extra['return']))
		{
			$data['return'] = $extra['return'];
		}

		// Set cancel uri
		if (isset($extra['cancel_uri']))
		{
			$data['cancel_uri'] = $extra['cancel_uri'];
		}

		// Set the no fields mesage. This has a lang default.
		if (isset($extra['no_fields_message']))
		{
			$data['no_fields_message'] = $extra['no_fields_message'];
		}
		
		if ($data['tabs'] === false)
		{
			$form = $CI->load->view('admin/partials/streams/form', $data, true);
		}
		else
		{
			// Make the fields keys the input_slug. This will make it easier to build tabs. Less looping.
			foreach ( $data['fields'] as $k => $v ){
				$data['fields'][$v['input_slug']] = $v;
				unset($data['fields'][$k]);
			}

			$form = $CI->load->view('admin/partials/streams/tabbed_form', $data, true);
		}
		
		if ($view_override === false) return $form;
		
		$data['content'] = $form;
		//$CI->data->content = $form;

		$CI->data = new stdClass;
		$CI->data->content = $form;
		
		$CI->template->build('admin/partials/blank_section', $data);
	}

	// --------------------------------------------------------------------------

	/**
	 * Custom Field Form
	 *
	 * Creates a custom field form.
	 *
	 * This allows you to easily create a form that users can
	 * use to add new fields to a stream. This functions as the
	 * form assignment as well.
	 *
	 * @param	string - stream slug
	 * @param	string - namespace
	 * @param 	string - method - new or edit. defaults to new
	 * @param 	string - uri to return to after success/fail
	 * @param 	[int - the assignment id if we are editing]
	 * @param	[array - field types to include]
	 * @param	[bool - view override - setting this to true will build template]
	 * @param	[array - extra params (see below)]
	 * @return	mixed - void or string
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * title	- Title of the form header (if using view override)
	 *			$extra['title'] = 'Streams Sample';
	 * 
	 * show_cancel - bool. Show the cancel button or not?
	 * cancel_url - uri to link to for cancel button
	 *
	 * see docs for more.
	 */
	public function field_form($stream_slug, $namespace, $method = 'new', $return, $assign_id = null, $include_types = array(), $view_override = false, $extra = array(), $exclude_types = array(), $skips = array())
	{
		$CI = get_instance();
		$data = array();
		$data['field'] = new stdClass;
		
		// We always need our stream
		$stream = $this->stream_obj($stream_slug, $namespace);
		if ( ! $stream) $this->log_error('invalid_stream', 'form');

		// -------------------------------------
		// Include/Exclude Field Types
		// -------------------------------------
		// Allows the inclusion or exclusion of
		// field types.
		// -------------------------------------

		if ($include_types)
		{
			$ft_types = new stdClass();

			foreach ($CI->type->types as $type)
			{
				if (in_array($type->field_type_slug, $include_types))
				{
					$ft_types->{$type->field_type_slug} = $type;
				}
			}
		}
		elseif (count($exclude_types) > 0)
		{
			$ft_types = new stdClass();

			foreach ($CI->type->types as $type)
			{
				if ( ! in_array($type->field_type_slug, $exclude_types))
				{
					$ft_types->{$type->field_type_slug} = $type;
				}
			}
		}
		else
		{
			$ft_types = $CI->type->types;
		}

		// -------------------------------------
		// Field Type Assets
		// -------------------------------------
		// These are assets field types may
		// need when adding/editing fields
		// -------------------------------------
   		
   		$CI->type->load_field_crud_assets($ft_types);
   		
   		// -------------------------------------
        
		// Need this for the view
		$data['method'] = $method;

		// Get our list of available fields
		$data['field_types'] = $CI->type->field_types_array($ft_types);

		// -------------------------------------
		// Get the field if we have the assignment
		// -------------------------------------
		// We'll always work off the assignment.
		// -------------------------------------

		if ($method == 'edit' and is_numeric($assign_id))
		{
			$assignment = $CI->db->limit(1)->where('id', $assign_id)->get(ASSIGN_TABLE)->row();

			// If we have no assignment, we can't continue
			if ( ! $assignment) show_error('Could not find assignment');

			// Find the field now
			$data['current_field'] = $CI->fields_m->get_field($assignment->field_id);

			// We also must have a field if we're editing
			if ( ! $data['current_field']) show_error('Could not find field.');

			// The assignment needs this too
			$assignment->field_map = $data['current_field']->field_map;
		}
		elseif ($method == 'new' and $_POST and $this->CI->input->post('field_type'))
		{
			$data['current_field'] = new stdClass();
			$data['current_field']->field_type = $this->CI->input->post('field_type');
		}
		else
		{
			$data['current_field'] = null;
		}

		// -------------------------------------
		// Should we should the set as title
		// column checkbox?
		// -------------------------------------

		if (isset($extra['allow_title_column_set']) and $extra['allow_title_column_set'] === true) {
			$data['allow_title_column_set'] = true;
		} else {
			$data['allow_title_column_set'] = false;
		}

		// -------------------------------------
		// Cancel Button
		// -------------------------------------

		$data['show_cancel'] = (isset($extra['show_cancel']) and $extra['show_cancel']) ? true : false;
		$data['cancel_uri'] = (isset($extra['cancel_uri'])) ? $extra['cancel_uri'] : null;

		// -------------------------------------
		// Validation & Setup
		// -------------------------------------

		if ($method == 'new')
		{
			$CI->fields_m->fields_validation[1]['rules'] .= '|streams_unique_field_slug[new:'.$namespace.']';

			$CI->fields_m->fields_validation[1]['rules'] .= '|streams_col_safe[new:'.$stream->stream_prefix.$stream->stream_slug.']';
		}
		else
		{
			// @todo edit version of this.
			$CI->fields_m->fields_validation[1]['rules'] .= '|streams_unique_field_slug['.$data['current_field']->field_slug.':'.$namespace.']';

			$CI->fields_m->fields_validation[1]['rules'] .= '|streams_col_safe[edit:'.$stream->stream_prefix.$stream->stream_slug.':'.$data['current_field']->field_slug.']';
		}

		$assign_validation = array(
			array(
				'field'	=> 'is_required',
				'label' => 'Is Required', // @todo languagize
				'rules'	=> 'trim'
			),
			array(
				'field'	=> 'is_unique',
				'label' => 'Is Unique', // @todo languagize
				'rules'	=> 'trim'
			),
			array(
				'field'	=> 'instructions',
				'label' => 'Instructions', // @todo languageize
				'rules'	=> 'trim'
			),
			array(
				'field'	=> 'field_map',
				'label' => 'Field Map', // @todo languageize
				'rules'	=> 'trim'
			)
		);

		// Get all of our valiation into one super validation object
		$validation = array_merge($CI->fields_m->fields_validation, $assign_validation);

		// Check if $skips is set to bypass validation for specified field slugs

		// No point skipping field_name & field_type
		$disallowed_skips = array('field_name', 'field_type');

		if (count($skips) > 0)
		{
			foreach ($skips as $skip)
			{
				// First check if the current skip is disallowed
				if (in_array($skip['slug'], $disallowed_skips))
				{
					continue;
				}

				foreach ($validation as $key => $value) 
				{
					if (in_array($value['field'], $skip))
					{
						unset($validation[$key]);
					}
				}
			}
		}

		$CI->form_validation->set_rules($validation);

		// -------------------------------------
		// Process Data
		// -------------------------------------

		if ($CI->form_validation->run())
		{
			$post_data = $CI->input->post();

			// Set custom data from $skips param
			if (count($skips) > 0)
			{	
				foreach ($skips as $skip)
				{
					if ($skip['slug'] == 'field_slug' && ( ! isset($skip['value']) || empty($skip['value'])))	
					{
						show_error('Set a default value for field_slug in your $skips param.');
					}
					else
					{
						$post_data[$skip['slug']] = $skip['value'];
					}
				}
			}

			if ($method == 'new')
			{
				if ( ! $CI->fields_m->insert_field(
									$post_data['field_name'],
									$post_data['field_slug'],
									$post_data['field_type'],
									$namespace,
									$post_data['field_map'],
									$post_data
					))
				{
				
					$CI->session->set_flashdata('notice', lang('streams:save_field_error'));	
				}
				else
				{
					// Add the assignment
					if( ! $CI->streams_m->add_field_to_stream($CI->db->insert_id(), $stream->id, $post_data))
					{
						$CI->session->set_flashdata('notice', lang('streams:save_field_error'));	
					}
					else
					{
						$CI->session->set_flashdata('success', (isset($extra['success_message']) ? $extra['success_message'] : lang('streams:field_add_success')));	
					}
				}
			}
			else
			{
				if ( ! $CI->fields_m->update_field(
									$data['current_field'],
									array_merge($post_data, array('field_namespace' => $namespace))
					))
				{
				
					$CI->session->set_flashdata('notice', lang('streams:save_field_error'));	
				}
				else
				{
					// Add the assignment
					if( ! $CI->fields_m->edit_assignment(
										$assign_id,
										$stream,
										$data['current_field'],
										$post_data
									))
					{
						$CI->session->set_flashdata('notice', lang('streams:save_field_error'));	
					}
					else
					{
						$CI->session->set_flashdata('success', (isset($extra['success_message']) ? $extra['success_message'] : lang('streams:field_update_success')));
					}
				}

			}
	
			redirect($return);
		}

		// -------------------------------------
		// See if we need our param fields
		// -------------------------------------
		
		if ($CI->input->post('field_type') or $method == 'edit')
		{
			// Figure out where this is coming from - post or data
			if ($CI->input->post('field_type'))
			{
				$field_type = $CI->input->post('field_type');
			}
			else
			{
				$field_type = $data['current_field']->field_type;
			}
		
			if (isset($CI->type->types->{$field_type}))
			{
				// Get the type so we can use the custom params
				$data['current_type'] = $CI->type->types->{$field_type};

				if ( ! is_object($data['current_field']))
				{
					$data['current_field'] = new stdClass();
					$data['current_field']->field_data = array();
				}
				
				// Get our standard params
				require_once(PYROSTEAMS_DIR.'libraries/Parameter_fields.php');
				
				$data['parameters'] = new Parameter_fields();
				
				if (isset($data['current_type']->custom_parameters) and is_array($data['current_type']->custom_parameters))
				{
					// Build items out of post data
					foreach ($data['current_type']->custom_parameters as $param)
					{
						if ( ! isset($_POST[$param]) and $method == 'edit')
						{
							if (isset($data['current_field']->field_data[$param]))
							{
								$data['current_field']->field_data[$param] = $data['current_field']->field_data[$param];
							}
						}
						else
						{
							$data['current_field']->field_data[$param] = $CI->input->post($param);
						}
					}
				}
			}
		}

		// -------------------------------------
		// Set our data for the form	
		// -------------------------------------

		foreach ($validation as $field)
		{
			if ( ! isset($_POST[$field['field']]) and $method == 'edit')
			{
				// We don't know where the value is. Hooray
				if (isset($data['current_field']->{$field['field']}))
				{
					$data['field']->{$field['field']} = $data['current_field']->{$field['field']};
				}
				else
				{
					$data['field']->{$field['field']} = $assignment->{$field['field']};
				}
			}
			else
			{
				$data['field']->{$field['field']} = $CI->input->post($field['field']);
			}
		}

		// Repopulate title column set
		$data['title_column_status'] = false;

		if ($data['allow_title_column_set'] and $method == 'edit') {

			if ($stream->title_column and $stream->title_column == $CI->input->post('title_column')) {
				$data['title_column_status'] = true;
			}
			elseif ($stream->title_column and $stream->title_column == $data['current_field']->field_slug) {
				$data['title_column_status'] = true;
			}
			
		} elseif ($data['allow_title_column_set'] and $method == 'new' and $_POST) {

			if ($CI->input->post('title_column')) {
				$data['title_column_status'] = true;
			}
		}

		// -------------------------------------
		// Run field setup events
		// -------------------------------------

		$CI->fields->run_field_setup_events($stream, $method, $data['current_field']);

		// -------------------------------------
		// Build page
		// -------------------------------------

		$CI->template->append_js('streams/fields.js');

		// Set title
		if (isset($extra['title']))
		{
			$CI->template->title($extra['title']);
		}

		// Set the cancel URI. If there is no cancel URI, then we won't
		// have a cancel button.
		$data['cancel_uri'] = (isset($extra['cancel_uri'])) ? $extra['cancel_uri'] : null;

		$table = $CI->load->view('admin/partials/streams/field_form', $data, true);
		
		if ($view_override)
		{
			// Hooray, we are building the template ourself.
			$CI->template->build('admin/partials/blank_section', array('content' => $table));
		}
		else
		{
			// Otherwise, we are returning the table
			return $table;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Fields Table
	 *
	 * Easily create a table of fields in a certain namespace
	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	[mixed - pagination, either null for no pagination or a number for per page]
	 * @param	[null - pagination uri without offset]
	 * @param	[bool - setting this to true will take care of the $this->template business
	 * @param	[array - extra params (see below)]
	 * @param	[array - fields to skip]
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * title	- Title of the page header (if using view override)
	 *			$extra['title'] = 'Streams Sample';
	 * 
	 * buttons	- an array of buttons (if using view override)
	 *			$extra['buttons'] = array(
	 *				'label' 	=> 'Delete',
	 *				'url'		=> 'admin/streams_sample/delete/-entry_id-',
	 *				'confirm'	= true
	 *			);
	 *
	 * see docs for more explanation
	 */
	public function fields_table($namespace, $pagination = null, $pagination_uri = null, $view_override = false, $extra = array(), $skips = array())
	{
		$CI = get_instance();
		$data['buttons'] = isset($extra['buttons']) ? $extra['buttons'] : null;

		// Determine the offset and the pagination URI.
		if (is_numeric($pagination))
		{
			$segs = explode('/', $pagination_uri);
			$page_uri = count($segs)+1;
	
	 		$offset = $CI->uri->segment($page_uri, 0);

			// Calculate actual offset if not first page
			if ( $offset > 0 )
			{
				$offset = ($offset - 1) * $pagination;
			}
  		}
  		else
  		{
  			$page_uri = null;
  			$offset = 0;
  		}

		// -------------------------------------
		// Get fields
		// -------------------------------------

		if (is_numeric($pagination))
		{	
			$data['fields'] = $CI->fields_m->get_fields($namespace, $pagination, $offset, $skips);
		}
		else
		{
			$data['fields'] = $CI->fields_m->get_fields($namespace, false, 0, $skips);
		}

		// -------------------------------------
		// Pagination
		// -------------------------------------

		if (is_numeric($pagination))
		{	
			$data['pagination'] = create_pagination(
											$pagination_uri,
											$CI->fields_m->count_fields($namespace),
											$pagination, // Limit per page
											$page_uri // URI segment
										);
		}
		else
		{ 
			$data['pagination'] = null;
		}

		// Allow view to inherit custom 'Add Field' uri
		$data['add_uri'] = isset($extra['add_uri']) ? $extra['add_uri'] : null;
		
		// -------------------------------------
		// Build Pages
		// -------------------------------------

		// Set title
		if (isset($extra['title']))
		{
			$CI->template->title($extra['title']);
		}

		$table = $CI->load->view('admin/partials/streams/fields', $data, true);
		
		if ($view_override)
		{
			// Hooray, we are building the template ourself.
			$CI->template->build('admin/partials/blank_section', array('content' => $table));
		}
		else
		{
			// Otherwise, we are returning the table
			return $table;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Field Assignments Table
	 *
	 * Easily create a table of fields in a certain namespace
	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	[mixed - pagination, either null for no pagination or a number for per page]
	 * @param	[null - pagination uri without offset]
	 * @param	[bool - setting this to true will take care of the $this->template business
	 * @param	[array - extra params (see below)]
	 * @param	[array - fields to skip]
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * title	- Title of the page header (if using view override)
	 *			$extra['title'] = 'Streams Sample';
	 * 
	 * buttons	- an array of buttons (if using view override)
	 *			$extra['buttons'] = array(
	 *				'label' 	=> 'Delete',
	 *				'url'		=> 'admin/streams_sample/delete/-entry_id-',
	 *				'confirm'	= true
	 *			);
	 *
	 * see docs for more explanation
	 */
	public function assignments_table($stream_slug, $namespace, $pagination = null, $pagination_uri = null, $view_override = false, $extra = array(), $skips = array())
	{
		$CI = get_instance();
		$data['buttons'] = (isset($extra['buttons']) and is_array($extra['buttons'])) ? $extra['buttons'] : array();

		// Get stream
		$stream = $this->stream_obj($stream_slug, $namespace);
		if ( ! $stream) $this->log_error('invalid_stream', 'assignments_table');

		if (is_numeric($pagination))
		{
			$segs = explode('/', $pagination_uri);
			$offset_uri = count($segs)+1;

	 		$offset = $pagination*($CI->uri->segment($offset_uri, 0)-1);

	 		// Negative value check
	 		if ($offset < 0) $offset = 0;
  		}
		else
		{
			$offset_uri = null;
			$offset = 0;
			$offset_uri = null;
		}

		// -------------------------------------
		// Get assignments
		// -------------------------------------

		if (is_numeric($pagination))
		{	
			$data['assignments'] = $CI->streams_m->get_stream_fields($stream->id, $pagination, $offset, $skips);
		}
		else
		{
			$data['assignments'] = $CI->streams_m->get_stream_fields($stream->id, null, 0, $skips);
		}

		// -------------------------------------
		// Get number of fields total
		// -------------------------------------
		
		$data['total_existing_fields'] = $CI->fields_m->count_fields($namespace);

		// -------------------------------------
		// Pagination
		// -------------------------------------

		if (is_numeric($pagination))
		{	
			$data['pagination'] = create_pagination(
											$pagination_uri,
											$CI->fields_m->count_assignments_for_stream($stream->id),
											$pagination,
											$offset_uri
										);
		}
		else
		{ 
			$data['pagination'] = false;
		}

		// Allow view to inherit custom 'Add Field' uri
		$data['add_uri'] = isset($extra['add_uri']) ? $extra['add_uri'] : null;

		// -------------------------------------
		// Build Pages
		// -------------------------------------

		// Set title
		if (isset($extra['title']))
		{
			$CI->template->title($extra['title']);
		}

		// Set no assignments message
		if (isset($extra['no_assignments_message']))
		{
			$data['no_assignments_message'] = $extra['no_assignments_message'];
		}

		$table = $CI->load->view('admin/partials/streams/assignments', $data, true);
		
		if ($view_override)
		{
			// Hooray, we are building the template ourself.
			$CI->template->build('admin/partials/blank_section', array('content' => $table, 'data' => $data));
		}
		else
		{
			// Otherwise, we are returning the table
			return $table;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Edit Form
	 *
	 * Edit the tabs and fields in a given form
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	string - the form slug
	 * @return	mixed - void or string
	 */
	public function edit_form($stream_slug, $namespace_slug, $form_slug, $view_override = true)
	{
		// These are.. available!
		$available_stream_fields = array();

		// Get our stream too
		$stream = $this->stream_obj($stream_slug, $namespace_slug);

		// Get stream_fields
		$stream_fields = $this->CI->streams_m->get_stream_fields($stream->id);

		// Get all our sheet
		$tabs = $this->CI->streams->forms->get_tabs($stream_slug, $namespace_slug, $form_slug, $available_stream_fields);

		// Get our form
		$form = $this->CI->db->select()->where('slug', $form_slug)->where('stream_id', $stream->id)->limit(1)->get('data_forms')->row(0);

		// Build the form
		$this->CI->template->append_js('streams/form_editor.js');

		$tabs = $this->CI->load->view('admin/partials/streams/edit_form', array('form' => $form, 'tabs' => $tabs, 'stream_fields' => $stream_fields, 'stream' => $stream, 'available_stream_fields' => $available_stream_fields), true);

		if ($view_override)
		{
			// Hooray, we are building the template ourself.
			$this->CI->template->build('admin/partials/blank_section', array('content' => $tabs));
		}
		else
		{
			// Otherwise, we are returning the html
			return $tabs;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * View Form
	 *
	 * Edit a view or create a new one
	 *
	 * This allows you to easily create a form that users can
	 * use to add new views to a stream.
	 *
	 * @param	string - stream slug
	 * @param	string - namespace
	 * @param 	string - method - new or edit. defaults to new
	 * @param 	string - uri to return to after success/fail
	 * @param 	[int - the view id if we are editing]
	 * @param	[bool - view override - setting this to true will build template]
	 * @param	[array - extra params (see below)]
	 * @return	mixed - void or string
	 *
	 * Extra parameters to pass in $extra array:
	 *
	 * title	- Title of the form header (if using view override)
	 *			$extra['title'] = 'Streams Sample';
	 * 
	 * show_cancel - bool. Show the cancel button or not?
	 * cancel_url - uri to link to for cancel button
	 *
	 * see docs for more.
	 */
	public function view_form($stream_slug, $namespace_slug, $method = 'new', $return, $view_id = null, $view_override = false, $extra = array())
	{
		$CI = get_instance();
		$data = array();
		$data['view'] = new stdClass;
		
		
		// We always need our stream
		$stream = $this->stream_obj($stream_slug, $namespace_slug);
		if ( ! $stream) $this->log_error('invalid_stream', 'form');

		
		// Get our list of available fields
		$stream_fields = $this->CI->streams_m->get_stream_fields($stream->id);

		// Make stream_fields mesh with the dropdown method
		$stream_fields_dropdown = array();

		foreach ($stream_fields as $stream_field) $stream_fields_dropdown[$stream_field->field_slug] = lang_label($stream_field->field_name);

		// -------------------------------------
		// Get the field if we have the view
		// -------------------------------------
		// We'll always work off the view.
		// -------------------------------------

		if ($method == 'edit' and is_numeric($view_id))
		{
			$views = $CI->streams->views->get_views($stream_slug, $namespace_slug);

			$view = $views[$view_id];
		}
		else
		{
			$view->is_locked = 'no';
			$view->name = null;
			$view->order_by = isset($stream_fields->{$stream->title_column}) ? $stream_fields->{$stream->title_column}->assign_id : null;
			$view->sort = 'ASC';
			$view->search = array();

			$view->view_assignments = array();
			$view->filters = array();
		}


		// -------------------------------------
		// Cancel Button
		// -------------------------------------

		$data['show_cancel'] = (isset($extra['show_cancel']) and $extra['show_cancel']) ? true : false;
		$data['cancel_uri'] = (isset($extra['cancel_uri'])) ? $extra['cancel_uri'] : null;

		
		// -------------------------------------
		// Process Data
		// -------------------------------------

		if ($CI->input->post())
		{
			$post_data = $CI->input->post();


			// Make sure we have a name - it's the only thing they could mess up really..
			if (! isset($post_data['name']) or empty($post_data['name'])) $post_data['name'] = 'Untitled view';

			
			// Build our view to update or insert
			$entry = array(
				'name' => $post_data['name'],
				'is_locked' => 'no',
				'stream_id' => $stream->id,
				'order_by' => $stream_fields->{$post_data['order']}->assign_id,
				'sort' => $post_data['sort'],
				'search' => isset($post_data['search']) ? $post_data['search'] : array(),
				);

			// Format search
			foreach ($entry['search'] as &$search)
			{
				$search = $stream_fields->{$search}->assign_id;
			}

			$entry['search'] = implode('|', $entry['search']);


			
			// Do what now?
			if ($method == 'new')
			{
				// Save
				$CI->db->insert('data_views', $entry);

				$view_id = $CI->db->insert_id();
			}
			else
			{
				// Update
				$CI->db->update('data_views', $entry, array('id' => $view_id));
			}


			
			// Clear view_assignments
			$CI->db->delete('data_view_assignments', array('view_id' => $view_id));

			// Add new ones
			if (isset($post_data['column']))
			{
				foreach ($post_data['column'] as $k=>$column)
				{
					$CI->db->insert(
						'data_view_assignments',
						array(
							'sort_order' => $k,
							'stream_id' => $stream->id,
							'view_id' => $view_id,
							'assign_id' => $column,
							)
						);
				}
			}



			// Clear view_filters
			$CI->db->delete('data_view_filters', array('view_id' => $view_id));

			// Add new ones
			if (isset($post_data['f-'.$stream->stream_slug.'-filter']))
			{
				foreach ($post_data['f-'.$stream->stream_slug.'-filter'] as $k=>$filter)
				{
					$CI->db->insert(
						'data_view_filters',
						array(
							'sort_order' => $k,
							'stream_id' => $stream->id,
							'view_id' => $view_id,
							'assign_id' => $stream_fields->{$filter}->assign_id,
							'condition' => $post_data['f-'.$stream->stream_slug.'-condition'][$k],
							'default_value' => $post_data['f-'.$stream->stream_slug.'-value'][$k],
							)
						);
				}
			}

	
			redirect($return);
		}


		// Set title
		if (isset($extra['title']))
		{
			$CI->template->title($extra['title']);
		}

		// Set the cancel URI. If there is no cancel URI, then we won't
		// have a cancel button.
		$data['cancel_uri'] = (isset($extra['cancel_uri'])) ? $extra['cancel_uri'] : null;


		// -------------------------------------
		// Add on $data
		// -------------------------------------

		$data['method'] = $method;
		$data['stream'] = $stream;
		$data['stream_fields'] = $stream_fields;
		$data['stream_fields_dropdown'] = $stream_fields_dropdown;
		$data['view'] = $view;
		$data['return'] = $return;


		// -------------------------------------
		// Build out our view
		// -------------------------------------

		$this->CI->template->append_js('streams/view_form.js');

		$form = $CI->load->view('admin/partials/streams/view_form', $data, true);
		
		if ($view_override)
		{
			// Hooray, we are building the template ourself.
			$CI->template->build('admin/partials/blank_section', array('content' => $form));
		}
		else
		{
			// Otherwise, we are returning the form
			return $form;
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Tear down assignment + field combo
	 *
	 * Usually we'd just delete the assignment,
	 * but we need to delete the field as well since
	 * there is a 1-1 relationship here.
	 *
	 * @param 	int - assignment id
	 * @param 	bool - force delete field, even if it is shared with multiple streams
	 * @return 	bool - success/fail
	 */
	public function teardown_assignment_field($assign_id, $force_delete = false)
	{
		$CI = get_instance();

		// Get the assignment
		$assignment = $CI->db->limit(1)->where('id', $assign_id)->get(ASSIGN_TABLE)->row();

		if ( ! $assignment)
		{
			$this->log_error('invalid_assignment', 'teardown_assignment_field');
		}
		
		// Get stream
		$stream = $CI->streams_m->get_stream($assignment->stream_id);

		// Get field
		$field = $CI->fields_m->get_field($assignment->field_id);

		// Delete the assignment
		if ( ! $CI->streams_m->remove_field_assignment($assignment, $field, $stream))
		{
			$this->log_error('invalid_assignment', 'teardown_assignment_field');
		}
		
		// Remove the field only if unlocked and assigned once
		if ($field->is_locked == 'no' or $CI->fields_m->count_assignments($assignment->field_id) == 1 or $force_delete)
		{
			// Remove the field
			return $CI->fields_m->delete_field($field->id);
		}
	}

}
