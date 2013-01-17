<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require PATH_THIRD.'ds_url_title/config.php';

/**
 * DS Url Title Extension class
 *
 * @author          Dion Snoeijen | Diovisuals.com (info@diovisuals.com)
 * @license         http://creativecommons.org/licenses/by-sa/3.0/
 */
class Ds_url_title_ext
{
    /**
	 * Extension settings
	 *
	 * @access      public
	 * @var         array
	 */
	public $settings = array();

	/**
	 * Extension name
	 *
	 * @access      public
	 * @var         string
	 */
	public $name = DS_URL_TITLE_NAME;

	/**
	 * Extension version
	 *
	 * @access      public
	 * @var         string
	 */
	public $version = DS_URL_TITLE_VERSION;

	/**
	 * Extension description
	 *
	 * @access      public
	 * @var         string
	 */
	public $description = 'Append string to url title. For example: .html';
    
    /**
	 * Do settings exist?
	 *
	 * @access      public
	 * @var         bool
	 */
	public $settings_exist = TRUE;

	/**
	 * Documentation link
	 *
	 * @access      public
	 * @var         string
	 */
	public $docs_url = DS_URL_TITLE_DOCS;

	// --------------------------------------------------------------------

	/**
	 * EE Instance
	 *
	 * @access      private
	 * @var         object
	 */
	private $EE;

	/**
	 * Current class name
	 *
	 * @access      private
	 * @var         string
	 */
	private $class_name;

	/**
	 * Current site id
	 *
	 * @access      private
	 * @var         int
	 */
	private $site_id;

	/**
	 * Default settings
	 *
	 * @access      public
	 * @var         array
	 */
	private $default_settings = array(
		'delimiter' => '.',
		'append_string' => 'html'
	);
	
    /**
	 * Legacy Constructor
	 *
	 * @see         __construct()
	 */
    function Ds_url_title($settings = array())
    {
    	$this->__construct($settings);
    }


	/**
	 * PHP 5 Constructor
	 *
	 * @access      public
	 * @param       mixed     Array with settings or FALSE
	 * @return      null
	 */
    function __construct($settings = array())
    {
    	// Get global instance
        $this->EE =& get_instance();

        // Get site id
		$this->site_id = $this->EE->config->item('site_id');

		// Set Class name
		$this->class_name = ucfirst(get_class($this));

		// Set settings
        $this->settings = $this->_get_site_settings($settings);

        // Define the package path
		$this->EE->load->add_package_path(PATH_THIRD.'ds_url_title');
    }

    // --------------------------------------------------------------------

	/**
	 * Settings form
	 *
	 * @access      public
	 * @param       array     Current settings
	 * @return      string
	 */
	function settings_form($current)
	{
		//$data = array();

		// -------------------------
		// Load helper
		// -------------------------
		$this->EE->load->helper('form');

		// -------------------------
		// Get current settings for this site
		// -------------------------
		$data['current'] = $this->_get_site_settings($current);
		
		
		// -------------------------
		// Add this extension's name to display data
		// -------------------------
		$data['name'] = DS_URL_TITLE_CLASS_NAME;
		

		// -------------------------
		// Set breadcrumb
		// -------------------------
		$this->EE->cp->set_breadcrumb('#', DS_URL_TITLE_NAME);

		// -------------------------
		// Load view
		// -------------------------
		return $this->EE->load->view('ext_settings', $data, TRUE);
	}
    
    /**
	 * Save extension settings
	 *
	 * @return      void
	 */
	public function save_settings()
	{
		// -------------------------
		// Get current settings from DB
		// -------------------------
		$settings = $this->_get_current_settings();
		if ( ! is_array($settings))
		{
			$settings = array($this->site_id => $this->default_settings);
		}

		// -------------------------
		// Loop through default settings, check
		// for POST values, fallback to default
		// -------------------------
		foreach ($this->default_settings AS $key => $val)
		{
			if (($post_val = $this->EE->input->post($key)) !== FALSE)
			{
				$val = $post_val;
			}

			if (is_array($val))
			{
				$val = array_filter($val);
			}

			$settings[$this->site_id][$key] = $val;
		}

		// -------------------------
		// Save serialized settings
		// -------------------------
		$this->EE->db->update('extensions', array('settings' => serialize($settings)), "class = '".$this->class_name."'");
	}
    
    // -------------------------
    //  Activate Extension
    // -------------------------
    function activate_extension()
    {
		$data = array(
			'class'		=> $this->class_name,
			'method'	=> 'entry_submission_end', // method called in $this
			'hook'		=> 'entry_submission_end', 
			'priority'	=> 1,
			'version'	=> DS_URL_TITLE_VERSION,
			'enabled'	=> 'y',
			'settings'	=> serialize($this->default_settings)
		);
		
		$this->EE->db->insert('extensions', $data);
	}
    
    
    // -------------------------
    //  Disable Extension
    // -------------------------
    function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}
    
    // -------------------------
    //  Update Extension
    // -------------------------
    function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
		
		if ($current < '1.0')
		{
			// Update to version 1.0
		}
		
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->update('extensions',	array('version' => $this->version));
	}

    // END    
    // ============================================================================  

    function entry_submission_end($entry_id, $meta, $data)
    {	
    	// -------------------------
    	//	Get delimiter and append string
    	// -------------------------
    	$settings = $this->_get_current_settings();
    	// -------------------------
    	//	Set defaults
    	// -------------------------
   		$delimiter = $settings['delimiter'];
   		$append_string = $settings['append_string'];

   		// -------------------------
   		//	Settings overruled?
   		// -------------------------
    	if(isset($settings[1])) 
    	{
    		$delimiter = $settings[1]['delimiter'];
    		$append_string = $settings[1]['append_string'];
    	}

    	// -------------------------
    	// Get the current url title
    	// -------------------------
    	$url_title = $meta['url_title'];

    	// -------------------------
    	//	Create append string
    	// -------------------------
    	$append = $delimiter . $append_string;

    	// -------------------------
    	//	Is it allready there?
    	// -------------------------
    	if(strstr($url_title, $delimiter, FALSE) != $append)
    	{
    		// -------------------------
    		// No, update the url title.
    		// -------------------------
    		$url_title .= $append;
    		$data = array('url_title' => $url_title);
    		$sql = $this->EE->db->update_string('exp_channel_titles', $data, "entry_id = " . $entry_id);
    		$this->EE->db->query($sql);
    	}

    	return true;
    }

    /**
	 * Get current settings from DB
	 *
	 * @access      private
	 * @return      mixed
	 */
	private function _get_current_settings()
	{
		$query = $this->EE->db->select('settings')
		       ->from('extensions')
		       ->where('class', $this->class_name)
		       ->limit(1)
		       ->get();

		return @unserialize($query->row('settings'));
	}

    /**
	 * Get settings for this site
	 *
	 * @access      private
	 * @return      mixed
	 */
	private function _get_site_settings($current = array())
	{
		$current = (array) $current;

		return isset($current[$this->site_id]) ? $current[$this->site_id] : array_merge($this->default_settings, $current);
	}

/* END class */
}
/* End of file ext.ds_url_title.php */
/* Location: ./system/expressionengine/third_party/ds_url_title/ */ 