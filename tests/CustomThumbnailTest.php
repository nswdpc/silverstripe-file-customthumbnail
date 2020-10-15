<?php

namespace NSWDPC\CustomThumbnail\Tests;

use NSWDPC\CustomThumbnail\Models\FileThumbnail;
use SilverStripe\Core\Config\Config;
use SilverStripe\Dev\SapphireTest;
use Silverstripe\Assets\Dev\TestAssetStore;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Assets\Image;
use SilverStripe\Forms\LiteralField;
use SilverStripe\AssetAdmin\Forms\UploadField;
use SilverStripe\MimeValidator\MimeUploadValidator;

/**
 * Unit test to verify custom thumnbnails
 * @author James
 */
class CustomThumbnailTest extends SapphireTest
{

    protected $usesDatabase = true;

    protected static $fixture_file = 'CustomThumbnailTest.yml';

    public function setUp() {
        parent::setUp();

        TestAssetStore::activate('data');
        $thumbs = FileThumbnail::get();
        foreach ($thumbs as $thumb) {
            $source_path = __DIR__ . '/data/' . $thumb->Name;
            $thumb->setFromLocalFile($source_path, $thumb->Filename);
            $thumb->write();
        }

        $files = File::get()->exclude(['ClassName' =>[ Folder::class, FileThumbnail::class]]);
        foreach ($files as $file) {
            $source_path = __DIR__ . '/data/' . $file->Name;
            $file->setFromLocalFile($source_path, $file->Filename);
            $file->write();
        }
    }

    public function tearDown()
    {
        TestAssetStore::reset();
        parent::tearDown();
    }

    public function testAssignThumbnail() {


        $file = $this->objFromFixture(File::class, 'file1');

        $thumb = $this->objFromFixture(FileThumbnail::class, 'thumb1');

        $this->assertTrue($thumb->exists(), "Thumb does not exist");

        $file->CustomThumbnailID = $thumb->ID;
        $file->write();

        $retrieved = $file->CustomThumbnail();

        $this->assertTrue($retrieved->getIsImage(), "Thumb is not an image");

        $directory = Config::inst()->get(FileThumbnail::class, 'custom_thumbail_directory');

        $file_thumb_directory = $file->getCustomThumbnailUploadDirectory();

        $this->assertEquals($directory . "/" . $file->ID, $file_thumb_directory, "The custom thumbnail directory {$file_thumb_directory} is not valid");

        $fields = $file->getThumbnailFields();

        // types from config
        $types = Config::inst()->get(FileThumbnail::class, 'allowed_types');
        foreach($fields as $field) {
            if($field instanceof UploadField) {
                $this->assertFalse($field->getIsMultiUpload(), "Multiupload should be false");
                $validator = $field->getValidator();
                $this->assertTrue($validator instanceof MimeUploadValidator, "Validator should be a MimeUploadValidator");
                $extensions = $field->getAllowedExtensions();
                $validator_extensions = $validator->getAllowedExtensions();
                $diff = array_diff( $types, $extensions, $validator_extensions);
                $this->assertEmpty($diff , "Allowed upload field types has these differences: " . implode(",", $diff));
            }
        }

        $parent = $thumb->ParentFile();
        $this->assertEquals($file->ID, $parent->ID, "Reverse file association failed");

    }

}
