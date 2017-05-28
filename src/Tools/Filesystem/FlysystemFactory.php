<?php

namespace FiiSoft\Tools\Filesystem;

use InvalidArgumentException;
use League\Flysystem\Adapter\Local;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Filesystem;
use LogicException;

final class FlysystemFactory
{
    const TYPE_LOCAL = 'local';
    
    /** @var FileLocationConfig */
    private $config;
    
    /** @var Filesystem */
    private $filesystem;
    
    /**
     * @param FileLocationConfig $config
     * @throws InvalidArgumentException
     */
    public function __construct(FileLocationConfig $config = null)
    {
        $this->setConfig($config);
    }
    
    /**
     * Set or unset configuration.
     *
     * @param FileLocationConfig|array|null $config
     * @throws InvalidArgumentException
     * @return void
     */
    public function setConfig($config)
    {
        if ($config instanceof FileLocationConfig) {
            if (!$this->config || !$this->config->equals($config)) {
                $this->changeConfiguration(clone $config);
            }
        } elseif (is_array($config)) {
            if (!$this->config || !$this->config->equals($config)) {
                $this->changeConfiguration(new FileLocationConfig($config));
            }
        } elseif ($config === null) {
            $this->changeConfiguration(null);
        } else {
            throw new InvalidArgumentException('Invalid param config');
        }
    }
    
    /**
     * @param FileLocationConfig|null $config
     * @return void
     */
    private function changeConfiguration(FileLocationConfig $config = null)
    {
        $this->config = $config;
        $this->filesystem = null;
    }
    
    /**
     * @throws LogicException
     * @return Filesystem
     */
    public function getFilesystem()
    {
        if (!$this->filesystem) {
            $this->filesystem = new Filesystem($this->getAdapter());
        }
        
        return $this->filesystem;
    }
    
    /**
     * @throws LogicException
     * @return AdapterInterface
     */
    private function getAdapter()
    {
        if (empty($this->config->type)) {
            throw new LogicException('Invalid configuration and adapter cannot be specified');
        }
        
        switch ($this->config->type) {
            case 'local': return $this->getLocalAdapter();
            default:
                throw new LogicException('FlysystemFactory is unable to create adapter of type '.$this->config->type);
        }
    }
    
    /**
     * @throws LogicException
     * @return Local
     */
    private function getLocalAdapter()
    {
        if (empty($this->config->path) && empty($this->config->root)) {
            throw new LogicException('Invalid configuration for adapter of type "local"');
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
    
        $root = rtrim($root, '\\/') . DIRECTORY_SEPARATOR;
        $adapter = new Local($root);
        
        if ($path !== '') {
            $path = ltrim($path, '\\/');
            $adapter->setPathPrefix($path);
        }
    
        return $adapter;
    }
}