<?php
namespace PHPThumb;
abstract class PHPThumb
{
	protected $fileName;
	protected $format;
	protected $remoteImage;
	protected $plugins;

	public function __construct($fileName, array $options = array(), array $plugins = array())
	{
		$this->fileName = $fileName;
		$this->remoteImage = false;
		if (!$this->validateRequestedResource($fileName)) {
			throw new \InvalidArgumentException("Image file not found: {$fileName}");
		}
		$this->setOptions($options);
		$this->plugins = $plugins;
	}

	abstract public function setOptions(array $options = array());

	protected function validateRequestedResource($filename)
	{
		if (false !== filter_var($filename, FILTER_VALIDATE_URL)) {
			$this->remoteImage = true;
			return true;
		}
		if (file_exists($filename)) {
			return true;
		}
		return false;
	}

	public function getFileName()
	{
		return $this->fileName;
	}

	public function setFileName($fileName)
	{
		$this->fileName = $fileName;
		return $this;
	}

	public function getFormat()
	{
		return $this->format;
	}

	public function setFormat($format)
	{
		$this->format = $format;
		return $this;
	}

	public function getIsRemoteImage()
	{
		return $this->remoteImage;
	}
} 