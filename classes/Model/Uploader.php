<?php defined('SYSPATH') or die('No direct script access.');
class Model_Uploader {

	private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760){        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) {
            $this->file = new Model_UploaderXHR();
        } elseif (isset($_FILES['qqfile'])) {
            $this->file = new Model_UploaderForm();
        } else {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings(){        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit){
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str){
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        switch($last) {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $replaceOldFile = FALSE){
        
        if ( !file_exists($uploadDirectory) ):
        	try { 
        		mkdir($uploadDirectory, 0775);
        	} catch ( Exception $e) {
	        	return array('error' => "Server error. Cannot create upload directory.");	
	        	
        	}
        endif;
        
        if (!is_writable($uploadDirectory)){
            return array('error' => "Server error. Upload directory isn't writable.");
        }
        
        if (!$this->file){
            return array('error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) {
            return array('error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) {
            return array('error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        $filename = str_replace(" ", "_", $filename);
		$filename = substr($filename,0,20)."-".substr(time(), -5);
		
		$date_path = date("/Y/m/d/");
		$local_enabled = false;
		$shared_enabled = false;
		if ( !file_exists($uploadDirectory.$date_path) ):
			mkdir($uploadDirectory.$date_path, 0775, true);
			if ( file_exists("/local/live/".$uploadDirectory) ):
				$local_enabled = true;
				mkdir("/local/live/".$uploadDirectory.$date_path, 0775, true);
			endif;
			if ( file_exists("/shared/live/".$uploadDirectory) ):
				$shared_enabled = true;
				mkdir("/shared/live/".$uploadDirectory.$date_path, 0775, true);
			endif;
		endif;
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions)){
            $these = implode(', ', $this->allowedExtensions);
            return array('error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        $fullpath = $uploadDirectory . $date_path . $filename . '.' . $ext;
        
        if(!$replaceOldFile){
            /// don't overwrite previous files that were uploaded
            while (file_exists($fullpath)) {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($fullpath)){
            global $sync_command;
            exec($sync_command . $fullpath);
            if ( $local_enabled ):
	            $local_rsync =  "rsync -rlgoDp ".$fullpath." /local/live/".$fullpath;
	            exec( "rsync ".$local_rsync );
            endif;
            
            if ( $shared_enabled ):
	            $shared_rsync =  "rsync -rlgoDp ".$fullpath." /shared/live/".$fullpath;
	            exec( "rsync ".$shared_rsync );
            endif;
            
            return array(
            	'success'=>true,
            	'filename'=> $date_path.$filename.".".$ext
            );
            
        } else {
            return array('error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
        
    }    


}