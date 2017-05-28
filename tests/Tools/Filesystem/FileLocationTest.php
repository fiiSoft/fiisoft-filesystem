<?php

namespace FiiSoft\Test\Tools\Filesystem;

use FiiSoft\Tools\Filesystem\FileLocation;
use FiiSoft\Tools\Filesystem\FileLocationConfig;
use FiiSoft\Tools\Filesystem\File;

class FileLocationTest extends \PHPUnit_Framework_TestCase
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
    
    public function test_it_can_give_access_to_some_location()
    {
        self::assertSame($this->config->root, $this->fl->getLocationPath());
    }
    
    public function test_it_can_list_files_in_location()
    {
        $files = $this->fl->listFiles();
        self::assertInternalType('array', $files);
        self::assertCount(4, $files);
    
        foreach ($files as $file) {
            self::assertInstanceOf(File::class, $file);
        }
    }
    
    public function test_it_can_list_files_with_extension()
    {
        $files = $this->fl->listFiles('txt');
        self::assertInternalType('array', $files);
        self::assertCount(2, $files);
    
        foreach ($files as $file) {
            self::assertInstanceOf(File::class, $file);
        }
    }
    
    public function test_it_can_list_files_with_various_extensions()
    {
        $files = $this->fl->listFiles(['txt', 'md']);
        self::assertInternalType('array', $files);
        self::assertCount(4, $files);
    
        foreach ($files as $file) {
            self::assertInstanceOf(File::class, $file);
        }
    }
    
    public function test_it_can_list_files_with_name_prefix()
    {
        $files = $this->fl->listFilesWithNamesStartingWith('other_');
        self::assertInternalType('array', $files);
        self::assertCount(2, $files);
    
        foreach ($files as $file) {
            self::assertInstanceOf(File::class, $file);
        }
    }
    
    public function test_it_can_list_files_with_name_prefix_and_extension()
    {
        $files = $this->fl->listFilesWithNamesStartingWith('other_', 'txt');
        self::assertInternalType('array', $files);
        self::assertCount(1, $files);
    
        foreach ($files as $file) {
            self::assertInstanceOf(File::class, $file);
        }
    }
    
    public function test_it_allows_to_fetch_some_information_about_file()
    {
        $files = $this->fl->listFilesWithNamesStartingWith('other_', 'txt');
        self::assertInternalType('array', $files);
        self::assertCount(1, $files);
        
        $file = reset($files);
        self::assertInstanceOf(File::class, $file);
        
        self::assertSame('other_file2.txt', $file->basename);
        self::assertSame('other_file2', $file->name);
        self::assertSame('txt', $file->ext);
        self::assertSame(23, $file->size);
        
        self::assertInternalType('int', $file->timestamp);
        self::assertGreaterThanOrEqual(0, $file->timestamp);
    }
    
    public function test_it_allows_to_read_content_of_file()
    {
        $expected = 'MIT License other_file2';
        
        $content = $this->fl->readFile('other_file2.txt');
        self::assertSame($expected, $content);
    
        $files = $this->fl->listFilesWithNamesStartingWith('other_', 'txt');
        $file = $files[0];
        self::assertSame($expected, $file->content);
    }
}
