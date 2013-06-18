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
		$views = $this->CI->db->select()->where('stream_id', $stream->id)->order_by('is_locked', 'DESC')->order_by('name', 'ASC')->get('data_views')->result();


		// Get our view stream_fields as well
		$view_stream_fields = $this->CI->db->select()->where('stream_id', $stream->id)->order_by('view_id', 'ASC')->order_by('sort_order', 'ASC')->get('data_view_assignments')->result();


		
		// Put our view stream_fields in place
		foreach ($views as &$view) {

			// Find stream_fields for this tab
			$view->stream_fields = array();

			// We'll use this later
			$view->stream_fields_field_slugs = array();

			foreach ($view_stream_fields as $view_stream_field)
			{
				// Only fot this tab..
				if ($view_stream_field->view_id != $view->id) continue;

				// Now load on the stream_field data
				foreach ($stream_fields as $stream_field)
				{
					if ($view_stream_field->assign_id == $stream_field->assign_id)
					{
						// Never know when we'll need this
						$view->stream_fields[] = $stream_field;

						// We'll need this shortly
						$view->stream_fields_field_slugs[] = $stream_field->field_slug;
					}
				}
			}


			// Build the query string
			$view->query_string = '?'.$stream_slug.'-view='.$view->id;

			// Do filters

			// Tack on the columns
			$view->query_string .= '&'.$stream_slug.'-columns='.implode('|', $view->stream_fields_field_slugs);
		}


		// Yaaaay
		return $views;
	}
}