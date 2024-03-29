<?php
namespace GarminVideos;

use Zend\ModuleManager\Feature\AutoloaderProviderInterface;
use Zend\ModuleManager\Feature\ConfigProviderInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;

use GarminVideos\Model\VideoItem;
use GarminVideos\Model\VideoItemTable;

class Module implements AutoloaderProviderInterface, ConfigProviderInterface
{
	public function getAutoloaderConfig()
	{
		return array(
				'Zend\Loader\ClassMapAutoloader' => array(
						__DIR__ . '/autoload_classmap.php',
				),
				'Zend\Loader\StandardAutoloader' => array(
						'namespaces' => array(
								__NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
						),
				),
		);
	}

	public function getConfig()
	{
		return include __DIR__ . '/config/module.config.php';
	}
	
	public function getServiceConfig()
	{
		return array(
				'factories' => array(
						'GarminVideos\Model\VideoItemTable' =>  function($sm) {
							$tableGateway = $sm->get('VideoItemTableGateway');
							$table = new VideoItemTable($tableGateway);
							return $table;
						},
						'VideoItemTableGateway' => function ($sm) {
							$dbAdapter = $sm->get('sqlite_garminvideos');
							$resultSetPrototype = new ResultSet();
							$resultSetPrototype->setArrayObjectPrototype(new VideoItem());
							return new TableGateway('VideoItem', $dbAdapter, null, $resultSetPrototype);
						},
				),
		);
	}
}
?>