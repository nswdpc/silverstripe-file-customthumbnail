<?php

namespace NSWDPC\CustomThumbnail\Extensions;

use NSWDPC\CustomThumbnail\Models\FileThumbnail;
use Silverstripe\ORM\DataExtension;
use Silverstripe\Forms\FieldList;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\FileField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\ORM\ValidationException;
use Silverstripe\Assets\Image;
use Silverstripe\Assets\Folder;
use SilverStripe\MimeValidator\MimeUploadValidator;

/**
 * Add CustomThumbnail Image record to File
 */
class FileExtension extends DataExtension
{

    /**
     * @inheritdoc
     */
    private static $has_one = [
        'CustomThumbnail' => FileThumbnail::class
    ];

    /**
     * @inheritdoc
     */
    private static $owns = [
        'CustomThumbnail'
    ];

    /**
     * Return the custom thumbnail directory, suffixed with the File ID
     * @return string
     */
    public function getCustomThumbnailUploadDirectory() {
        $folder_name = FileThumbnail::getCustomThumbnailUploadDirectory();
        return $folder_name . "/" . $this->owner->ID;
    }

    /**
     * Get fields to add to the form for this record
     * @return array
     */
    public function getThumbnailFields() {
        $fields = [];

        $fields[] = LiteralField::create(
            'CustomThumbnailHelper',
            '<p class="message notice">'
            . sprintf(
                _t(
                    __CLASS__ . '.THUMBNAIL_INSTRUCTION',
                    'Upload an image to act as a custom thumbnail for this %s'
                ),
                $this->owner->i18n_singular_name()
            )
            . '</p>'
        );


        $types = FileThumbnail::getAllowedFileTypes();
        $validator = MimeUploadValidator::create();
        $validator->setAllowedExtensions($types);
        $fields[] = UploadField::create('CustomThumbnail')
                    ->setValidator($validator)
                    ->setIsMultiUpload(false)
                    ->setAllowedExtensions($types)
                    ->setFolderName($this->owner->getCustomThumbnailUploadDirectory());
        return $fields;
    }

    /**
     * @inheritdoc
     */
    public function onBeforeWrite() {
        parent::onBeforeWrite();
        $thumb = $this->owner->CustomThumbnail();
        if($thumb && !$thumb->getIsImage()) {
            throw new ValidationException( _t(__CLASS__ . ".INVALID_THUMB_TYPE", "The thumbnail is not a valid image") );
        }
    }

    /**
     * @inheritdoc
     */
    public function updateCMSFields(FieldList $fields)
    {
        if(!$this->owner instanceof FileThumbnail
            && !$this->owner instanceof Folder
        ) {
            $fields->addFieldsToTab(
                'Root.Main',
                $this->owner->getThumbnailFields()
            );
        }
    }
}
