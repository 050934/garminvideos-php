<?php
namespace GarminVideos\Model;

use Zend\Db\TableGateway\TableGateway;
use GarminVideos\Model\LocalCategory;

class VideoItemTable
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
	
	public function getVideoTotalCount(array $excludeVideos=array(), $category=LocalCategory::All)
	{
		$sql = "SELECT COUNT(*) AS totalcount
				FROM VideoItem
				%s";
		$whereSql = "";
		if($category != LocalCategory::All)
		{
			$categorySql = sprintf("WHERE localcategory = '%s' ", $category);
			$whereSql .= $categorySql;
		}
		
		if($excludeVideos)
		{
			$exSqlList = array();
			foreach ($excludeVideos as $video)
			{
				if(is_string($video))
					array_push($exSqlList, $video);
				elseif ($video instanceof VideoItem)
					array_push($exSqlList, $video->videoId);
				else
					continue;
			}
			
			if (count($exSqlList) > 0)
			{
				if($whereSql)
					$whereSql .= (" AND videoid NOT IN('" . join("', '", $exSqlList) . "')");
				else
					$whereSql .= ("WHERE videoid NOT IN('" . join("', '", $exSqlList) . "')");
			}
		}
		
		$sql = sprintf($sql, $whereSql);
		
		/**
		 * @see http://stackoverflow.com/questions/15332671/how-to-run-raw-sql-query-with-zend-framework-2
		 */
		$stmt = $this->tableGateway->getAdapter()->query($sql, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
		foreach ($stmt as $row)
			return (int)$row["totalcount"];
		return 0;
	}
	
	public function getVideoItems($videoId=null, $pageSize=null, $pageNum=null,
						array $excludeVideos=array(), $category=LocalCategory::All)
	{
		$sql = "SELECT * FROM VideoItem";
		if(is_int($videoId) && is_int($pageSize))
		{
			$pageNum = $pageSize;
			$pageSize = $videoId;
			$videoId = null;
		}
		if($videoId == null && is_int($pageSize) && is_int($pageNum))
		{
			$top = $pageSize * $pageNum;
			$totals = $this->getVideoTotalCount($excludeVideos, $category);
			$pageCount = $totals % $pageSize == 0 ? $totals / $pageSize : $totals / $pageSize + 1;
			
			if($pageNum > $pageCount and $totals % $pageSize != 0)
				$pageSize = $totals % $pageSize;
			
			$sql = "
                  SELECT * FROM(
                      SELECT * FROM(
                          SELECT * FROM VideoItem
                          %s
                          ORDER BY created_at DESC
                          LIMIT 0, ?
                       )
                       ORDER BY created_at ASC
                       LIMIT ?
                  )ORDER BY created_at DESC			
			";
			
			$whereSql = "";
			if($category != LocalCategory::All)
			{
				$categorySql = sprintf("WHERE localcategory = '%s' ", $category);
				$whereSql .= $categorySql;
			}
			
			if($excludeVideos)
			{
				$exSqlList = array();
				foreach ($excludeVideos as $video)
				{
					if(is_string($video))
						array_push($exSqlList, $video);
					elseif ($video instanceof VideoItem)
						array_push($exSqlList, $video->videoId);
					else
						continue;
				}
					
				if (count($exSqlList) > 0)
				{
					if($whereSql)
						$whereSql .= (" AND videoid NOT IN('" . join("', '", $exSqlList) . "')");
					else
						$whereSql .= ("WHERE videoid NOT IN('" . join("', '", $exSqlList) . "')");
				}
			}
			
			$sql = sprintf($sql, $whereSql);
		}
		
		if($videoId == null && is_int($pageSize) && is_int($pageNum))
		{
			$resultset = $this->tableGateway->getAdapter()->query($sql, array($top, $pageSize));
		}
		else
		{
			$sql .= " ORDER BY created_at DESC";
			$resultset = $this->tableGateway->getAdapter()->query($sql);
		}
		
		return array_map(function($dbVideoItem){
			return new VideoItem($dbVideoItem);
		}, $resultset->toArray());
	}
	
	public function getVideoItem($videoId)
	{
		$selectField = "videoid";
		if(is_int($videoId))
			$selectField = "id";
				
		if($videoId instanceof VideoItem)
		{
			if($videoId->videoId)
				$videoId = $videoId->videoId;
		}
		
		$rowset = $this->tableGateway->select(array($selectField => $videoId));
		$videoItem = $rowset->current();
		if(!$videoItem)
		{
			throw new \Exception("Could not find video \"$videoId\"");
		}
		return $videoItem;
	}
	
	public function saveVideoItem(VideoItem $videoitem)
	{
		if($videoitem->videoId)
		{
			$_videoitem = $this->getVideoItem($videoitem->videoId);
			if($_videoitem)
			{
				$videoitem->syncWithDb($_videoitem);
				$data = $videoitem->toDbDict();
				$this->tableGateway->update($data, $videoitem->toDbVideoIdDict());
			}
		}
		else
		{//insert a videoitem
			while (true)
			{
				$videoitem->genVideoId();
				try {
					$_videoitem = $this->getVideoItem($videoitem->videoId);
					if(!$_videoitem)
						break;
				} catch (\Exception $e) {
					break;
				}
			}
			$data = $videoitem->toDbDict();
			$this->tableGateway->insert($data);
		}
		
		return $videoitem;
	}
	
	public function deleteVideoItem($videoId)
	{
		$video = $this->getVideoItem($videoId);

		if($video)
		{
			$selectField = "videoid";
			if(is_int($videoId))
				$selectField = "id";
			
			if($videoId instanceof VideoItem)
			{
				if($videoId->videoId)
					$videoId = $videoId->videoId;
			}
			$this->tableGateway->delete(array($selectField => $videoId));
			return $video;
		}
		return null;
	}
}