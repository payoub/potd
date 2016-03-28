<?php

require('app/app.php');

try{

	$potd = new potd();
	$potd->getImageSrc();
	$potd->getImageDescription();
	$potd->saveData();


}catch(Exception $e){
	echo "Error: {$e->getMessage()}";
}
