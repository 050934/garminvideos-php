<?php
namespace Weixin\Controller;
use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use Zend\View\Helper\ViewModel;

class CategoryController extends AbstractRestfulController
{
	protected $categoryTable;
	
	public function getCategoryTable()
	{
		if(!$this->categoryTable)
		{
			$sm = $this->getServiceLocator();
			$this->categoryTable = $sm->get("Weixin\Model\CategoryTable");
		}
		return $this->categoryTable;
	}
	
	public function get($id)
	{
		try {
			$catDict = $this->getCategoryTable()->getCategory($id)->toDict();
		} catch (\Exception $e) {
			return new JsonModel(array(
					"errMsgs" => array($e->getMessage(),
									   //$e->getTraceAsString()
					)
			));
		}
		
		//return $this->getCategoryTable()->getCategory($id);
		return new JsonModel($catDict);
		//$jsonModel = new JsonModel();
		//$jsonModel->setVariable("cat", $this->getCategoryTable()->getCategory($id));
		//return $jsonModel;
	}
	
	public function getList()
	{
// 		return new ViewModel(array(
// 			"category" => $this->getCategoryTable()->fetchAll()
// 		));
		return new JsonModel((array)$this->getCategoryTable()->fetchAll());
	}
	
	public function create($data)
	{
		$this->getCategoryTable()->saveCategory($data);
	}
	
	public function update($id, $data)
	{
		$data->catId = $id;
		$this->getCategoryTable()->saveCategory($data);
	}
	
	public function delete($id)
	{
		$this->getCategoryTable()->deleteCategory($id);
	}
}