<?php
namespace News\View\Helper;
 
use Zend\View\Helper\AbstractHelper;

class NewsImageUrl extends AbstractHelper
{
    /**
     * Thumbnails url
     * @var string
     */
    protected $thumbnailsUrl;

    /**
     * Images url
     * @var string
     */
    protected $imagesUrl;

    /**
     * Class constructor
     *
     * @param string $thumbnailsUrl
     * @param string $imagesUrl
     */
    public function __construct($thumbnailsUrl, $imagesUrl)
    {
        $this->thumbnailsUrl = $thumbnailsUrl;
        $this->imagesUrl = $imagesUrl;
    }

    /**
     * News image url
     *
     * @param string $imageName
     * @param boolean $thumbnail
     * @return string
     */
    public function __invoke($imageName = null, $thumbnail = true)
    {
        return $thumbnail ? $this->thumbnailsUrl . $imageName : $this->imagesUrl . $imageName;
    }
}