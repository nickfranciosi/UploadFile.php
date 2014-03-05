<?php 
namespace foundationphp;

class UploadFile
{
	protected $destination;
	protected $messages = [];
	protected $maxSize = 51200;
	protected $permittedTypes = array(
		'image/jpg',
		'image/jpeg',
		'image/png',
		'image/gif'
	);
	protected $newName;
	protected $typeCheckingOn = true;
	protected $notTrusted = array ('bin','exe','js','pl','php','py','sh');
	protected $suffix = '.upload';

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
		$serverMax = self::convertToBytes(ini_get('upload_max_filesize'));
		if ($bytes > $serverMax){
			throw new \Exception("Max size of files can not exceed server limit: " . self::convertFromBytes($serverMax));
		}
		if(is_numeric($bytes) && $bytes > 0){
			$this->maxSize = $bytes;
		}

	}

	public static function convertToBytes($val)
	{	
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		if(in_array($last, array('g','m','k'))){
			switch($last){
				case 'g':
					$val *= 1024;
				case 'm':
					$val *= 1024;
				case 'k':
					$val *= 1024;

			}
		}
		return $val;
	}

	public static function convertFromBytes($bytes)
	{

		$bytes /= 1024;
		if($bytes > 1024){
			return number_format($bytes/1024, 1) . ' MB';
		}else{
			return number_format($bytes, 1) . ' KB';
		}
	}

<<<<<<< HEAD
=======
	public function allowAllTypes($suffix = null)
	{
		$this->typeCheckingOn = false;
		if(!is_null($suffix)){
			if(strpos($suffix, '.') === 0 || $suffix == ''){
				$this->suffix = $suffix;
			}else{
				$this->suffix = '.' . $suffix;
			}
		} 
	}

>>>>>>> dev
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
		if($this->typeCheckingOn){

			if(!$this->checkType($file)){
				return false;
			}
		}
		$this->checkName($file);
		return true;
	}

	protected function getErrorMessage($file)
	{
		switch ($file) {
			case 1:
			case 2:
				$this->messages[] = $file['name'] . ' is too big (max: ' . self::convertFromBytes($this->maxSize) . ')';
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
			$this->messages[] = $file['name'] . ' is larger than is allowed(max: ' . self::convertFromBytes($this->maxSize) . ')';
			return false;
		}else{
			return true;
		}
	}

	protected function checkType($file)
	{
		if( in_array($file['type'], $this->permittedTypes)){
			return true;
		}else{
			$this->messages[] = $file['name'] . ' is not permitted type of file.';
			return false;
		}
	}

	protected function checkName($file)
	{
		$this->newName = null;
		$noSpaces = str_replace(' ', '_', $file['name']);
		if($noSpaces != $file['name']){
			$this->newName = $noSpaces;
		}
		$nameparts = pathinfo($noSpaces);
		$extension = isset($nameparts['extension']) ? $nameparts['extension'] : '';
		if(!$this->typeCheckingOn && !empty($this->suffix)){
			if(in_array($extension, $this->notTrusted) || empty($extension)){
				$this->newName = $noSpaces . $this->suffix;
			}
		}
	}

	protected function moveFile($file)
	{
		$result= $file['name'] . ' was uploaded successfully ';
		if(!is_null($this->newName)){
			$result .= "and renamed to " . $this->newName;
		}
		$result .= '.';

		$this->messages[] = $result;
	}

}