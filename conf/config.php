<?php

/**
*
* Picture of the day configuration settings
*
*/
return array(

	/**
	* Hide errors caused by poorly formatted xml
	*/
	"hide_xml_errors" => true,
	
	/**
	* National Geographic Photo of the day enpoint 
	*/
	"url"	=> "http://photography.nationalgeographic.com/photography/photo-of-the-day/",


	/**
	* Maximum attempts the application will try and contact national geographic servers 
	*/
	"max_url_attempts" => 10,

	/**
	* XPath to the photo of the day image src 
	*/
	"xpath_img" =>	"//div[@class='primary_photo']/a/img/@src",

	/**
	* XPath to the photo of the day description 
	*/
	"xpath_desc_date" 	=>	"//div[@class='article_text']/div[@id='caption']/p[@class='publication_time']",
	"xpath_desc_title" 	=>	"//div[@class='article_text']/div[@id='caption']/h2",
	"xpath_desc_credit" 	=>	"//div[@class='article_text']/div[@id='caption']/p[@class='credit']/a",
	"xpath_desc_body" 	=>	"//div[@class='article_text']/div[@id='caption']/p[not(*) and not(@class)]",

	/**
	* Data directory for downloaded contents 
	*/
	"data_dir" =>	"/var/www/html/potd/data/",

	/**
	* Timezone for date functions 
	*/
	"timezone" =>	"Australia/Sydney"
);
