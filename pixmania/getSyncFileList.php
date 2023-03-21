<?php
//          Create Date      2014/04/27
//          FileName		 getSyncFileList.php
//          Developer		 Somsak Ri
//          E-Mail			 risomsak@gmail.com

$filePath		= "sync/";

$fileList = array();

$handler = opendir($filePath);

while ($file = readdir($handler)) {
	if ($file != "." && $file != "..") {
		$fileList[] = $file;
	}
}
closedir($handler);
echo json_encode($fileList);

?>