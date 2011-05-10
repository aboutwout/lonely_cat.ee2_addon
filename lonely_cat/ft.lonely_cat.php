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

	  $options = array();
		$cats = $this->_fetch_categories($data);

    $cat_group = NULL;
 		 		
// 		 debug($cats);		
 		 		
    $options['0'] = $this->EE->lang->line('none');
		foreach($cats as $val)
		{
			$indent = ($val['5'] != 1) ? repeater(NBS.NBS.NBS, $val['5']) : '';
			$options[$val['3']][$val['0']] = $indent.$val['1'];
		}

	  return form_dropdown($this->field_name, $options, $data);
		
	}
	
	function validate()
	{
	  return TRUE;
	}
	
	function save($data=0)
	{	  
	  $cats = !$data ? array() : (array) $data;
	  
	  $this->EE->api_channel_categories->cat_parents = $cats;

    return $data;
	}
	
	function _fetch_categories($data)
	{ 
    $this->EE->load->library('api');
    $this->EE->api->instantiate('channel_categories');
	  
	  return $this->EE->api_channel_categories->categories;
	}
	
}
// END Lonely_cat_ft class

/* End of file ft.lonely_cat.php */
/* Location: ./system/expressionengine/third_party/lonely_cat/ft.lonely_cat.php */