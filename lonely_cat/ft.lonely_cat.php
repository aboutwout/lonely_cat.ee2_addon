<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

// Include the config file
require PATH_THIRD.'lonely_cat/config.php';

/**
 * @package		Lonely Cat
 * @subpackage	ThirdParty
 * @category	Modules
 * @author		Wouter Vervloet
 * @link		http://www.baseworks.nl/
 */
class Lonely_cat_ft extends EE_Fieldtype {

	var $info = array(
		'name'		=> LONCAT_NAME,
		'version'	=> LONCAT_VERSION
	);
	
	// --------------------------------------------------------------------
	
	function display_field($data)
	{
	  $this->EE->lang->loadfile('lonely_cat');

    $this->EE->load->library('api');
    $this->EE->api->instantiate(array('channel_categories', 'channel_structure'));

    $this->channel_id = $this->EE->input->get_post('channel_id');

    // If no category is set, check if one has
    // already been set in the categories array
    if ( ! $data AND isset($this->EE->api_channel_categories->categories))
    {
      foreach ($this->EE->api_channel_categories->categories as $cat_id => $cat)
      {
        if ($cat[4])
        {
          $data = $cat[0];
          break;
        }
      }
    }
    
    // If there is no category set, fetch the default category if set.
    if ( ! $data && $this->channel_id)
    {
      $data = $this->EE->api_channel_structure->get_channel_info($this->channel_id)->row('deft_category');
    }
  
    $tmp = array();
	  $options = array();
		$cats = $this->_fetch_categories();  

    $cat_group = NULL;
 		 		
		foreach($cats as $val)
		{
			$indent = ($val['parent_id'] != 1) ? repeater(NBS.NBS.NBS, $val['parent_id']) : '';
			$tmp[$val['cat_group_name']][$val['cat_id']] = $indent.$val['cat_name'];
		}
		
		$options[''] = $this->EE->lang->line('loncat_none');
		
		if(count($tmp) === 1)
		{
		  foreach($tmp[$val['cat_group_name']] as $cat_id => $cat)
		  {
  		  $options[$cat_id] = $cat;
		  }
		}
		else
		{
		  $options = array_merge($options, $tmp);
		}

	  return form_dropdown($this->field_name, $options, $data);
		
	}
	
	function replace_category_name($data, $params = array(), $tagdata = FALSE)
	{ 
    if ( ! $data) return '';
    
    $category_info = $this->_get_category($data);
    extract($category_info);

		return $cat_name;
	}

	function replace_category_id($data, $params = array(), $tagdata = FALSE)
	{
    if ( ! $data) return '';
		return $data;
	}

	function replace_category_group($data, $params = array(), $tagdata = FALSE)
	{    
    if ( ! $data) return '';
    
    $category_info = $this->_get_category($data);
    extract($category_info);

		return $group_id;
	}
	
	function replace_category_parent($data, $params = array(), $tagdata = FALSE)
	{    
    if ( ! $data) return '';
    
    $category_info = $this->_get_category($data);
    extract($category_info);

		return $parent_id;
	}
	
	function replace_category_url_title($data, $params = array(), $tagdata = FALSE)
	{    
    if ( ! $data) return '';
    
    $category_info = $this->_get_category($data);
    extract($category_info);

		return $cat_url_title;
	}
	
	function replace_category_description($data, $params = array(), $tagdata = FALSE)
	{    
    if ( ! $data) return '';
    
    $category_info = $this->_get_category($data);
    extract($category_info);

		return $cat_description;
	}
	
	function replace_category_image($data, $params = array(), $tagdata = FALSE)
	{    
    if ( ! $data) return '';
    
    $category_info = $this->_get_category($data);
    extract($category_info);

		return $cat_image;
	}
	
	function validate()
	{
	  return TRUE;
	}
	
	function save($data=0)
	{	  
	  $cats = ! $data ? array() : (array) $data;
	  
	  $this->EE->api_channel_categories->cat_parents = $cats;

    return $data;
	}
	
	public function display_settings($data=array())
	{
		$this->EE->lang->loadfile('lonely_cat');
		$this->EE->table->add_row(
			lang('apply_existing_entries', 'apply_existing_entries'),
			form_checkbox('apply_existing_entries', 'y', 0).NBS.'Yes'
		);
	}
	
	public function save_settings($data)
	{
	  if ($this->EE->input->post('apply_existing_entries') == 'y')
	  {
	    $this->apply_existing_entries = TRUE;
	  }
	}
	
	public function post_save_settings($data=array())
	{
	  if ($this->apply_existing_entries === TRUE)
	  {
	    $channels = array();
	    $group_id = $data['group_id'];
	    $field_id = $data['field_id'];
	    $field_name = 'field_id_'.$field_id;
	    
      $site_id = $data['site_id'];
      
      $c_query = $this->EE->db
        ->select('channel_id')
        ->where('field_group', $group_id)
        ->where('site_id', $site_id)
        ->get('channels');
      foreach ($c_query->result() as $row)
      {
        $channels[] = $row->channel_id;
      }
      
      $e_query = $this->EE->db
        ->from('channel_data cd')
        ->join('category_posts cp', 'cd.entry_id=cp.entry_id')
        ->select('cd.'.$field_name.', cp.*')
        ->where_in('cd.channel_id', $channels)
        ->where('cd.site_id', $site_id)
        ->get();
        
      $catdata = array();
      
      foreach ($e_query->result() as $row)
      {
        if ($row->$field_name == $row->cat_id) continue;
        
        $catdata[] = array(
          'entry_id' => $row->entry_id,
          $field_name => $row->cat_id
        );
      }
      
      if (count($catdata) > 0)
      {
        $this->EE->db->update_batch('channel_data', $catdata, 'entry_id');
        $this->EE->session->set_flashdata('message_success', $this->EE->db->affected_rows().' entries updated.');
      }
      else
      {
        $this->EE->session->set_flashdata('message_success', '0 entries updated.');
      }
	  }
	}
	
	function _fetch_categories()
	{ 
	  
	  $cats = array();
	  
    if (isset($this->EE->safecracker))
    {
      $cat_group = $this->EE->safecracker->channel['cat_group'];
      $cats = $this->EE->api_channel_categories->category_tree($cat_group);
    }
    elseif (is_array($this->EE->api_channel_categories->categories) AND count($this->EE->api_channel_categories->categories) > 0)
	  {
	    $cats = $this->EE->api_channel_categories->categories;
	  }

    foreach ($cats as $cat_id => $cat)
    {
      $cats[$cat[0]] = array(
        'cat_id' => $cat[0],
        'cat_name' => $cat[1],
        'cat_group_id' => $cat[2],
        'cat_group_name' => $cat[3],
        'parent_id' => $cat[5]
      );
    }
  
	  
	  return $cats;

	}
	
	function _get_category($cat_id=0)
	{
	  $this->EE->api->instantiate('channel_categories');
	  debug($this->EE->api_channel_categories);
    
	  if ( ! isset($this->EE->session->cache[__CLASS__][$cat_id]))
    {
      $query = $this->EE->db->where('cat_id', $cat_id)->get('categories');
      
      if ($query->num_rows() > 0)
      {
        $this->EE->session->cache[__CLASS__][$cat_id] = $query->row_array();
      }
    }
    
    return $this->EE->session->cache[__CLASS__][$cat_id];
    
	}
	
}
// END Lonely_cat_ft class

/* End of file ft.lonely_cat.php */
/* Location: ./system/expressionengine/third_party/lonely_cat/ft.lonely_cat.php */