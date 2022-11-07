<?php

namespace NSWDPC\CustomThumbnail\Models;

use Silverstripe\ORM\DataExtension;
use Silverstripe\Forms\FieldList;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\Core\Config\Config;
use Silverstripe\Assets\Image;
use Silverstripe\Assets\File;
use Silverstripe\Assets\Folder;


class FileThumbnail extends Image {

    /**
     * @var string
     */
    private static $custom_thumbail_directory = 'CustomThumbnails';

    /**
     * @var array
     */
    private static $allowed_types = ['jpg','jpeg','gif','png', 'webp'];

    /**
     * @var array
     */
    private static $belongs_to = [
        'ParentFile' => File::class.'.CustomThumbnail'
    ];

    /**
     * Return the configured upload directory or the default fallback
     * @return string
     */
    public static function getCustomThumbnailUploadDirectory()
    {
        $folder_name = Config::inst()->get(self::class, 'custom_thumbail_directory');
        if (!$folder_name) {
            $folder_name = 'CustomThumbnails';
        }
        return $folder_name;
    }

    /**
     * Allowed file types for this file
     */
    public static function getAllowedFileTypes() {
        $allowed_types = Config::inst()->get(self::class, 'allowed_types');
        if (!$allowed_types) {
            $allowed_types = 'jpg,jpeg,gif,png,webp';
        }
        return $allowed_types;
    }

}
