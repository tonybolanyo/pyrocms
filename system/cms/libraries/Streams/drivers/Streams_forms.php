<?php defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Forms Driver
 *
 * Contains functions that allow for
 * constructing / manipulating PyrCMS streams
 * form data.
 *
 * @author  	AI Web Systems, Inc. - Ryan Thompson
 * @package  	PyroCMS\Core\Libraries\Streams\Drivers
 */ 
 
class Streams_forms extends CI_Driver {

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
	 * Insert a form
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	array - the form array
	 * @return	mixed - bool or array
	 *
	 */
	public function insert_form($stream_slug, $namespace_slug, $form)
	{
		// We always need our stream		
		if (! ($stream = $this->stream_obj($stream_slug, $namespace_slug))) $this->log_error('invalid_stream', 'form');


		// Insert that betch
		$this->CI->db->insert(
			'data_forms',
			array(
				'stream_id' => $stream->id,
				'slug' => $form['slug'],
				)
			);

		// Save der ID
		$id = $this->CI->db->insert_id();



		// If we have tabs to install - let's do that too!
		if (isset($form['tabs']) and is_array($form['tabs']))
		{
			// Install each one
			foreach ($form['tabs'] as $tab)
			{
				$this->insert_tab($stream_slug, $namespace_slug, $form['slug'], $tab);
			}
		}



		// Nice
		return $id;
	}

	// --------------------------------------------------------------------------

	/**
	 * Insert Tab
	 *
	 * Insert a tab and it's fields
	 *
	 * @param	string - the form slug
	 * @param	array - the tab array
	 * @return	mixed - bool or array
	 *
	 */
	public function insert_tab($stream_slug, $namespace_slug, $form_slug, $tab)
	{
		// We always need our stream		
		if (! ($stream = $this->stream_obj($stream_slug, $namespace_slug))) $this->log_error('invalid_stream', 'form');


		// Get stream_fields
		$stream_fields = $this->CI->streams_m->get_stream_fields($stream->id);


		// Get our form
		$form = $this->CI->db->select()->where('slug', $form_slug)->where('stream_id', $stream->id)->limit(1)->get('data_forms')->row(0);



		// Insert our actual tab
		$this->CI->db->insert(
			'data_tabs',
			array(
				'sort_order' => $tab['sort_order'],
				'stream_id' => $stream->id,
				'form_id' => $form->id,
				'name' => $tab['name'],
				)
			);

		// Mmyyesss
		$id = $this->CI->db->insert_id();



		// Install fields
		foreach ($tab['fields'] as $k => $tab_field)
		{
			// Find the field stream_field
			foreach ($stream_fields as $stream_field)
			{
				if ($tab_field == $stream_field->field_slug)
				{
					$this->CI->db->insert(
						'data_tab_assignments',
						array(
							'sort_order' => $k,
							'stream_id' => $stream->id,
							'form_id' => $form->id,
							'tab_id' => $id,
							'assign_id' => $stream_field->assign_id,
							)
						);
				}
			}
		}
	}

	// --------------------------------------------------------------------------

	/**
	 * Get Tabs
	 *
	 * Get's tabs for a stream form
 	 *
	 * @param	string - the stream slug
	 * @param	string - the stream namespace slug
	 * @param	string - the form slug
	 * @param	array - a referenced array of skip items
	 * @return	mixed - bool or array
	 *
	 */
	public function get_tabs($stream_slug, $namespace_slug, $form_slug, &$not_in_form = array())
	{
		// Get readeh
		$tabs = array();
		
		
		// We always need our stream		
		if (! ($stream = $this->stream_obj($stream_slug, $namespace_slug))) $this->log_error('invalid_stream', 'form');


		// Get stream_fields
		$stream_fields = $this->CI->streams_m->get_stream_fields($stream->id);


		// Get our form too
		$form = $this->CI->db->select()->where('slug', $form_slug)->where('stream_id', $stream->id)->limit(1)->get('data_forms')->row(0);

		if (! isset($form->id)) return false;


		// Get our tabs
		$form_tabs = $this->CI->db->select()->where('form_id', $form->id)->order_by('sort_order', 'ASC')->get('data_tabs')->result();


		// Get our tab stream_fields as well
		$form_tab_stream_fields = $this->CI->db->select()->where('form_id', $form->id)->order_by('tab_id', 'ASC')->order_by('sort_order', 'ASC')->get('data_tab_assignments')->result();


		
		// Put our tabs in place
		foreach ($form_tabs as $k => $form_tab) {

			// Find stream_fields for this tab
			$tab_fields = array();

			foreach ($form_tab_stream_fields as $form_tab_stream_field)
			{
				// Only fot this tab..
				if ($form_tab_stream_field->tab_id != $form_tab->id) continue;

				foreach ($stream_fields as $stream_field)
				{
					if ($form_tab_stream_field->assign_id == $stream_field->assign_id)
					{
						$tab_fields[] = $stream_field->field_slug;
					}
				}
			}


			// Build er on top
			$tabs[] = array(
				'name' => $form_tab->name,
				'id' => $form_tab->id,
				'slug' => slugify($form_tab->name).'-tab',
				'fields' => $tab_fields,
				);
		}



		/**
		 * Now that we know what's in the tabs / form
		 * we figure out what's NOT and add it
		 * to $not_in_form
		 */

		foreach ($stream_fields as $stream_field)
		{
			$in_form = false;

			foreach ($tabs as $tab)
			{
				if (in_array($stream_field->field_slug, $tab['fields'])) $in_form = true;
			}

			// Not in the form? Skip it..
			if (! $in_form) $not_in_form[$stream_field->assign_id] = $stream_field->field_slug;
		}



		// Yaaaay
		return $tabs;
	}
}