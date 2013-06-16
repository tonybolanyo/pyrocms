<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 125: Add robots meta tag
 *
 * This will let allow to set the robots meta tag
 * 
 * Added April 20th, 2013
 */
class Migration_Add_field_map_column extends CI_Migration
{
  public function up()
	{
		// Add meta robots index
		if ( ! $this->db->field_exists('field_map', 'data_fields'))
		{
			$this->dbforge->add_column('data_fields', array(
				'field_map' => array(
					'type' => 'text'
				)
			));
		}
	}
	
	public function down()
	{
		return true;
	}
}
