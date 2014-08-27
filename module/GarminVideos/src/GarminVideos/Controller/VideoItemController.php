<?php
namespace GarminVideos\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use GarminVideos\Model\VideoItem;
use GarminVideos\Model\LocalCategory;

include __DIR__ . '/../Model/VideoItem.php';

class VideoItemControllerImpl
{
	protected $videoItemTable;
	protected $serviceManager;
	
	public function __construct($sm)
	{
		$this->serviceManager = $sm;
	}
	
	public function getVideoItemTable($sm=null)
	{
		if(!$this->videoItemTable)
		{
			if($sm)
				$this->serviceManager = $sm;
			$this->videoItemTable = $this->serviceManager->get("GarminVideos\Model\VideoItemTable");
		}
		return $this->videoItemTable;
	}

	public function getVideoItem($videoId)
	{
		$result = null;
		try {
			$result = $this->getVideoItemTable()->getVideoItem($videoId)->toDict();
		} catch (\Exception $e) {
			$result = array(
					"errMsgs" => array($e->getMessage()
					));
		}
		return new JsonModel($result);
	}

	public function getVideoItems($videoId=null, $pageSize=null, $pageNum=null,
			array $excludeVideos=array(), $category=LocalCategory::All)
	{
		$videos = array();
		$context = null;
		
		if($videoId == null && $pageSize == null && $pageNum == null
		&& count($excludeVideos) == 0)
		{
			foreach ($this->getVideoItemTable()->fetchAll() as $video)
			{
				array_push($videos, $video);
			}
			$context = array(
					"totals" => count($videos),
					"videos" => $videos
			);
		}
		else
		{
			$videos = $this->getVideoItemTable()->getVideoItems($videoId, $pageSize, $pageNum,
											$excludeVideos, $category);
			if(is_int($videoId) && is_int($pageSize))
			{
				$pageNum = $pageSize;
				$pageSize = $videoId;
				$videoId = null;
			}
			$context = array(
				"totals" => $this->getVideoItemTable()->getVideoTotalCount($excludeVideos, $category),
				"videos" => $videos,
				"pageSize" => $pageSize,
				"pageNum" => $pageNum				
			);
		}
		
		return new JsonModel($context);
	}
	
	public function deleteVideoItem($videoId)
	{
		$result = null;
		$context = null;
		try {
			$video = $this->getVideoItemTable()->deleteVideoItem($videoId);
			$result = array(
					"status" => "success",
					"video" => $video
			);
		} catch (\Exception $e) {
			$result = array(
					"status" => "error",
					"errMsgs" => array($e->getMessage()
					));
		}
		return new JsonModel($result);
	}
	
	public function addVideoItem(VideoItem $videoItem)
	{
		$result = null;
		try {
			$video = $this->getVideoItemTable()->saveVideoItem($videoItem);
			$result = array(
					"status" => "success",
					"msg" => "add video success",
					"video" => $video
			);
		} catch (\Exception $e) {
			$result = array(
					"errMsgs" => array($e->getMessage()
					));
		}
		return new JsonModel($result);		
	}
	
	public function editVideoItem(VideoItem $videoItem)
	{
		$result = null;
		try {
			$video = $this->getVideoItemTable()->saveVideoItem($videoItem);
			$result = array(
					"status" => "success",
					"msg" => "edit video success",
					"video" => $video
			);			
		} catch (\Exception $e) {
			$result = array(
					"errMsgs" => array($e->getMessage()
					));			
		}
		return new JsonModel($result);		
	}
	
}

class VideoItemController extends AbstractActionController
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
	
	public function getVideoId()
	{
		$vid = $this->params("vid");
		/**
		 * @see http://php.net/manual/zh/function.preg-match.php
		 */
		if(preg_match("/\d+/", $vid))
			$vid = (int)$vid;
		return $vid;
	}
	
	public function dispatchAction()
	{
		$actionResp = null;
		switch ($this->request->getMethod())
		{
			case "GET":
				$actionResp = $this->getList();
				break;
			case "PUT":
				$actionResp = $this->put();
				break;
			default:
				$this->response->setStatusCode(405);
				$actionResp = new JsonModel(array("code" => $this->response->getStatusCode(),
					"phrase" => $this->response->getReasonPhrase()));
				break;
		}
		
		return $actionResp;
	}
	
	public function videoAction()
	{
		$actionResp = null;
		$action	= $this->params("_action");
		
		if(!$action)
		{
			switch ($this->getRequest()->getMethod())
			{
				case "GET":
					$actionResp = $this->get();
					break;
				case "POST":
					$actionResp = $this->post();
					break;
				case "DELETE":
					$actionResp = $this->delete();
					break;
				default:
					$resp = $this->getResponse();
					$resp->setStatusCode(405);
					$actionResp = new JsonModel(array("code" => $resp->getStatusCode(),
							"phrase" => $resp->getReasonPhrase()));
					break;
			}
		}
		else
		{
			$action = strtolower($action);
			switch ($action)
			{
				case "get":
					$actionResp = $this->get();
					break;
				case "edit":
					$actionResp = $this->post();
					break;
				case "delete":
					$actionResp = $this->delete();
					break;
				default:
					if($action === "add" && !$this->getVideoId())
						$actionResp = $this->put();
					else
					{
						$resp = $this->getResponse();
						$resp->setStatusCode(404);
						$actionResp = new JsonModel(array("code" => $resp->getStatusCode(),
								"phrase" => $resp->getReasonPhrase()));
						$resp->setContent($actionResp->serialize());
						$actionResp = $resp;
					}
					break;
					
			}
		}
		
		return $actionResp;
	}
	
	public function get()
	{
		$videoId = $this->getVideoId();
		return $this->getVideoItemControllerImpl()->getVideoItem($videoId);
	}
	
	public function getList()
	{
		if(isset($_REQUEST["pgsize"]) || isset($_REQUEST["pgnum"]))
		{
			$dftPageSize = 12;
			$dftPageNum = 1;
			
			$pageSize = isset($_REQUEST["pgsize"]) ? $_REQUEST["pgsize"] : $dftPageSize;
			$pageNum = isset($_REQUEST["pgnum"]) ? $_REQUEST["pgnum"] : 1;
			try {
				$pageSize = (int)$pageSize;
				$pageSize = $pageSize > 0 && $pageSize <= 20 ? $pageSize : $dftPageSize;
			} catch (\Exception $e) {
				$pageSize = $dftPageSize;
			}
			
			try {
				$pageNum = (int)$pageNum;
				$pageNum = $pageNum > 0 ? $pageNum : $dftPageNum;
			} catch (\Exception $e) {
				$pageNum = $dftPageNum;
			}
			
			return $this->getVideoItemControllerImpl()->getVideoItems($pageSize, $pageNum);
			
		}
		return $this->getVideoItemControllerImpl()->getVideoItems();
	}
	
	public function delete()
	{
		$videoId = $this->getVideoId();
		return $this->getVideoItemControllerImpl()->deleteVideoItem($videoId);
	}
	
	public function put()
	{
		//$videoItem = VideoItem::deserialize();
		$videoItem = VideoItem::deserializeFromRequest();
		if($videoItem instanceof VideoItem)
		{
			return $this->getVideoItemControllerImpl()->addVideoItem($videoItem);
		}
		else
		{
			return new JsonModel($videoItem);
		}
	}
	
	public function post()
	{
		$videoItem = VideoItem::deserialize();
		if($videoItem instanceof VideoItem)
		{
			return $this->getVideoItemControllerImpl()->addVideoItem($videoItem);
		}
		else
		{
			return new JsonModel($videoItem);
		}
	}
}
