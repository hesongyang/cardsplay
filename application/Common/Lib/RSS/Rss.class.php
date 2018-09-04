<?php namespace Common\Lib\RSS;
class RSS
{
	protected $channel_title = '';
	protected $channel_link = '';
	protected $channel_description = '';
	protected $channel_imgurl = '';
	protected $language = 'zh_CN';
	protected $pubDate = '';
	protected $lastBuildDate = '';
	protected $generator = 'YBlog RSS Generator';
	protected $items = array();

	public function __construct($title, $link, $description, $imgurl = '')
	{
		$this->channel_title = $title;
		$this->channel_link = $link;
		$this->channel_description = $description;
		$this->channel_imgurl = $imgurl;
		$this->pubDate = Date('Y-m-d H:i:s', time());
		$this->lastBuildDate = Date('Y-m-d H:i:s', time());
	}

	public function Config($key, $value)
	{
		$this->{$key} = $value;
	}

	function AddItem($title, $link, $description, $pubDate)
	{
		$this->items[] = array('title' => $title, 'link' => $link, 'description' => $description, 'pubDate' => $pubDate);
	}

	public function Fetch()
	{
		$rss = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\r\n";
		$rss = "<rss version=\"2.0\">\r\n";
		$rss .= "<channel>\r\n";
		$rss .= "<title><![CDATA[{$this->channel_title}]]></title>\r\n";
		$rss .= "<description><![CDATA[{$this->channel_description}]]></description>\r\n";
		$rss .= "<link>{$this->channel_link}</link>\r\n";
		$rss .= "<language>{$this->language}</language>\r\n";
		if (!empty($this->pubDate)) $rss .= "<pubDate>{$this->pubDate}</pubDate>\r\n";
		if (!empty($this->lastBuildDate)) $rss .= "<lastBuildDate>{$this->lastBuildDate}</lastBuildDate>\r\n";
		if (!empty($this->generator)) $rss .= "<generator>{$this->generator}</generator>\r\n";
		$rss .= "<ttl>5</ttl>\r\n";
		if (!empty($this->channel_imgurl)) {
			$rss .= "<image>\r\n";
			$rss .= "<title><![CDATA[{$this->channel_title}]]></title>\r\n";
			$rss .= "<link>{$this->channel_link}</link>\r\n";
			$rss .= "<url>{$this->channel_imgurl}</url>\r\n";
			$rss .= "</image>\r\n";
		}
		for ($i = 0; $i < count($this->items); $i++) {
			$rss .= "<item>\r\n";
			$rss .= "<title><![CDATA[{$this->items[$i]['title']}]]></title>\r\n";
			$rss .= "<link>{$this->items[$i]['link']}</link>\r\n";
			$rss .= "<description><![CDATA[{$this->items[$i]['description']}]]></description>\r\n";
			$rss .= "<pubDate>{$this->items[$i]['pubDate']}</pubDate>\r\n";
			$rss .= "</item>\r\n";
		}
		$rss .= "</channel>\r\n</rss>";
		return $rss;
	}

	public function Display()
	{
		header("Content-Type: text/xml; charset=utf-8");
		echo $this->Fetch();
		exit;
	}
} 