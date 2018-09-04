<?php
namespace PHPThumb;
class GD extends PHPThumb
{
	protected $oldImage;
	protected $workingImage;
	protected $currentDimensions;
	protected $newDimensions;
	protected $options;
	protected $maxWidth;
	protected $maxHeight;
	protected $percent;

	public function __construct($fileName, $options = array(), array $plugins = array())
	{
		parent::__construct($fileName, $options, $plugins);
		$this->determineFormat();
		$this->verifyFormatCompatiblity();
		switch ($this->format) {
			case 'GIF':
				$this->oldImage = imagecreatefromgif($this->fileName);
				break;
			case 'JPG':
				$this->oldImage = imagecreatefromjpeg($this->fileName);
				break;
			case 'PNG':
				$this->oldImage = imagecreatefrompng($this->fileName);
				break;
			case 'STRING':
				$this->oldImage = imagecreatefromstring($this->fileName);
				break;
		}
		$this->currentDimensions = array('width' => imagesx($this->oldImage), 'height' => imagesy($this->oldImage));
	}

	public function __destruct()
	{
		if (is_resource($this->oldImage)) {
			imagedestroy($this->oldImage);
		}
		if (is_resource($this->workingImage)) {
			imagedestroy($this->workingImage);
		}
	}

	public function pad($width, $height, $color = array(255, 255, 255))
	{
		if ($width == $this->currentDimensions['width'] && $height == $this->currentDimensions['height']) {
			return $this;
		}
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($width, $height);
		} else {
			$this->workingImage = imagecreate($width, $height);
		}
		$fillColor = imagecolorallocate($this->workingImage, $color[0], $color[1], $color[2]);
		imagefill($this->workingImage, 0, 0, $fillColor);
		imagecopyresampled($this->workingImage, $this->oldImage, intval(($width - $this->currentDimensions['width']) / 2), intval(($height - $this->currentDimensions['height']) / 2), 0, 0, $this->currentDimensions['width'], $this->currentDimensions['height'], $this->currentDimensions['width'], $this->currentDimensions['height']);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $width;
		$this->currentDimensions['height'] = $height;
		return $this;
	}

	public function resize($maxWidth = 0, $maxHeight = 0)
	{
		if (!is_numeric($maxWidth)) {
			throw new \InvalidArgumentException('$maxWidth must be numeric');
		}
		if (!is_numeric($maxHeight)) {
			throw new \InvalidArgumentException('$maxHeight must be numeric');
		}
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($maxHeight) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $maxHeight;
			$this->maxWidth = (intval($maxWidth) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $maxWidth;
		} else {
			$this->maxHeight = intval($maxHeight);
			$this->maxWidth = intval($maxWidth);
		}
		$this->calcImageSize($this->currentDimensions['width'], $this->currentDimensions['height']);
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		} else {
			$this->workingImage = imagecreate($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		}
		$this->preserveAlpha();
		imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, 0, 0, $this->newDimensions['newWidth'], $this->newDimensions['newHeight'], $this->currentDimensions['width'], $this->currentDimensions['height']);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $this->newDimensions['newWidth'];
		$this->currentDimensions['height'] = $this->newDimensions['newHeight'];
		return $this;
	}

	public function adaptiveResize($width, $height)
	{
		if ((!is_numeric($width) || $width == 0) && (!is_numeric($height) || $height == 0)) {
			throw new \InvalidArgumentException('$width and $height must be numeric and greater than zero');
		}
		if (!is_numeric($width) || $width == 0) {
			$width = ($height * $this->currentDimensions['width']) / $this->currentDimensions['height'];
		}
		if (!is_numeric($height) || $height == 0) {
			$height = ($width * $this->currentDimensions['height']) / $this->currentDimensions['width'];
		}
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($height) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $height;
			$this->maxWidth = (intval($width) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $width;
		} else {
			$this->maxHeight = intval($height);
			$this->maxWidth = intval($width);
		}
		$this->calcImageSizeStrict($this->currentDimensions['width'], $this->currentDimensions['height']);
		$this->resize($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($height) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $height;
			$this->maxWidth = (intval($width) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $width;
		} else {
			$this->maxHeight = intval($height);
			$this->maxWidth = intval($width);
		}
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($this->maxWidth, $this->maxHeight);
		} else {
			$this->workingImage = imagecreate($this->maxWidth, $this->maxHeight);
		}
		$this->preserveAlpha();
		$cropWidth = $this->maxWidth;
		$cropHeight = $this->maxHeight;
		$cropX = 0;
		$cropY = 0;
		if ($this->currentDimensions['width'] > $this->maxWidth) {
			$cropX = intval(($this->currentDimensions['width'] - $this->maxWidth) / 2);
		} elseif ($this->currentDimensions['height'] > $this->maxHeight) {
			$cropY = intval(($this->currentDimensions['height'] - $this->maxHeight) / 2);
		}
		imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $this->maxWidth;
		$this->currentDimensions['height'] = $this->maxHeight;
		return $this;
	}

	public function adaptiveResizePercent($width, $height, $percent = 50)
	{
		if (!is_numeric($width) || $width == 0) {
			throw new \InvalidArgumentException('$width must be numeric and greater than zero');
		}
		if (!is_numeric($height) || $height == 0) {
			throw new \InvalidArgumentException('$height must be numeric and greater than zero');
		}
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($height) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $height;
			$this->maxWidth = (intval($width) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $width;
		} else {
			$this->maxHeight = intval($height);
			$this->maxWidth = intval($width);
		}
		$this->calcImageSizeStrict($this->currentDimensions['width'], $this->currentDimensions['height']);
		$this->resize($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($height) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $height;
			$this->maxWidth = (intval($width) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $width;
		} else {
			$this->maxHeight = intval($height);
			$this->maxWidth = intval($width);
		}
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($this->maxWidth, $this->maxHeight);
		} else {
			$this->workingImage = imagecreate($this->maxWidth, $this->maxHeight);
		}
		$this->preserveAlpha();
		$cropWidth = $this->maxWidth;
		$cropHeight = $this->maxHeight;
		$cropX = 0;
		$cropY = 0;
		if ($percent > 100) {
			$percent = 100;
		} elseif ($percent < 1) {
			$percent = 1;
		}
		if ($this->currentDimensions['width'] > $this->maxWidth) {
			$maxCropX = $this->currentDimensions['width'] - $this->maxWidth;
			$cropX = intval(($percent / 100) * $maxCropX);
		} elseif ($this->currentDimensions['height'] > $this->maxHeight) {
			$maxCropY = $this->currentDimensions['height'] - $this->maxHeight;
			$cropY = intval(($percent / 100) * $maxCropY);
		}
		imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $this->maxWidth;
		$this->currentDimensions['height'] = $this->maxHeight;
		return $this;
	}

	public function adaptiveResizeQuadrant($width, $height, $quadrant = 'C')
	{
		if (!is_numeric($width) || $width == 0) {
			throw new \InvalidArgumentException('$width must be numeric and greater than zero');
		}
		if (!is_numeric($height) || $height == 0) {
			throw new \InvalidArgumentException('$height must be numeric and greater than zero');
		}
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($height) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $height;
			$this->maxWidth = (intval($width) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $width;
		} else {
			$this->maxHeight = intval($height);
			$this->maxWidth = intval($width);
		}
		$this->calcImageSizeStrict($this->currentDimensions['width'], $this->currentDimensions['height']);
		$this->resize($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		if ($this->options['resizeUp'] === false) {
			$this->maxHeight = (intval($height) > $this->currentDimensions['height']) ? $this->currentDimensions['height'] : $height;
			$this->maxWidth = (intval($width) > $this->currentDimensions['width']) ? $this->currentDimensions['width'] : $width;
		} else {
			$this->maxHeight = intval($height);
			$this->maxWidth = intval($width);
		}
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($this->maxWidth, $this->maxHeight);
		} else {
			$this->workingImage = imagecreate($this->maxWidth, $this->maxHeight);
		}
		$this->preserveAlpha();
		$cropWidth = $this->maxWidth;
		$cropHeight = $this->maxHeight;
		$cropX = 0;
		$cropY = 0;
		if ($this->currentDimensions['width'] > $this->maxWidth) {
			switch ($quadrant) {
				case 'L':
					$cropX = 0;
					break;
				case 'R':
					$cropX = intval(($this->currentDimensions['width'] - $this->maxWidth));
					break;
				case 'C':
				default:
					$cropX = intval(($this->currentDimensions['width'] - $this->maxWidth) / 2);
					break;
			}
		} elseif ($this->currentDimensions['height'] > $this->maxHeight) {
			switch ($quadrant) {
				case 'T':
					$cropY = 0;
					break;
				case 'B':
					$cropY = intval(($this->currentDimensions['height'] - $this->maxHeight));
					break;
				case 'C':
				default:
					$cropY = intval(($this->currentDimensions['height'] - $this->maxHeight) / 2);
					break;
			}
		}
		imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $cropX, $cropY, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $this->maxWidth;
		$this->currentDimensions['height'] = $this->maxHeight;
		return $this;
	}

	public function resizePercent($percent = 0)
	{
		if (!is_numeric($percent)) {
			throw new \InvalidArgumentException ('$percent must be numeric');
		}
		$this->percent = intval($percent);
		$this->calcImageSizePercent($this->currentDimensions['width'], $this->currentDimensions['height']);
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		} else {
			$this->workingImage = imagecreate($this->newDimensions['newWidth'], $this->newDimensions['newHeight']);
		}
		$this->preserveAlpha();
		imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, 0, 0, $this->newDimensions['newWidth'], $this->newDimensions['newHeight'], $this->currentDimensions['width'], $this->currentDimensions['height']);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $this->newDimensions['newWidth'];
		$this->currentDimensions['height'] = $this->newDimensions['newHeight'];
		return $this;
	}

	public function cropFromCenter($cropWidth, $cropHeight = null)
	{
		if (!is_numeric($cropWidth)) {
			throw new \InvalidArgumentException('$cropWidth must be numeric');
		}
		if ($cropHeight !== null && !is_numeric($cropHeight)) {
			throw new \InvalidArgumentException('$cropHeight must be numeric');
		}
		if ($cropHeight === null) {
			$cropHeight = $cropWidth;
		}
		$cropWidth = ($this->currentDimensions['width'] < $cropWidth) ? $this->currentDimensions['width'] : $cropWidth;
		$cropHeight = ($this->currentDimensions['height'] < $cropHeight) ? $this->currentDimensions['height'] : $cropHeight;
		$cropX = intval(($this->currentDimensions['width'] - $cropWidth) / 2);
		$cropY = intval(($this->currentDimensions['height'] - $cropHeight) / 2);
		$this->crop($cropX, $cropY, $cropWidth, $cropHeight);
		return $this;
	}

	public function crop($startX, $startY, $cropWidth, $cropHeight)
	{
		if (!is_numeric($startX)) {
			throw new \InvalidArgumentException('$startX must be numeric');
		}
		if (!is_numeric($startY)) {
			throw new \InvalidArgumentException('$startY must be numeric');
		}
		if (!is_numeric($cropWidth)) {
			throw new \InvalidArgumentException('$cropWidth must be numeric');
		}
		if (!is_numeric($cropHeight)) {
			throw new \InvalidArgumentException('$cropHeight must be numeric');
		}
		$cropWidth = ($this->currentDimensions['width'] < $cropWidth) ? $this->currentDimensions['width'] : $cropWidth;
		$cropHeight = ($this->currentDimensions['height'] < $cropHeight) ? $this->currentDimensions['height'] : $cropHeight;
		if (($startX + $cropWidth) > $this->currentDimensions['width']) {
			$startX = ($this->currentDimensions['width'] - $cropWidth);
		}
		if (($startY + $cropHeight) > $this->currentDimensions['height']) {
			$startY = ($this->currentDimensions['height'] - $cropHeight);
		}
		if ($startX < 0) {
			$startX = 0;
		}
		if ($startY < 0) {
			$startY = 0;
		}
		if (function_exists('imagecreatetruecolor')) {
			$this->workingImage = imagecreatetruecolor($cropWidth, $cropHeight);
		} else {
			$this->workingImage = imagecreate($cropWidth, $cropHeight);
		}
		$this->preserveAlpha();
		imagecopyresampled($this->workingImage, $this->oldImage, 0, 0, $startX, $startY, $cropWidth, $cropHeight, $cropWidth, $cropHeight);
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $cropWidth;
		$this->currentDimensions['height'] = $cropHeight;
		return $this;
	}

	public function rotateImage($direction = 'CW')
	{
		if ($direction == 'CW') {
			$this->rotateImageNDegrees(90);
		} else {
			$this->rotateImageNDegrees(-90);
		}
		return $this;
	}

	public function rotateImageNDegrees($degrees)
	{
		if (!is_numeric($degrees)) {
			throw new \InvalidArgumentException('$degrees must be numeric');
		}
		if (!function_exists('imagerotate')) {
			throw new \RuntimeException('Your version of GD does not support_data image rotation');
		}
		$this->workingImage = imagerotate($this->oldImage, $degrees, 0);
		$newWidth = $this->currentDimensions['height'];
		$newHeight = $this->currentDimensions['width'];
		$this->oldImage = $this->workingImage;
		$this->currentDimensions['width'] = $newWidth;
		$this->currentDimensions['height'] = $newHeight;
		return $this;
	}

	public function imageFilter($filter, $arg1 = false, $arg2 = false, $arg3 = false, $arg4 = false)
	{
		if (!is_numeric($filter)) {
			throw new \InvalidArgumentException('$filter must be numeric');
		}
		if (!function_exists('imagefilter')) {
			throw new \RuntimeException('Your version of GD does not support_data image filters');
		}
		$result = false;
		if ($arg1 === false) {
			$result = imagefilter($this->oldImage, $filter);
		} elseif ($arg2 === false) {
			$result = imagefilter($this->oldImage, $filter, $arg1);
		} elseif ($arg3 === false) {
			$result = imagefilter($this->oldImage, $filter, $arg1, $arg2);
		} elseif ($arg4 === false) {
			$result = imagefilter($this->oldImage, $filter, $arg1, $arg2, $arg3);
		} else {
			$result = imagefilter($this->oldImage, $filter, $arg1, $arg2, $arg3, $arg4);
		}
		if (!$result) {
			throw new \RuntimeException('GD imagefilter failed');
		}
		$this->workingImage = $this->oldImage;
		return $this;
	}

	public function show($rawData = false)
	{
		if ($this->plugins) {
			foreach ($this->plugins as $plugin) {
				$plugin->execute($this);
			}
		}
		if (headers_sent() && php_sapi_name() != 'cli') {
			throw new \RuntimeException('Cannot show image, headers have already been sent');
		}
		if ($this->options['interlace'] === true) {
			imageinterlace($this->oldImage, 1);
		} elseif ($this->options['interlace'] === false) {
			imageinterlace($this->oldImage, 0);
		}
		switch ($this->format) {
			case 'GIF':
				if ($rawData === false) {
					header('Content-type: image/gif');
				}
				imagegif($this->oldImage);
				break;
			case 'JPG':
				if ($rawData === false) {
					header('Content-type: image/jpeg');
				}
				imagejpeg($this->oldImage, null, $this->options['jpegQuality']);
				break;
			case 'PNG':
			case 'STRING':
				if ($rawData === false) {
					header('Content-type: image/png');
				}
				imagepng($this->oldImage);
				break;
		}
		return $this;
	}

	public function getImageAsString()
	{
		$data = null;
		ob_start();
		$this->show(true);
		$data = ob_get_contents();
		ob_end_clean();
		return $data;
	}

	public function save($fileName, $format = null)
	{
		$validFormats = array('GIF', 'JPG', 'PNG');
		$format = ($format !== null) ? strtoupper($format) : $this->format;
		if (!in_array($format, $validFormats)) {
			throw new \InvalidArgumentException("Invalid format type specified in save function: {$format}");
		}
		if (!is_writeable(dirname($fileName))) {
			if ($this->options['correctPermissions'] === true) {
				@chmod(dirname($fileName), 0777);
				if (!is_writeable(dirname($fileName))) {
					throw new \RuntimeException("File is not writeable, and could not correct permissions: {$fileName}");
				}
			} else {
				throw new \RuntimeException("File not writeable: {$fileName}");
			}
		}
		if ($this->options['interlace'] === true) {
			imageinterlace($this->oldImage, 1);
		} elseif ($this->options['interlace'] === false) {
			imageinterlace($this->oldImage, 0);
		}
		switch ($format) {
			case 'GIF':
				imagegif($this->oldImage, $fileName);
				break;
			case 'JPG':
				imagejpeg($this->oldImage, $fileName, $this->options['jpegQuality']);
				break;
			case 'PNG':
				imagepng($this->oldImage, $fileName);
				break;
		}
		return $this;
	}

	public function setOptions(array $options = array())
	{
		if (sizeof($this->options) == 0) {
			$defaultOptions = array('resizeUp' => false, 'jpegQuality' => 100, 'correctPermissions' => false, 'preserveAlpha' => true, 'alphaMaskColor' => array(255, 255, 255), 'preserveTransparency' => true, 'transparencyMaskColor' => array(0, 0, 0), 'interlace' => null);
		} else {
			$defaultOptions = $this->options;
		}
		$this->options = array_merge($defaultOptions, $options);
		return $this;
	}

	public function getCurrentDimensions()
	{
		return $this->currentDimensions;
	}

	public function setCurrentDimensions($currentDimensions)
	{
		$this->currentDimensions = $currentDimensions;
		return $this;
	}

	public function getMaxHeight()
	{
		return $this->maxHeight;
	}

	public function setMaxHeight($maxHeight)
	{
		$this->maxHeight = $maxHeight;
		return $this;
	}

	public function getMaxWidth()
	{
		return $this->maxWidth;
	}

	public function setMaxWidth($maxWidth)
	{
		$this->maxWidth = $maxWidth;
		return $this;
	}

	public function getNewDimensions()
	{
		return $this->newDimensions;
	}

	public function setNewDimensions($newDimensions)
	{
		$this->newDimensions = $newDimensions;
		return $this;
	}

	public function getOptions()
	{
		return $this->options;
	}

	public function getPercent()
	{
		return $this->percent;
	}

	public function setPercent($percent)
	{
		$this->percent = $percent;
		return $this;
	}

	public function getOldImage()
	{
		return $this->oldImage;
	}

	public function setOldImage($oldImage)
	{
		$this->oldImage = $oldImage;
		return $this;
	}

	public function getWorkingImage()
	{
		return $this->workingImage;
	}

	public function setWorkingImage($workingImage)
	{
		$this->workingImage = $workingImage;
		return $this;
	}

	protected function calcWidth($width, $height)
	{
		$newWidthPercentage = (100 * $this->maxWidth) / $width;
		$newHeight = ($height * $newWidthPercentage) / 100;
		return array('newWidth' => intval($this->maxWidth), 'newHeight' => intval($newHeight));
	}

	protected function calcHeight($width, $height)
	{
		$newHeightPercentage = (100 * $this->maxHeight) / $height;
		$newWidth = ($width * $newHeightPercentage) / 100;
		return array('newWidth' => ceil($newWidth), 'newHeight' => ceil($this->maxHeight));
	}

	protected function calcPercent($width, $height)
	{
		$newWidth = ($width * $this->percent) / 100;
		$newHeight = ($height * $this->percent) / 100;
		return array('newWidth' => ceil($newWidth), 'newHeight' => ceil($newHeight));
	}

	protected function calcImageSize($width, $height)
	{
		$newSize = array('newWidth' => $width, 'newHeight' => $height);
		if ($this->maxWidth > 0) {
			$newSize = $this->calcWidth($width, $height);
			if ($this->maxHeight > 0 && $newSize['newHeight'] > $this->maxHeight) {
				$newSize = $this->calcHeight($newSize['newWidth'], $newSize['newHeight']);
			}
		}
		if ($this->maxHeight > 0) {
			$newSize = $this->calcHeight($width, $height);
			if ($this->maxWidth > 0 && $newSize['newWidth'] > $this->maxWidth) {
				$newSize = $this->calcWidth($newSize['newWidth'], $newSize['newHeight']);
			}
		}
		$this->newDimensions = $newSize;
	}

	protected function calcImageSizeStrict($width, $height)
	{
		if ($this->maxWidth >= $this->maxHeight) {
			if ($width > $height) {
				$newDimensions = $this->calcHeight($width, $height);
				if ($newDimensions['newWidth'] < $this->maxWidth) {
					$newDimensions = $this->calcWidth($width, $height);
				}
			} elseif ($height >= $width) {
				$newDimensions = $this->calcWidth($width, $height);
				if ($newDimensions['newHeight'] < $this->maxHeight) {
					$newDimensions = $this->calcHeight($width, $height);
				}
			}
		} elseif ($this->maxHeight > $this->maxWidth) {
			if ($width >= $height) {
				$newDimensions = $this->calcWidth($width, $height);
				if ($newDimensions['newHeight'] < $this->maxHeight) {
					$newDimensions = $this->calcHeight($width, $height);
				}
			} elseif ($height > $width) {
				$newDimensions = $this->calcHeight($width, $height);
				if ($newDimensions['newWidth'] < $this->maxWidth) {
					$newDimensions = $this->calcWidth($width, $height);
				}
			}
		}
		$this->newDimensions = $newDimensions;
	}

	protected function calcImageSizePercent($width, $height)
	{
		if ($this->percent > 0) {
			$this->newDimensions = $this->calcPercent($width, $height);
		}
	}

	protected function determineFormat()
	{
		$formatInfo = getimagesize($this->fileName);
		if ($formatInfo === false) {
			if ($this->remoteImage) {
				throw new \Exception("Could not determine format of remote image: {$this->fileName}");
			} else {
				throw new \Exception("File is not a valid image: {$this->fileName}");
			}
		}
		$mimeType = isset($formatInfo['mime']) ? $formatInfo['mime'] : null;
		switch ($mimeType) {
			case 'image/gif':
				$this->format = 'GIF';
				break;
			case 'image/jpeg':
				$this->format = 'JPG';
				break;
			case 'image/png':
				$this->format = 'PNG';
				break;
			default:
				throw new \Exception("Image format not supported: {$mimeType}");
		}
	}

	protected function verifyFormatCompatiblity()
	{
		$isCompatible = true;
		$gdInfo = gd_info();
		switch ($this->format) {
			case 'GIF':
				$isCompatible = $gdInfo['GIF Create Support'];
				break;
			case 'JPG':
				$isCompatible = (isset($gdInfo['JPG Support']) || isset($gdInfo['JPEG Support'])) ? true : false;
				break;
			case 'PNG':
				$isCompatible = $gdInfo[$this->format . ' Support'];
				break;
			default:
				$isCompatible = false;
		}
		if (!$isCompatible) {
			$isCompatible = $gdInfo['JPEG Support'];
			if (!$isCompatible) {
				throw new \Exception("Your GD installation does not support_data {$this->format} image types");
			}
		}
	}

	protected function preserveAlpha()
	{
		if ($this->format == 'PNG' && $this->options['preserveAlpha'] === true) {
			imagealphablending($this->workingImage, false);
			$colorTransparent = imagecolorallocatealpha($this->workingImage, $this->options['alphaMaskColor'][0], $this->options['alphaMaskColor'][1], $this->options['alphaMaskColor'][2], 0);
			imagefill($this->workingImage, 0, 0, $colorTransparent);
			imagesavealpha($this->workingImage, true);
		}
		if ($this->format == 'GIF' && $this->options['preserveTransparency'] === true) {
			$colorTransparent = imagecolorallocate($this->workingImage, $this->options['transparencyMaskColor'][0], $this->options['transparencyMaskColor'][1], $this->options['transparencyMaskColor'][2]);
			imagecolortransparent($this->workingImage, $colorTransparent);
			imagetruecolortopalette($this->workingImage, true, 256);
		}
	}
} 