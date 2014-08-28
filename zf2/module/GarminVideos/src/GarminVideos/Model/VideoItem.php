<?php
namespace GarminVideos\Model;

class VideoItem implements \ArrayAccess
{
	public $videoId;
	public $title;
	public $by;
	public $author;
	public $viewCount;
	public $watched;
	public $isNew;
	public $createdAt;
	public $thumb;
	public $localThumb;
	public $localViewCount;
	public $clkCount;
	public $duration;
	
	private $errTmpl = "Video property - \"%s\" ";
	private $jsonKeys = array(
			"videoId" => array("type" => "string", "errorMsg" => " must be 11 characters"),
			"title" => array("type" => "string", "errorMsg" => " must be string"),
			"by" => array("type" => "string", "errorMsg" => " must be string"),
			"author" => array("type" => "string", "errorMsg" => " must be string"),
			"viewCount" => array("type" => "int", "errorMsg" => " must be interer")
	);
	
	public function __construct($dbVideoItem=NULL)
	{
		if($dbVideoItem)
			$this->exchangeArray($dbVideoItem);
	}
	
	public function offsetSet($offset, $value)
	{
		$this->$offset = $value;
	}
	public function offsetExists($offset)
	{
		return isset($this->$offset);
	}
	
	public function offsetUnset($offset)
	{
		if($this->offsetExists($offset))
			unset($this->$offset);
	}
	
	public function offsetGet($offset)
	{
		return $this->offsetExists($offset) ? $this->$offset : null;
	}
	
	public function exchangeArray($data)
	{
		if(is_array($data))
		{
			$this->videoId = $data["videoid"];
			$this->title = $data["title"];
			$this->by = $data["by"];
			$this->author = $data["author"];
			$this->viewCount = (int)$data["viewcount"];
			$this->createdAt = $data["created_at"];
			$this->thumb = $data["thumb"];
			$this->localThumb = $data["local_thumb"];
			$this->localViewCount = (int)$data["local_viewcount"];
			$this->clkCount = (int)$data["clkcount"];
			$this->duration = $data["duration"];
		}
	}
	
	public function toDict()
	{
		return array(
			"videoId" => $this->videoId,
			"title" => $this->title,
			"by" => $this->by,
			"author" => $this->author,
			"viewCount" => $this->viewCount
		);
	}
	
	public function toDbDict()
	{
		return array(
			"videoid" => $this->videoId,
			"title" => $this->title,
			"by" => $this->by,
			"author" => $this->author,
			"viewcount" => $this->viewCount
		);
	}
	
	public function toDbVideoIdDict()
	{
		return array(
			"videoid" => $this->videoId
		);
	}
	
	public function genVideoId()
	{
		$timestamp = time();
		$md5 = md5($timestamp);
		$base64 = base64_encode($md5);
		$this->videoId = substr($base64, 0, 11);
	}
	
	public function syncWithDb($dbVideoItem)
	{
		if(!$this->videoId)
			$this->videoId = $dbVideoItem["videoid"];
		if(!$this->title)
			$this->title = $dbVideoItem["title"];
		if(!$this->by)
			$this->by = $dbVideoItem["by"];
		if(!$this->author)
			$this->author = $dbVideoItem["author"];
		if(!$this->viewCount)
			$this->viewCount = $dbVideoItem["viewcount"];
	}

	public static function deserializeFromRequest()
	{
		$result = null;
		$videoItem = new VideoItem();
		
		if(isset($_GET["video"]))
		{
			$video = $_GET["video"];
			try {
				$video = json_decode($video, true);
			} catch (\Exception $e) {
			}
				
			if(is_array($video))
			{
				try {
					foreach (array_keys($videoItem->jsonKeys) as $jsonKey)
					{
						if(array_key_exists($jsonKey, $video))
						{
							switch ($videoItem->jsonKeys[$jsonKey]["type"])
							{
								case "string":
									if(is_string($video[$jsonKey]))
										$videoItem[$jsonKey] = $video[$jsonKey];
									else
										$result = self::genErrMsgs($result,
												sprintf($videoItem->errTmpl . $videoItem->jsonKeys[$jsonKey]["errorMsg"] . ", \"%s\" given",
														$jsonKey, gettype($video[$jsonKey])));
									break;
								case "int":
									if(is_int($video[$jsonKey]))
										$videoItem[$jsonKey] = $video[$jsonKey];
									else
										$result = self::genErrMsgs($result,
												sprintf($videoItem->errTmpl . $videoItem->jsonKeys[$jsonKey]["errorMsg"] . ", \"%s\" given",
														$jsonKey, gettype($video[$jsonKey])));										
									break;
							}
						}
					}
				} catch (\Exception $e) {
					$result = self::genErrMsgs($result, $e->getMessage());
				}
			}else{
				if($video === null)
					$result = self::genErrMsgs($result, "Video must be JSON object representation, format may error - " .
							"" . $_GET["video"] ." ");
				else
					$result = self::genErrMsgs($result, "Video must be JSON object representation, " .
							"\"" . $_GET["video"] ."\" - " . gettype($video) ." given");
			}
		}
		else
		{
			$result = self::genErrMsgs($result, "Invalid argument");
		}
		
		return $result ? $result : $videoItem;
	}
	
	
	public static function deserialize()
	{
		$result = null;
		$videoItem = new VideoItem();
		
		if(isset($_GET["video"]))
		{
			$video = $_GET["video"];
			try {
				$video = json_decode($video, true);
			} catch (\Exception $e) {
			}
			
			if(is_array($video))
			{
				try {
					if(array_key_exists("videoId", $video))
					{
						if(is_string($video["videoId"]))
						{
							$videoItem->videoId = $video["videoId"];
						}
						else
							$result = self::genErrMsgs($result, "VideoId must be 11 characters");
					}
					if(array_key_exists("title", $video))
					{
						if(is_string($video["title"]))
						{
							$videoItem->title = $video["title"];
						}
						else
							$result = self::genErrMsgs($result, "Video Title must be string");
					}
					if(array_key_exists("by", $video))
					{
						if(is_string($video["by"]))
						{
							$videoItem->by = $video["by"];
						}
						else
							$result = self::genErrMsgs($result, "Video By must be string");
					}
					if(array_key_exists("author", $video))
					{
						if(is_string($video["author"]))
						{
							$videoItem->author = $video["author"];
						}
						else
							$result = self::genErrMsgs($result, "Video Author must be string");
					}
					if(array_key_exists("viewCount", $video))
					{
						if(is_int($video["viewCount"]))
						{
							$videoItem->viewCount = $video["viewCount"];
						}
						else
							$result = self::genErrMsgs($result, "Video ViewCount must be interer");
					}
				} catch (\Exception $e) {
					$result = self::genErrMsgs($result, $e->getMessage());
				}
			}else{
				if($video === null)
					$result = self::genErrMsgs($result, "Video must be JSON object representation, format may error - " .
							"" . $_GET["video"] ." ");
				else
					$result = self::genErrMsgs($result, "Video must be JSON object representation, " .
							"\"" . $_GET["video"] ."\" - " . gettype($video) ." given");
			}
		}
		else
		{
			$result = self::genErrMsgs($result, "Invalid argument");
		}
		
		return $result ? $result : $videoItem;
		
	}
	
	protected static function genErrMsgs($result=null, $errMsg)
	{
		if(!$result)
			$result = array();
		
		if(!isset($result["errMsgs"]))
			$result["errMsgs"] = array();
		
		if(is_string($errMsg))
			array_push($result["errMsgs"], $errMsg);
		
		return $result;
	}
}

class LocalCategory
{
	const All = "all";
	const Garmin = "garmin";
	const TED = "ted";
	const Famous = "famous";
	const Others = "others";
	
	public static function fromString($category)
	{
		if(is_string($category))
		{
			$category_ = null;
			$category = strtolower($category);
			switch ($category)
			{
				case "all":
					$category_ = self::All;
					break;
				case "garmin":
					$category_ = self::Garmin;
					break;
				case "ted":
					$category_ = self::TED;
					break;
				case "famous":
					$category_ = self::Famous;
					break;
				case "others":
					$category_ = self::Others;
					break;
				default:
					throw new \InvalidArgumentException(sprintf("Category must be \"all|garmin|ted|famous|others\", \"%s\" given", $category));
			}
			return $category_;
		}
		else
		{
			throw new \InvalidArgumentException(sprintf("Category must be string, \"%s\" given", gettype($category)));
		}
	}
}