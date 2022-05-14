<?php
class Profile
{
	public $name;
	public $UUID;
	public $texture;
	function __construct($name, $uuid, $texture)
	{
		$this->name = $name;
		$this->UUID = $uuid;
		$this->texture = $texture;
	}

	public function __toString()
	{
		return json_encode($this->getArrayFormated());
	}

	public function getArrayFormated()
	{
		$texture_data = (new ProfileProperties("textures", base64_encode($this->texture)))->getArrayFormated();
		$dataarr = array(
			"id" => $this->UUID,
			"name" => $this->name,
			"properties" => array(
				$texture_data,
			),
		);
		return $dataarr;
	}
}
