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
 ?>
<!DOCTYPE html>
<html>
<head>
	<title>PHP File</title>
</head>
<body>
<h2>Uploading Files</h2>
<?php if($result){ ?>
<ul class="result">
	<?php 
		foreach($result as $message){
			echo '<li>' . $message . '</li>';
		}
	?>
</ul>
<?php } ?>
<form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post" enctype="multipart/form-data">
	<label for="file">Filename:</label>
	<input type="file" name="file" id="file"><br>
	<input type="submit" name="upload" id="upload" value="Submit">
</form>

<?php echo $age; ?>
<br />
<?php echo $newName; ?>

</body>
</html>
