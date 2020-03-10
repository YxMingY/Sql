<?php


namespace yxmingy;


class Table
{
	private $name;
	private $colomns = [];
	private $addition = "";
	public function __construct(string $name)
	{
		$this->name = $name;
	}
	public function add(string $colomn)
	{
		$this->colomns[] = $colomn;
	}
	public function setAddition(string $addition)
	{
		$this->addition = $addition;
	}
	public function __toString():string
	{
		return "TABLE IF NOT EXISTS $this->name (\n"
			.implode(",",$this->colomns)
			."\n)"
			.$this->addition;
	}
}