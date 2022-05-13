<?php
class User{
	public $username;
	public $passwd;
	public $UUID;
	public $language;

	function __construct($username,$passwd,$uuid,$language){
		$this->username = $username;
		$this->passwd = $passwd;
		$this->UUID = $uuid;
		$this->language = $language;
	}
	
	public function __toString() {
		return json_encode($this->getArrayFormated());
	}
	
	public function getArrayFormated(){
        $dataarr = array(
			"id" => $this->UUID,
			"properties"=>array( // User attributes (array, one attribute per element)
				array(
					"name"=>"preferredLanguage",
					"value"=>$this->language,
				),
				// ,...(there can be more)
			)
			);
		return $dataarr;
    }
}
