<?php

namespace FiiSoft\Tools\Filesystem;

use FiiSoft\Tools\Configuration\AbstractConfiguration;

final class FileLocationConfig extends AbstractConfiguration
{
    /**
     * Type of location, required.
     * Valid value is one of: local
     *
     * @var string
     */
    public $type;
    
    /**
     * Root of location, required.
     * If path is set then will be considered as relative to root.
     *
     * @var string
     */
    public $root;
    
    /**
     * If root is set, path is considered as relative to root.
     * Can be empty if root is set.
     *
     * @var string
     */
    public $path;
}