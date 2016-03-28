<?php

class potd {

	protected $config 		= null;
	protected $rawHtml 		= null;
	protected $dom			= null;
	protected $hasParsedHtml 	= false;
	protected $image_url 		= null;
	protected $image_desc		= null;

	public function __construct(){
		$this->config = require('../conf/config.php');
		libxml_use_internal_errors($this->config['hide_xml_errors']);
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
			throw Exception("Error contacting national geographic servers while retrieving photo of the day");
		}

		return $this->rawHtml;
	}

	protected function parseHtml(){
		$this->dom = new DOMDocument();
		if( $this->dom->loadHtml($this->rawHtml) ){
			$this->hasParsedHtml = true;
			return true;
		}else{
			throw Exception("Could not parse raw html");
		}
	}

	public function getImageSrc(){
		if(!$this->hasParsedHtml){
			$this->parseHtml();
		}
		
		$xpath = new DOMXpath($this->dom);
		$result = $xpath->query($this->config['xpath_img']);
		
		$c = count($result);
		if($c != 1){
			throw Exception("Image xpath is incorrect, expected 1 result, found $c");
		}

		$this->image_url = "https:".$result->item(0)->value;
		return $this->image_url;
	}

	public function getImageDescription(){
		if(!$this->hasParsedHtml){
			$this->parseHtml();
		}

		$xpath = new DOMXpath($this->dom);
		$result = $xpath->query($this->config['xpath_desc']);
		
		print_r($result);
		
	}

	public function saveData($data_dir = null){
		if(is_null($data_dir)){
			$data_dir = $this->config['data_dir'];
		}
		
		mkdir($data_dir.date("Y-m-d",time()));		
		
		if(!is_null($this->image_url)){
			
			file_put_contents($data_dir.basename($this->image_url), fopen($this->image_url, 'r'));
		}

	}
	

}

$a = new potd();

$a->getRawHtml();

