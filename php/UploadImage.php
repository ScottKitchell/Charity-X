<?

/*	
	This page uploads an image to the server
	using posted file data. 
	Returns the confimation in JSON format. 
*/

// Include class structures
require_once($_SERVER["DOCUMENT_ROOT"].'/lib/X-ClassStructures.php');

// Format the responce object
$result = array(
	"imageName" => null,
	"status" => 'unknown',
	"errorMsg" => null
);


if(isset($_POST) and $_SERVER['REQUEST_METHOD'] == "POST"){
	
	// Get file data
	$name = $_FILES['file']['name'];
	$size = $_FILES['file']['size'];
	$tmp = $_FILES['file']['tmp_name'];
	
	// Test if file exists
	if(!strlen($name)){
		$result["status"] = "failed";
		$result["errorMsg"] = "No image was selected.";
		echo json_encode($result);
		exit;
	}
	
	// Test if the file format is an image
	$ext = getExtension($name);
	$formats = array("jpg", "png", "gif", "bmp", "jpeg", "PNG", "JPG", "JPEG", "GIF", "BMP");
	if(!in_array($ext, $formats)){
		$result["status"] = "failed";
		$result["errorMsg"] = "$name is an invalid image file format.";
		echo json_encode($result);
		exit;
	}
	
	// Test if the file is small enough
	if($size>(1024*1024)){
		$result["status"] = "failed";
		$result["errorMsg"] = "$name file size exceeds 1 MB ($size).";
		echo json_encode($result);
		exit;
	}
	
	// Set a new image filename
	$imageName = ID::create(8).".".$ext;

	// Upload the image to the server
	if(move_uploaded_file($tmp, $_SERVER["DOCUMENT_ROOT"]."/images/".$imageName)){
		$result["status"] = "success";
		$result["imageName"] = $imageName;
	} else{
		$result["status"] = "failed";
		$result["errorMsg"] = "$name failed to upload.";
	}
	
	// Return the result
	echo json_encode($result);
}

function getExtension($str) {
	$i=strrpos($str,".");
	if(!$i){
		return"";
	}
	$l = strlen($str)-$i;
	$ext=substr($str,$i+1,$l);
	return $ext;
}


?>