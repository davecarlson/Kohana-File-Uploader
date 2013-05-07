<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Files {

	// Notifies if any errors have occured
	protected $status;
	
	protected $_config = array();

	/**
	 * Creates a new Files object.
	 *
	 * @return  Files
	 */
	public static function factory()
	{

		return new Files();

	} // public static function factory()
	

	/**
	 * Creates a new Files object.
	 *
	 * @return  void
	 */
	public function __construct()
	{
	
		$this->_config = Kohana::$config->load('files');
		
	}  // public function __construct()

	
	/**
	 * Creates the Upload form
	 *
	 * @return View
	 */
	public function render()
	{
		$view = View::factory("Files/Uploadform");
		if ($this->status == "error"):
			$view->bind("error", $this->status);
		endif;
		return $view->render();

	} // public function render()


	public function upload()
	{
		// list of valid extensions, ex. array("jpeg", "xml", "bmp")
		$allowedExtensions = array();
		// max file size in bytes
		$sizeLimit = 100 * 1024 * 1024;

		$uploader = new Model_Uploader($allowedExtensions, $sizeLimit);
		$result = $uploader->handleUpload($this->_config['upload_base']);
		// to pass data through iframe you will need to encode all html tags
		echo htmlspecialchars(json_encode($result), ENT_NOQUOTES);

	}
	
} // class Kohana_Files
