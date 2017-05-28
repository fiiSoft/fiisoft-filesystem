<?php

namespace FiiSoft\Tools\Filesystem;

use Exception;
use InvalidArgumentException;
use RuntimeException;

/**
 * @property string $basename full name of file, with extension
 * @property string $name a name of file, without extension
 * @property string $ext extension of file
 * @property int $size size of file, in bytes
 * @property int $timestamp timestamp of file (when was created or accessed last time)
 * @property string $content content of file
 */
final class File
{
    /** @var array */
    private static $required = ['type', 'timestamp', 'size', 'basename', 'extension', 'filename'];
    
    /** @var FileLocation */
    private $fileLocation;
    
    /** @var int */
    private $timestamp;
    
    /** @var int */
    private $size;
    
    /** @var string */
    private $ext;
    
    /** @var string */
    private $name;
    
    /** @var string */
    private $basename;
    
    /**
     * @param FileLocation $fileLocation
     * @param array $fileInfo
     * @throws InvalidArgumentException
     * @throws RuntimeException
     */
    public function __construct(FileLocation $fileLocation, array $fileInfo)
    {
        $this->fileLocation = $fileLocation;
        $this->validateAndSetFileInfo($fileInfo);
    }
    
    /**
     * @param string $name
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return mixed
     */
    public function __get($name)
    {
        switch ($name) {
            case 'basename': return $this->basename;
            case 'name': return $this->name;
            case 'ext': return $this->ext;
            case 'size': return $this->size;
            case 'timestamp': return $this->timestamp;
            case 'content':
                try {
                    return $this->fileLocation->readFile($this->basename);
                } catch (Exception $e) {
                    throw new RuntimeException(
                        'Unable to read content of file '.$this->fileLocation->getFileUrl($this->basename)
                    );
                }
            default:
                throw new InvalidArgumentException('There is no property "'.$name.'" in '.__CLASS__);
        }
    }
    
    /**
     * @param array $fileInfo
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return void
     */
    private function validateAndSetFileInfo(array $fileInfo)
    {
        foreach (self::$required as $item) {
            if (!isset($fileInfo[$item])) {
                throw new InvalidArgumentException('Missing key '.$item.' in param fileInfo');
            }
        }
    
        if ($fileInfo['type'] !== 'file') {
            throw new RuntimeException('File is required');
        }
        
        $this->size = (int) $fileInfo['size'];
        if ($this->size < 0) {
            throw new InvalidArgumentException('Invalid size');
        }
    
        $this->timestamp = (int) $fileInfo['timestamp'];
        if ($this->timestamp < 0) {
            throw new InvalidArgumentException('Invalid timestamp');
        }
    
        $this->ext = (string) $fileInfo['extension'];
        $this->name = (string) $fileInfo['filename'];
        $this->basename = (string) $fileInfo['basename'];
        
        if ($this->ext !== '' && $this->basename !== $this->name.'.'.$this->ext) {
            throw new InvalidArgumentException('Invalid param fileInfo');
        }
    }
}