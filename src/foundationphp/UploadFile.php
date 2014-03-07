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
	protected $renameDuplicates;


	/**
	 * Magic __constructor method to create UploadFile object
	 * @param string - the full path to where the files will be uploaded
	 * @return exception or sets $destination
	 */
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


	/**
	 * Sets max size of a single file up to the server limit
	 * @param numeric - bytes 
	 * uses static method to conver server max filesize to bytes to easily compare the input
	 */
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
	/**
	 * Helper function to convert a string containg a file size with suffix
	 * @param string
	 * @return numeric
	 */
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


	/**
	 * Helper function to convert bytes to a user friendly version
	 * @param numeric
	 * @returns string - number converted from bites to either Megabytes or Kilobytes
	 */
	public static function convertFromBytes($bytes)
	{

		$bytes /= 1024;
		if($bytes > 1024){
			return number_format($bytes/1024, 1) . ' MB';
		}else{
			return number_format($bytes, 1) . ' KB';
		}
	}
	/**
	 * Permits all file types to be uploaded. Any mime type that is $notTrusted will have a suffix appened
	 * @param options string - the suffix will be either the default property if left blank,
	 *			omitted if an empty string, or whataver string is provided
	 * Turns off type checking which was stopping files not in the $permittedTypes array from being uploaded
	 */
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

	/**
	 * Function called on object to validate the file for upload. Calls method to actaully move the file if passes.
	 * @param optional boolean - defaults to true, which will rename duplicate file names,
	 *		  if false, original file will be overwritten
     * handles both single or multiple files
	 */
	public function upload($renameDuplicates = true)
	{
		$this->renameDuplicates = $renameDuplicates;
		$uploaded = current($_FILES);
		if(is_array($uploaded['name'])){
			foreach ($uploaded['name'] as $key => $value) {
				$currentFile['name'] = $uploaded['name'][$key];
				$currentFile['type'] = $uploaded['type'][$key];
				$currentFile['tmp_name'] = $uploaded['tmp_name'][$key];
				$currentFile['error'] = $uploaded['error'][$key];
				$currentFile['size'] = $uploaded['size'][$key];
				if($this->checkFile($currentFile)){
					$this->moveFile($currentFile);
				}
			}
		}else{
			if($this->checkFile($uploaded)){
				$this->moveFile($uploaded);
			}
		}
	}	

    /**
     * returns any error or success messages added to a messages array during file upload process
     */
	public function getMessages()
	{
		return $this->messages;
	}


    /**
     * @param assoc array - a reference to the current super global $_FILE
     * Checks for passing grades on error message, file size, and file name
     * @return boolean
     */
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


    /**
     * @param assoc array - a reference to the current super global $_FILE
     * This is called if file does not pass the error check in checkFile and adds a message for the user to the messages array
     */
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


    /**
     * @param assoc array - a reference to the current super global $_FILE
     * Checks the file size to make sure it is not empty or too large
     * @return boolean - if it fails a message is also appended to the messages array
     */
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

    /**
     * @param assoc array - a reference to the current super global $_FILE
     * Only called if typeCheckingOn is true. Checks the file type against permittedTypes.
     * @return boolean - if it fails a message is also appended to the messages array
     */
	protected function checkType($file)
	{
		if( in_array($file['type'], $this->permittedTypes)){
			return true;
		}else{
			$this->messages[] = $file['name'] . ' is not permitted type of file.';
			return false;
		}
	}


    /**
     * @param assoc array - a reference to the current super global $_FILE
     * Removes spaces from file name and replaces with '_'
     */
	protected function checkName($file)
	{
		$this->newName = null;
		$noSpaces = str_replace(' ', '_', $file['name']);

        //only change to noSpace verion if necessary
		if($noSpaces != $file['name']){
			$this->newName = $noSpaces;
		}

        //extract the file extension and add suffix if neccessary
		$nameparts = pathinfo($noSpaces);
		$extension = isset($nameparts['extension']) ? $nameparts['extension'] : '';

        //only add suffix if suffix is not empty and typeChecking has ben enabled
		if(!$this->typeCheckingOn && !empty($this->suffix)){
			if(in_array($extension, $this->notTrusted) || empty($extension)){
				$this->newName = $noSpaces . $this->suffix;
			}
		}

        //checks the value in the upload method to see if duplicate files should be renamed/overwritten
		if ($this->renameDuplicates){

            //gets name of either the changed name or original file name
			$name = isset($this->newName) ? $this->newName : $file['name'];

            //find all files in the destination folder
			$existing = scandir($this->destination);
			if(in_array($name, $existing)){
				$i = 1;
				do{

                    //tries to to rename the file with an underscore and number
                    //continues to increment until a filename does not exist with the same name
					$this->newName = $nameparts['filename'] . '_' .$i++;
					if(!empty($extension)){
						$this->newName .= ".$extension";
					}
					if(in_array($extension, $this->notTrusted)){
						$this->newName .= $this->suffix;
					}
				}while(in_array($this->newName, $existing));
			}
		}
	}

    /**
     * @param assoc array - a reference to the current super global $_FILE
     * Moves file to destination and appends messages to messages array
     */
	protected function moveFile($file)
	{
		$filename = isset($this->newName) ? $this->newName : $file['name'];
		$success = move_uploaded_file($file['tmp_name'], $this->destination . $filename);
		if($success){
			$result= $file['name'] . ' was uploaded successfully ';
			if(!is_null($this->newName)){
				$result .= "and renamed to " . $this->newName;
			}
			$result .= '.';

			$this->messages[] = $result;
		}else{
			$this->messages[] = 'Could not upload ' . $file['name'];
		}
		
	}

}