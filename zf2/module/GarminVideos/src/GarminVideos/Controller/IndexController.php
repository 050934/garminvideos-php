<?php
namespace GarminVideos\Controller;

use Zend\Mvc\Controller\AbstractActionController;

class IndexController extends AbstractActionController
{
	protected $videoItemTable;
	
	public function getVideoItemTable()
	{
		if(!$this->videoItemTable)
		{
			$sm = $this->getServiceLocator();
			$this->videoItemTable = $sm->get("GarminVideos\Model\VideoItemTable");
		}
	
		return $this->videoItemTable;
	}
	
	public function indexAction()
	{
		return $this->layout("garmin-videos/index/index.phtml");
	}
}