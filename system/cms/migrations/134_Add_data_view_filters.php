<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 134: Add data_view_filters
 * 
 * Added June 17th, 2013
 */
class Migration_Add_data_view_filters extends CI_Migration
{
  public function up()
	{
		// Add meta robots index
		if ( ! $this->db->table_exists('data_view_filters'))
		{
			$fields = array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11, 
					'unsigned' => TRUE,
					'auto_increment' => TRUE
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
				'condition' => array(
					'type' => 'VARCHAR',
					'constraint' => 30,
					),
				'default_value' => array(
					'type' => 'TEXT',
					'null' => true,
					),
				);

			$this->dbforge->add_field($fields);

			$this->dbforge->add_key('id', TRUE);

			$this->dbforge->create_table('data_view_filters');
		}
	}
	
	public function down()
	{
		return true;
	}
}