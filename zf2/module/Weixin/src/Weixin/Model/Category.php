<?php
namespace Weixin\Model;

class Category
{
	public $catId;
	public $catTitle;
	public $catPages;
	public $catSubcats;
	public $catFiles;
	
	public function exchangeArray($data)
	{
		$this->catId = (!empty($data["cat_id"])) ? $data["cat_id"] : null;
		$this->catTitle = (!empty($data["cat_title"])) ? $data["cat_title"] : null;
		$this->catPages = (!empty($data["cat_pages"])) ? $data["cat_pages"] : null;
		$this->catSubcats = (!empty($data["cat_subcats"])) ? $data["cat_subcats"] : null;
		$this->catFiles = (!empty($data["cat_files"])) ? $data["cat_files"] : null;
	}

	public function toDict()
	{
		return array(
			"catId" => $this->catId,
			"catTitle" => $this->catTitle,
			"catPages" => $this->catPages,
			"catSubcats" => $this->catSubcats,
			"catFiles" => $this->catFiles,
		);
	}
}
?>