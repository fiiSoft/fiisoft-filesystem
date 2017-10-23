<?php

namespace FiiSoft\Tools\Filesystem;

use InvalidArgumentException;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use LogicException;
use RuntimeException;

final class FileLocation
{
    /** @var FileLocationConfig */
    private $config;
    
    /** @var Filesystem */
    private $fileSystem;
    
    /**
     * @param FileLocationConfig $config
     */
    public function __construct(FileLocationConfig $config)
    {
        $this->config = clone $config;
    }
    
    /**
     * @param string $fileName
     * @throws LogicException
     * @return string
     */
    public function getFileUrl($fileName)
    {
        //TODO it depends from adapter so must be delegated
        return $this->getLocationPath() . DIRECTORY_SEPARATOR . ltrim($fileName, '\\/');
    }
    
    /**
     * @param string $fileName
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @throws LogicException
     * @return File|null
     */
    public function getFile($fileName)
    {
        $handler = $this->fileSystem()->get($fileName);
        if ($handler->exists() && $handler->isFile()) {
            return new File($this, [
                'type' => $handler->getType(),
                'timestamp' => $handler->getTimestamp(),
                'size' => $handler->getSize(),
                'filename' => pathinfo($fileName, PATHINFO_FILENAME),
                'extension' => pathinfo($fileName, PATHINFO_EXTENSION),
                'basename' => $fileName,
            ]);
        }
        
        return null;
    }
    
    /**
     * @param string $fileName
     * @throws LogicException
     * @throws FileExistsException
     * @throws FileNotFoundException
     * @return string|false content of file or false on read error
     */
    public function readFile($fileName)
    {
        return $this->fileSystem()->read($fileName);
    }
    
    /**
     * @param string $fileName filename
     * @param string $contents
     * @throws LogicException
     * @return bool true on success, false on failure
     */
    public function putFile($fileName, $contents)
    {
        return $this->fileSystem()->put($fileName, $contents);
    }
    
    /**
     * @param string $namePrefix
     * @param string|array $withExtension optionally can filter by file extension (or extensions if array)
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return File[]
     */
    public function listFilesWithNamesStartingWith($namePrefix, $withExtension = null)
    {
        return array_values(array_filter($this->listFiles($withExtension), function (File $file) use ($namePrefix) {
            return 0 === strpos($file->name, $namePrefix);
        }));
    }
    
    /**
     * @param string|array $withExtension optionally can filter by file extension (or extensions if array)
     * @throws LogicException
     * @throws InvalidArgumentException
     * @throws RuntimeException
     * @return File[]
     */
    public function listFiles($withExtension = null)
    {
        if ($withExtension === null) {
            $withExtension = [];
        } elseif (is_string($withExtension)) {
            $withExtension = [$withExtension];
        } elseif (!is_array($withExtension)) {
            throw new InvalidArgumentException('Invalid param withExtension');
        }
        
        return array_values(array_map(function (array $file) {
            return new File($this, $file);
        }, array_filter($this->fileSystem()->listContents(), function (array $file) use ($withExtension) {
            return $file['type'] === 'file'
                && (empty($withExtension) || in_array($file['extension'], $withExtension, true));
        })));
    }
    
    /**
     * @throws LogicException
     * @return string
     */
    public function getLocationPath()
    {
        if (empty($this->config->type)) {
            throw new LogicException('Invalid configuration - missing key "type"');
        }
    
        switch ($this->config->type) {
            case 'local': return $this->getLocalPath();
            default:
                throw new LogicException('Unsupported type of location: '.$this->config->type);
        }
    }
    
    /**
     * @throws LogicException
     * @return string
     */
    private function getLocalPath()
    {
        if (empty($this->config->path) && empty($this->config->root)) {
            throw new LogicException('Invalid configuration for location of type "local"');
        }
    
        $path = '';
        if (empty($this->config->root)) {
            $root = $this->config->path;
        } else {
            $root = $this->config->root;
            if (!empty($this->config->path)) {
                $path = $this->config->path;
            }
        }
    
        $root = rtrim($root, '\\/');
        $path = ltrim($path, '\\/');
    
        return $path !== ''
            ? $root . DIRECTORY_SEPARATOR . $path
            : $root;
    }
    
    /**
     * @throws LogicException
     * @return Filesystem
     */
    private function fileSystem()
    {
        if (!$this->fileSystem) {
            $flysystemFactory = new FlysystemFactory($this->config);
            $this->fileSystem = $flysystemFactory->getFilesystem();
        }
        
        return $this->fileSystem;
    }
}