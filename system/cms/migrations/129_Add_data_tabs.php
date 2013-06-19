<?php defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Migration 129: Add data_tabs
 * 
 * Added June 17th, 2013
 */
class Migration_Add_data_tabs extends CI_Migration
{
  public function up()
	{
		// Add meta robots index
		if ( ! $this->db->table_exists('data_tabs'))
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
				'form_id' => array(
					'type' => 'INT',
					'constraint' => 11,
					),
				'title' => array(
					'type' => 'VARCHAR',
					'constraint' => 60,
					),
				);

			$this->dbforge->add_field($fields);

			$this->dbforge->add_key('id', TRUE);

			$this->dbforge->create_table('data_tabs');
		}
	}
	
	public function down()
	{
		return true;
	}
}
