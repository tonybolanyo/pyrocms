<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Views Driver
 *
 * Contains functions that allow for
 * constructing / manipulating PyrCMS streams
 * views data.
 *
 * @author  	AI Web Systems, Inc. - Ryan Thompson
 * @package  	PyroCMS\Core\Libraries\Streams\Drivers
 */ 
 
class Streams_views extends CI_Driver {

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
	 * Insert Form
	 *
	 * Insert a view
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	array - the view array
	 * @return	mixed - bool or array
	 *
	 */
	public function insert_view($stream_slug, $namespace_slug, $view)
	{
		// We always need our stream		
		if (! ($stream = $this->stream_obj($stream_slug, $namespace_slug))) $this->log_error('invalid_stream', 'view');


		// Insert that betch
		$this->CI->db->insert(
			'data_views',
			array(
				'stream_id' => $stream->id,
				'name' => $view['name'],
				'slug' => $view['slug'],
				)
			);

		// Save der ID
		$id = $this->CI->db->insert_id();



		// If we have tabs to install - let's do that too!
		if (isset($view['tabs']))
		{
			// Install each one
			foreach ($view['tabs'] as $tab)
			{
				$this->insert_tab($stream_slug, $namespace_slug, $view['slug'], $tab);
			}
		}



		// Nice
		return $id;
	}

	// --------------------------------------------------------------------------

	/**
	 * Get Views
	 *
	 * Get's views for a stream
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @return	mixed - bool or array
	 *
	 */
	public function get_views($stream_slug, $namespace_slug)
	{
		// We always need our stream		
		if (! ($stream = $this->stream_obj($stream_slug, $namespace_slug))) $this->log_error('invalid_stream', 'view');


		// Get stream_fields
		$stream_fields = $this->CI->streams_m->get_stream_fields($stream->id);


		// Get our views
		$db_views = $this->CI->db->select()->where('stream_id', $stream->id)->order_by('is_locked', 'DESC')->order_by('name', 'ASC')->get('data_views')->result();


		// Get our view_assignments as well
		$view_assignments = $this->CI->db->select()->where('stream_id', $stream->id)->order_by('view_id', 'ASC')->order_by('sort_order', 'ASC')->get('data_view_assignments')->result();


		// Get our view stream_fields as well
		$view_filters = $this->CI->db->select()->where('stream_id', $stream->id)->order_by('view_id', 'ASC')->order_by('sort_order', 'ASC')->get('data_view_filters')->result();


		// Index on ID
		$views = array();

		foreach ($db_views as $view)
		{
			$views[$view->id] = $view;
		}


		
		// Put things together
		foreach ($views as &$view) {

			// Find view_assignments for this tab
			$view->view_assignments = array();

			// Find view_filters for this tab
			$view->view_filters = array();

			// Expand these
			$view->search = empty($view->search) ? array() : explode('|', $view->search);



			// Get slugs for single items
			foreach ($stream_fields as $stream_field)
			{
				// Order_by
				if ($view->order_by == $stream_field->assign_id) $view->order_by = $stream_field->field_slug;
			}



			// Get slugs for search
			foreach ($view->search as &$search)
			{
				// Now load on the stream_field data
				foreach ($stream_fields as $stream_field)
				{
					if ($search == $stream_field->assign_id) $search = $stream_field->field_slug;
				}
			}



			// Get slugs for view_assignments (columns)
			foreach ($view_assignments as $view_assignment)
			{
				// Only fot this view..
				if ($view_assignment->view_id != $view->id) continue;

				// Now load on the stream_field data
				foreach ($stream_fields as $stream_field)
				{
					if ($view_assignment->assign_id == $stream_field->assign_id)
					{
						// Never know when we'll need this
						$view->view_assignments[] = $stream_field->field_slug;
					}
				}
			}



			// Get slugs for advanced_filters filters
			foreach ($view_filters as &$view_filter)
			{
				// Only fot this view..
				if ($view_filter->view_id != $view->id) continue;

				// Now load on the stream_field data
				foreach ($stream_fields as $stream_field)
				{
					if ($view_filter->assign_id == $stream_field->assign_id)
					{
						$view_filter->field_slug = $stream_field->field_slug;

						$view->view_filters[] = $view_filter;
					}
				}
			}



			// Build the query string
			$view->query_string = '?'.$stream_slug.'-view='.$view->id;

			// Do filters

			// Tack on the columns
			if (! empty($view->view_assignments))
			{
				$view->query_string .= '&'.$stream_slug.'-column[]='.implode('&'.$stream_slug.'-column[]=', $view->view_assignments);
			}

			// Tack on ordering
			$view->query_string .= '&order-'.$stream_slug.'='.$view->order_by;

			// Tack on sorting
			$view->query_string .= '&sort-'.$stream_slug.'='.$view->sort;

			// Tack on filters
			foreach ($view->view_filters as $view_filter)
			{
				$view->query_string .= '&f-'.$stream_slug.'-filter[]='.$view_filter->field_slug.'&f-'.$stream_slug.'-condition[]='.$view_filter->condition.'&f-'.$stream_slug.'-value[]='.$this->CI->parser->parse_string($view_filter->default_value, $this->CI, true);
			}
		}



		// Yaaaay
		return $views;
	}

	// --------------------------------------------------------------------------

	/**
	 * Delete a view
	 *
	 * @access	public
	 * @param	int - view id
	 * @return	object
	 */
	public function delete_view($view_id)
	{
		$this->CI->db->delete('data_views', array('id' => $view_id));
		$this->CI->db->delete('data_view_assignments', array('view_id' => $view_id));
		$this->CI->db->delete('data_view_filters', array('view_id' => $view_id));

		return true;
	}
}