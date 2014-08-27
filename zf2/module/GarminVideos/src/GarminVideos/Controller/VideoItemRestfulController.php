<?php
namespace GarminVideos\Controller;

use Zend\Mvc\Controller\AbstractRestfulController;
use Zend\View\Model\JsonModel;
use GarminVideos\Model\VideoItem;

include 'VideoItemController.php';
include __DIR__ . '/../Model/VideoItem.php';


class VideoItemRestfulController extends AbstractRestfulController
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

	public function get($videoId)
	{
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

	public function create($data)
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

	public function update($id, $data)
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

	public function delete($videoId)
	{
		return $this->getVideoItemControllerImpl()->deleteVideoItem($videoId);
	}
}

