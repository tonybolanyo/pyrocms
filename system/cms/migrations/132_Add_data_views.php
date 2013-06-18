<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 131: Add data_forms
 * 
 * Added June 17th, 2013
 */
class Migration_Add_data_views extends CI_Migration
{
  public function up()
	{
		// Add meta robots index
		if ( ! $this->db->table_exists('data_views'))
		{
			$fields = array(
				'id' => array(
					'type' => 'INT',
					'constraint' => 11, 
					'unsigned' => TRUE,
					'auto_increment' => TRUE
					),
				'name' => array(
					'type' => 'VARCHAR',
					'constraint' => 60,
					),
				'is_locked' => array(
					'type' => 'ENUM',
					'constraint' => array('yes', 'no'),
					),
				'stream_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				'order_by' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				'sort' => array(
					'type' => 'ENUM',
					'constraint' => array('ASC', 'DESC'),
					),
				'limit' => array(
					'type' => 'INT',
					'constraint' => 3,
					),
				);

			$this->dbforge->add_field($fields);

			$this->dbforge->add_key('id', TRUE);

			$this->dbforge->create_table('data_views');
		}
	}
	
	public function down()
	{
		return true;
	}
}
