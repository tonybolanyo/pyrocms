<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 132: Add data_view_assignments
 * 
 * Added June 17th, 2013
 */
class Migration_Add_data_view_assignments extends CI_Migration
{
  public function up()
	{
		// Add meta robots index
		if ( ! $this->db->table_exists('data_view_assignments'))
		{
			$fields = array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11, 
					'unsigned' => TRUE,
					'auto_increment' => TRUE
					),
				'sort_order' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				'stream_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				'view_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				'assign_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				);

			$this->dbforge->add_field($fields);

			$this->dbforge->add_key('id', TRUE);

			$this->dbforge->create_table('data_view_assignments');
		}
	}
	
	public function down()
	{
		return true;
	}
}
