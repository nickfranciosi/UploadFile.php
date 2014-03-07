<?php 
use foundationphp\UploadFile;

$max = 500 * 1024;
$result = [];
if(isset($_POST['upload'])){
	require_once('src/foundationphp/UploadFile.php');
	$destination = __DIR__ . '/uploaded/';
	try{
		$upload = new UploadFile($destination);
		$upload->setMaxSize($max);
		$upload->allowAlltypes('nick');
		$upload->upload();
		$result = $upload->getMessages();
	} catch (Exception $e){
		$result[] = $e->getMessage();
	}
}

$error = error_get_last();
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>UPload File</title>
</head>
<body>
<h2>Uploading Files</h2>
<?php if($result || $error){ ?>
<ul class="result">
	<?php 
		if($error){
			echo "<li>{$error['message']}</li>";
		}
		if($result){
			foreach($result as $message){
				echo '<li>' . $message . '</li>';
			}
		}
	?>
</ul>
<?php } ?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
	<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max; ?>"
	<label for="file">Filename:</label>
	<input type="file" name="file[]" id="file" multiple><br>
	<input type="submit" name="upload" id="upload" value="Submit">
</form>


</body>
</html>