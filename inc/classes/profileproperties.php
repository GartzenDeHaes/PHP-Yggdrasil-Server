<?php
class ProfileProperties
{
	public $name;
	public $value;
	public $signiture;

	function __construct($name, $value, $issigned = true)
	{
		$this->name = $name;
		$this->value = $value;
		if ($issigned) $this->signiture = Encypt::genSigniture($this->value);
		else $this->signiture = "N/A";
	}

	public function __toString()
	{

		return json_encode($this->getArrayFormated());
	}
	public function getArrayFormated()
	{
		$dataarr = array(
			"name" => $this->name,
			"value" => $this->value,
		);
		if ($this->signiture != "N/A") {
			$dataarr["signature"] = $this->signiture;
		}

		return $dataarr;
	}
}
