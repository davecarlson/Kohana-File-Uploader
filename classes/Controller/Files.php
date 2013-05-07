<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Files extends Controller_Template_Site {

	public function before()
	{
		parent::before();
		array_push($this->template->scripts, "fileuploader.js");

	}
	
	public function action_index()
	{
		$this->template->title = "File Uploader";
		$files = Files::factory();
		$this->template->content = $files->render();
			

	} // action_create

	public function action_post()
	{
		$this->auto_render = false;
		$files = Files::factory();
		echo $files->upload();
		exit;

	} // function action_post
	
} // End Files