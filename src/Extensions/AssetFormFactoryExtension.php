<?php

namespace NSWDPC\CustomThumbnail\Extensions;

use NSWDPC\CustomThumbnail\Models\FileThumbnail;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Extension;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Forms\Fieldlist;
use SilverStripe\Forms\LiteralField;
use SilverStripe\AssetAdmin\Forms\UploadField;

/**
 * Adds CustomThumbnail field to the form
 */
class AssetFormFactoryExtension extends Extension
{

    /**
     * CMS Fields
     * @return FieldList
     */
    public function updateFormFields($fields, $controller, $form_name, $context)
    {
        if ($form_name == 'fileSelectForm') {
            // cannot edit in select mode - there are no save/publish buttons
            return;
        }
        $record = isset($context['Record']) ? $context['Record'] : null;

        if(!$record
            || $record instanceof FileThumbnail
            || $record instanceof Folder
        ) {
            // FileThumbnails cannot allow FileThumbnails
            return;
        }
        $thumbnail_fields = $record->getThumbnailFields();
        $tab_name = _t(__CLASS__ . '.THUMBNAIL', 'Thumbnail');
        $fields->addFieldsToTab(
            'Editor.' . $tab_name,
            $thumbnail_fields
        );
    }
}
