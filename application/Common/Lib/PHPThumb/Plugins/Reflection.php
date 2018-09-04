<?php
namespace PHPThumb\Plugins;
class Reflection implements \PHPThumb\PluginInterface
{
	protected $currentDimensions;
	protected $workingImage;
	protected $newImage;
	protected $options;
	protected $percent;
	protected $reflection;
	protected $white;
	protected $border;
	protected $borderColor;

	public function __construct($percent, $reflection, $white, $border, $borderColor)
	{
		$this->percent = $percent;
		$this->reflection = $reflection;
		$this->white = $white;
		$this->border = $border;
		$this->borderColor = $borderColor;
	}

	public function execute($phpthumb)
	{
		$this->currentDimensions = $phpthumb->getCurrentDimensions();
		$this->workingImage = $phpthumb->getWorkingImage();
		$this->newImage = $phpthumb->getOldImage();
		$this->options = $phpthumb->getOptions();
		$width = $this->currentDimensions['width'];
		$height = $this->currentDimensions['height'];
		$this->reflectionHeight = intval($height * ($this->reflection / 100));
		$newHeight = $height + $this->reflectionHeight;
		$reflectedPart = $height * ($this->percent / 100);
		$this->workingImage = imagecreatetruecolor($width, $newHeight);
		imagealphablending($this->workingImage, true);
		$colorToPaint = imagecolorallocatealpha($this->workingImage, 255, 255, 255, 0);
		imagefilledrectangle($this->workingImage, 0, 0, $width, $newHeight, $colorToPaint);
		imagecopyresampled($this->workingImage, $this->newImage, 0, 0, 0, $reflectedPart, $width, $this->reflectionHeight, $width, ($height - $reflectedPart));
		$this->imageFlipVertical();
		imagecopy($this->workingImage, $this->newImage, 0, 0, 0, 0, $width, $height);
		imagealphablending($this->workingImage, true);
		for ($i = 0; $i < $this->reflectionHeight; $i++) {
			$colorToPaint = imagecolorallocatealpha($this->workingImage, 255, 255, 255, ($i / $this->reflectionHeight * -1 + 1) * $this->white);
			imagefilledrectangle($this->workingImage, 0, $height + $i, $width, $height + $i, $colorToPaint);
		}
		if ($this->border == true) {
			$rgb = $this->hex2rgb($this->borderColor, false);
			$colorToPaint = imagecolorallocate($this->workingImage, $rgb[0], $rgb[1], $rgb[2]);
			imageline($this->workingImage, 0, 0, $width, 0, $colorToPaint);
			imageline($this->workingImage, 0, $height, $width, $height, $colorToPaint);
			imageline($this->workingImage, 0, 0, 0, $height, $colorToPaint);
			imageline($this->workingImage, $width - 1, 0, $width - 1, $height, $colorToPaint);
		}
		if ($phpthumb->getFormat() == 'PNG') {
			$colorTransparent = imagecolorallocatealpha($this->workingImage, $this->options['alphaMaskColor'][0], $this->options['alphaMaskColor'][1], $this->options['alphaMaskColor'][2], 0);
			imagefill($this->workingImage, 0, 0, $colorTransparent);
			imagesavealpha($this->workingImage, true);
		}
		$phpthumb->setOldImage($this->workingImage);
		$this->currentDimensions['width'] = $width;
		$this->currentDimensions['height'] = $newHeight;
		$phpthumb->setCurrentDimensions($this->currentDimensions);
		return $phpthumb;
	}

	protected function imageFlipVertical()
	{
		$x_i = imagesx($this->workingImage);
		$y_i = imagesy($this->workingImage);
		for ($x = 0; $x < $x_i; $x++) {
			for ($y = 0; $y < $y_i; $y++) {
				imagecopy($this->workingImage, $this->workingImage, $x, $y_i - $y - 1, $x, $y, 1, 1);
			}
		}
	}

	protected function hex2rgb($hex, $asString = false)
	{
		if (0 === strpos($hex, '#')) {
			$hex = substr($hex, 1);
		} elseif (0 === strpos($hex, '&H')) {
			$hex = substr($hex, 2);
		}
		$cutpoint = ceil(strlen($hex) / 2) - 1;
		$rgb = explode(':', wordwrap($hex, $cutpoint, ':', $cutpoint), 3);
		$rgb[0] = (isset($rgb[0]) ? hexdec($rgb[0]) : 0);
		$rgb[1] = (isset($rgb[1]) ? hexdec($rgb[1]) : 0);
		$rgb[2] = (isset($rgb[2]) ? hexdec($rgb[2]) : 0);
		return ($asString ? "{$rgb[0]} {$rgb[1]} {$rgb[2]}" : $rgb);
	}
} 