<?php

namespace FiiSoft\Test\Tools\Filesystem;

use FiiSoft\Tools\Filesystem\File;
use FiiSoft\Tools\Filesystem\FileLocation;
use FiiSoft\Tools\Filesystem\FileLocationConfig;

class FileTest extends \PHPUnit_Framework_TestCase
{
    /** @var FileLocationConfig */
    private $config;
    
    /** @var FileLocation */
    private $fl;
    
    public function setUp()
    {
        $this->config = new FileLocationConfig();
        $this->config->type = 'local';
        $this->config->root = dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'files';
        
        $this->fl = new FileLocation($this->config);
    }
    
    public function test_file_can_be_accessed_by_name_through_constructor()
    {
        $fileLocation = new FileLocation(new FileLocationConfig([
            'type' => 'local',
            'root' => dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR,
            'path' => 'files',
        ]));
        
        $file = new File($fileLocation, 'file1.txt');
    
        self::assertSame('file1.txt', $file->basename);
        self::assertSame('file1', $file->name);
        self::assertSame('txt', $file->ext);
        self::assertSame(17, $file->size);
        self::assertSame('MIT License file1', $file->content);
    }
}
