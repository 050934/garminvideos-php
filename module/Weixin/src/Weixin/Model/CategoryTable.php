<?php
namespace Weixin\Model;

use Zend\Db\TableGateway\TableGateway;

class CategoryTable
{
	protected $tableGateway;
	
	public function __construct(TableGateway $tableGateway)
	{
		$this->tableGateway = $tableGateway;
	}
	
	public function fetchAll()
	{
		$resultSet = $this->tableGateway->select();
		return $resultSet;
	}
	
	public function getCategory($catId)
	{
		$catId = (int)$catId;
		$rowset = $this->tableGateway->select(array("cat_id" => $catId));
		$row = $rowset->current();
		if(!$row)
		{
			throw new \Exception("Could not find row $catId");
		}
		return $row;
	}
	
	public function saveCategory(Category $category)
	{
		$data = array(
				"cat_title" => $category->catTitle,
				"cat_pages" => $category->catPages,
				"cat_subcats" => $category->catSubcats,
				"cat_files" => $category->catFiles
		);

		$catId = (int)$category->catId;
		if($catId == 0)
		{
			$this->tableGateway->insert($data);
		}
		else
		{
			if($this->getCategory($catId))
			{
				$this->tableGateway->update($data, array("cat_id" => $catId));
			}
			else
			{
				throw new \Exception("Category id: $catId, does not exist");
			}
		}
	}
	
	public function deleteCagegory($catId)
	{
		$this->tableGateway->delete(array("cat_id" => $catId));
	}
}