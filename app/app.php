<?php

class potd {

	protected $config 		= null;
	protected $rawHtml 		= null;
	protected $dom			= null;
	protected $hasParsedHtml 	= false;
	protected $image_url 		= null;
	protected $image_desc		= null;

	public function __construct(){
		$this->config = require(dirname(__DIR__).'/conf/config.php');
		libxml_use_internal_errors($this->config['hide_xml_errors']);
		date_default_timezone_set($this->config['timezone']);
	}

	public function getRawHtml($attempt_timeout = 5){

		if(!empty($this->rawHtml)){
			return $this->rawHtml;
		}

		$attempts = 0;
		while($attempts < $this->config['max_url_attempts']){

			$this->rawHtml = file_get_contents($this->config['url']);

			if($this->rawHtml != false){
				break;	
			}
			
			sleep($attempt_timeout);
			$attempts++;
		}

		if( empty($this->rawHtml) ){
			throw new Exception("Error contacting national geographic servers while retrieving photo of the day");
		}

		return $this->rawHtml;
	}

	protected function parseHtml(){
		if(empty($this->rawHtml)){
			$this->getRawHtml();
		}

		$this->dom = new DOMDocument();
		if( $this->dom->loadHtml($this->rawHtml) ){
			$this->hasParsedHtml = true;
			return true;
		}else{
			throw new Exception("Could not parse raw html");
		}
	}

	public function getImageSrc(){
		if(!$this->hasParsedHtml){
			$this->parseHtml();
		}
		
		$xpath = new DOMXpath($this->dom);
		$result = $xpath->query($this->config['xpath_img']);

		$this->checkXpathResult($result, "Image", 1);

		$this->image_url = "https:".$result->item(0)->value;
		return $this->image_url;
	}

	public function getImageDescription(){
		if(!$this->hasParsedHtml){
			$this->parseHtml();
		}

		$xpath = new DOMXpath($this->dom);
		$date  = $xpath->query($this->config['xpath_desc_date']);
		$title = $xpath->query($this->config['xpath_desc_title']);
		$credit = $xpath->query($this->config['xpath_desc_credit']);
		$body = $xpath->query($this->config['xpath_desc_body']);
		
		$this->checkXpathResult($date,"Description date",1);
		$this->checkXpathResult($title,"Description title",1);
		$this->checkXpathResult($credit,"Description credit",1);
		$this->checkXpathResult($body,"Description body",1);

		$date_val = $date->item(0)->nodeValue;
		$title_val = $title->item(0)->nodeValue;	
		$credit_val = $credit->item(0)->nodeValue;	
		$body_val = $body->item(0)->nodeValue;	

		$this->image_desc = html_entity_decode( implode("\r\n",array(
			"Title: $title_val",
			"Date: $date_val",
		   	"Photographed by $credit_val",	
			"",
			$body_val
		)));

		return $this->image_desc;

	}

	public function saveData($attempt_timeout = 5){
		$data_dir = $this->config['data_dir'].date("Y-m-d",time());
		
		if(!file_exists($data_dir)){
			echo "Creating directory $data_dir".PHP_EOL;
			if(!mkdir($data_dir,0775,true)){
				throw new Exception("Failed to create directory ($data_dir)");
			}		
		}

		echo "Downloading image from {$this->image_url}".PHP_EOL;
		$attempts = 0;
		$success = false;
		while($attempts < $this->config['max_url_attempts']){

			$filepath = $data_dir."/".basename($this->image_url);

			$result = file_put_contents($filepath, fopen($this->image_url, 'r'));
			if($result && file_exists($filepath)){
				$success = true;
				break;
			}
			
			sleep($attempt_timeout);
			$attempts++;
		}

		if(!$success){
			throw new Exception("Could not download image of the day.");
		}

		echo "Writing description to file".PHP_EOL;
		$d_result  = file_put_contents($data_dir."/description.txt",$this->image_desc);

		if(!$d_result){
			throw new Exception("Could not write image description to file");
		}
		

	}

	protected function checkXpathResult($result,$field,$expected_count){
		$count = $result->length;
		if($count != $expected_count){
			throw new Exception("$field xpath is incorrect, expected $expected_count result, found $count");
		}
	}
	

}
