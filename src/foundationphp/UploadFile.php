<?php 
namespace foundationphp;

class UploadFile
{
	protected $destination;
	protected $messages = [];
	protected $maxSize = 51200;

	public function __construct($uploadFolder)
	{
		if(!is_dir($uploadFolder) || !is_writable($uploadFolder)){
			throw new \Exception("$uploadFolder must be valid and writable");
		}
		if($uploadFolder[strlen($uploadFolder-1)] != '/'){
			$uploadFolder .= '/';
		}
		$this->destination = $uploadFolder;
	}

	public function setMaxSize($bytes)
	{
		if(is_numeric($bytes) && $bytes > 0){
			$this->maxSize = $bytes;
		}

	}

	public function upload()
	{
		$uploaded = current($_FILES);
		if($this->checkFile($uploaded)){
			$this->moveFile($uploaded);
		}
	}

	public function getMessages()
	{
		return $this->messages;
	}

	protected function checkFile($file)
	{
		if($file['error'] != 0) {
			$this->getErrorMessage($file);
			return false;
		}

		if(!$this->checkSize($file)){
			return false;
		}
		return true;
	}

	protected function getErrorMessage($file)
	{
		switch ($file) {
			case 1:
			case 2:
				$this->messages[] = $file['name'] . ' is too big';
				break;
			case 3:
				$this->messages[] = $file['name'] . ' was only partially laoded';
				break;
			case 4:
				$this->messages[] = "No file selected.";
				break;
			default:
				$this->messages[] = "Sorry there was a problem uploading " . $file['name'];
				break;
		}
	}

	protected function checkSize($file)
	{
		if($file['size'] == 0){
			$this->messages[] = $file['name'] . ' is empty.';
			return false;
		}elseif ($file['size'] > $this->maxSize){
			$this->messages[] = $file['name'] . ' is larger than is allowed.';
			return false;
		}else{
			return true;
		}
	}

	protected function moveFile($file)
	{
		$this->messages[] = $file['name'] . ' was uploaded successfully.';
	}

}