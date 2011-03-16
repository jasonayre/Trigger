<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Trigger Groups Driver
 *
 * @package		Trigger
 * @author		Addict Add-ons Dev Team
 * @copyright	Copyright (c) 2010 - 2011, Addict Add-ons
 * @license		
 * @link		
 */
class Driver_groups
{
	public $driver_version		= "0.9";
	
	public $driver_slug			= "groups";

	// --------------------------------------------------------------------------

	function __construct()
	{
		$this->EE =& get_instance();		
	}
	
	// --------------------------------------------------------------------------
	
	/**
	 * New Group
	 *
	 * Creates a group and a 
	 *
	 * @access	public
	 * @param	string - name of the group
	 * @param	[string] - default group y/n
	 * @return	string
	 */	
	public function _comm_new($group_name, $is_site_default='n')
	{
		if(!$group_name):
		
			return "no group name provided";
		
		endif;
		
		// Does it exist already?
		$query = $this->EE->db->limit(1)->get_where('template_groups', array('group_name' => $group));

		if($query->num_rows() == 1):
		
			return "template group already exists";
		
		endif;
		
		// Create the group
		$group_data['is_site_default'] = $is_site_default;
		$group_data['site_id'] = $this->EE->config->item('site_id');
		$group_data['group_name'] = $group_name;
		
		$this->EE->load->model('template_model');
	
		$group_id = $this->EE->template_model->create_group($group_data);
	
		// Create the index template
		$template_data['site_id'] 				= $this->EE->config->item('site_id');
		$template_data['template_name']			= 'index';
		$template_data['save_template_file']	= 'n';
		$template_data['template_data'] 		= '';
		$template_data['template_type'] 		= 'webpage';
		$template_data['group_id'] 				= $group_id;
		
		if(!$this->EE->db->insert('templates', $template_data)):
		
			return "group created. error creating template";
		
		else:
		
			return "group and index template created";
		
		endif;
	}

	// --------------------------------------------------------------------------

	/**
	 * Delete a template
	 */
	public function _comm_delete($group)
	{
		if(!$group):
		
			return "no group provided";
		
		endif;
		
		// Get the group ID
		$query = $this->EE->db->limit(1)->where('group_name', strtolower($group))->get('template_groups');
		
		if($query->num_rows() == 0):
		
			return "group not found";
		
		endif;
		
		$row = $query->row();
		$group_id = $row->group_id;
		
		// Delete groups
		$this->EE->db
					->where('group_id', $group_id)
					->where('site_id', $this->EE->config->item('site_id'))
					->delete('template_groups');
					
		// Delete templates
		$this->EE->db
					->where('group_id', $group_id)
					->where('site_id', $this->EE->config->item('site_id'))
					->delete('templates');
		
		return "group and ".$this->EE->db->affected_rows()." templates deleted";
	}

	// --------------------------------------------------------------------------	
	
	/**
	 * List the groups
	 */
	public function _comm_list()
	{
		// Get our snippets
		$db_obj = $this->EE->db->where('site_id', $this->EE->config->item('site_id'))->get('template_groups');
		
		$total = $db_obj->num_rows();
		
		if($total == 0):
		
			return "no groups";
		
		endif;
		
		$out = TRIGGER_BUFFER."\n";
		
		foreach($db_obj->result() as $group):
		
			$out .= $group->group_name."\n";
		
		endforeach;
		
		return $out .= TRIGGER_BUFFER;
	}

	// --------------------------------------------------------------------------

	/**
	 * Delete all the groups
	 *
	 * @access	public
	 * @return	string
	 */	
	public function _comm_delete_all()
	{
		// Check for access
		if ( ! $this->EE->cp->allowed_group('can_access_design') OR ! $this->EE->cp->allowed_group('can_admin_templates')):

			return trigger_lang('trigger_no_access');
			
		endif;

		$db_obj = $this->EE->db->where('site_id', $this->EE->config->item('site_id'))->get('template_groups');
	
		if($db_obj->num_rows() == 0):
		
			return "no groups";
		
		endif;
	
		// Go through the groups and delete all the templates
		
		// NEED TO BE ABLE TO DELETE THE FILES / FOLDERS AS WELL
		
		$this->EE->db
					->where('site_id', $this->EE->config->item('site_id'))
					->delete('snippets');
		
		return trigger_lang('all_snippets_deleted');
	}

}

/* End of file groups.driver.php */