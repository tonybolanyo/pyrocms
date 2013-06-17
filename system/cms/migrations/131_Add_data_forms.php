<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 131: Add data_forms
 * 
 * Added June 17th, 2013
 */
class Migration_Add_data_forms extends CI_Migration
{
  public function up()
	{
		// Add meta robots index
		if ( ! $this->db->table_exists('data_forms'))
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
				'title' => array(
					'type' => 'VARCHAR',
					'constraint' => 60,
					),
				'slug' => array(
					'type' => 'VARCHAR',
					'constraint' => 60,
					),
				);

			$this->dbforge->add_field($fields);

			$this->dbforge->add_key('id', TRUE);

			$this->dbforge->create_table('data_forms');
		}
	}
	
	public function down()
	{
		return true;
	}
}
