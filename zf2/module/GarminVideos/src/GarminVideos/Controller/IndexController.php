<?php
namespace GarminVideos\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use GarminVideos\Model\LocalCategory;
include 'VideoItemController.php';

class IndexController extends AbstractActionController
{
	protected $videoItemControllerImpl;
	
	public function getVideoItemControllerImpl()
	{
		if(!$this->videoItemControllerImpl)
		{
			$this->videoItemControllerImpl = new VideoItemControllerImpl($this->getServiceLocator());
		}
		return $this->videoItemControllerImpl;
	}
	
	
	public function indexAction()
	{
		$queryString = array();
		$args = array();
		$pageSize = $dftPageSize = 12;
		$pageNum = $dftPageNum = 1;
		if($this->params("pageNum"))
			$pageNum = (int)$this->params("pageNum");
		
		if(isset($_REQUEST["pgnum"]))
		{
			$pageNum = $_REQUEST["pgnum"];
			try{
				$pageNum = (int)$pageNum;
				$pageNum = $pageNum <= 0 ? $dftPageNum : $pageNum;
			}catch(\Exception $e){
				$pageNum = $dftPageNum;
			}
		}
		if(isset($_REQUEST["pgsize"]))
		{
			$pageSize = $_REQUEST["pgsize"];
			try {
				$pageSize = (int)$pageSize;
				if($pageSize > 0 and $pageSize <= 20)
					array_push($queryString, "pgsize=$pageSize");
				else
					$pageSize = $dftPageSize;
				
			} catch (Exception $e) {
				$pageSize = $dftPageSize;
			}
		}
		if(isset($_REQUEST["youtube"]))
			array_push($queryString, "youtube");
		if(isset($_REQUEST["upload"]))
			array_push($queryString, "upload");
		$catUI = "1";
		if(isset($_REQUEST["catui"]))
		{
			$catUI = $_REQUEST["catui"];
			if(in_array($catUI, array("1", "2")))
				array_push($queryString, "catui=$catUI");
			else
				$catUI = "1";
		}
		
		$queryStringNoCat = $queryString;
		if(count($queryStringNoCat) > 0)
			$args["queryStringNoCat"] = "?" . join("&", $queryStringNoCat);
		
		
		$latestVideoItems = $this->getVideoItemControllerImpl()
									->getVideoItemTable()->getVideoItems(4, 1);
		$baseURL = "http://" . $_SERVER["HTTP_HOST"] . "/garminvideos/";

		$category = LocalCategory::All;
		try {
			if(isset($_REQUEST["cat"]))
			{
				$category = LocalCategory::fromString($_REQUEST["cat"]);
				array_push($queryString, "cat=$category");
			}
		} catch (\Exception $e) {
			$category = LocalCategory::All;
		}
		
		$videoItems = $this->getVideoItemControllerImpl()
				->getVideoItemTable()->getVideoItems($pageSize, $pageNum, null, $latestVideoItems, $category);
		$totals = $this->getVideoItemControllerImpl()
				->getVideoItemTable()->getVideoTotalCount($latestVideoItems, $category);
		$pageCount = $totals % $pageSize == 0 ? $totals / $pageSize : (int)($totals / $pageSize) + 1;
		$display = 4;
		
		$args["latestVideoItems"] = $latestVideoItems;
		$args["pageCountList"] = array();
		$args["videoItems"] = $videoItems;
		$args["totals"] = $totals;
		$args["baseURL"] = $baseURL;
		$args["catUI"] = $catUI;
		$args["category"] = $category;
		$args["pageNum"] = $pageNum;
		
		if(count($queryString) > 0)
			$args["queryString"] = "?" . join("&", $queryString);
		
		if($totals){
			if($pageCount <= 4)
				$args["pageCountList"] = range(1, $pageCount);
			else
			{
				if($pageCount - $pageNum <= 1)
				{
					foreach (range($pageCount - $display + 1, $pageCount) as $i)
						array_push($args["pageCountList"], $i);
				}
				else
				{
					if($pageNum >= 2)
					{
						foreach (range(-1, $display - 2) as $i)
							array_push($args["pageCountList"], $i + $pageNum);
					}
					else
					{
						foreach (range(1, $display) as $i)
							array_push($args["pageCountList"], $i);
					}
			
				}
			}
		}
		
		if($pageCount > $display and ($display - $pageNum <= 1 or $pageNum >= $display))
			$args["first"] = 1;
		if($pageCount > $display and !in_array($pageCount, $args["pageCountList"]))
			$args["last"] = $pageCount;
		if($pageNum >= 2)
			$args["pre"] = $pageNum - 1;
		if($pageNum < $pageCount)
			$args["next"] = $pageNum + 1;
		
		$tmpl = "garmin-videos/index/index.phtml";
		$model = new ViewModel($args);
		$model->setTemplate($tmpl);
		$model->setTerminal(true);
		return $model;
		
		//return $this->layout($tmpl);
	}
}