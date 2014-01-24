<?php
namespace GetSky\Phalcon\Bootstrap;

use Phalcon\Config as BaseConfig;

/**
 * Class Config
 * @package GetSky\Phalcon\Bootstrap
 */
class Config
{

    /**
     * @var array
     */
    protected $adapters = [
        'ini' => '\Phalcon\Config\Adapter\Ini',
        'json' => '\Phalcon\Config\Adapter\Json'
    ];

    /**
     * @param string $path
     * @return BaseConfig
     * @throws ExtensionNotFoundException
     * @throws AdapterNotFoundException
     */
    public function create($path)
    {
        $extension = $this->extractExtension($path);

        if ($extension === null) {
            throw new ExtensionNotFoundException("Extension not found ($path)");
        }

        if (isset($this->adapters[$extension])) {
            return new $this->adapters[$extension]($path);
        }

        throw new AdapterNotFoundException("Adapter can be found for $path");
    }

    /**
     * @param string $name
     * @param string $class
     */
    public function add($name, $class)
    {
        $this->adapters[$name] = $class;
    }

    /**
     * @param string $name
     */
    public function remove($name)
    {
        unset($this->adapters[$name]);
    }

    /**
     * @return void
     */
    public function removeAll()
    {
        $this->adapters = [];
    }

    /**
     * @param string $path
     * @return null|string
     */
    protected function extractExtension($path)
    {
        $fileInfo = pathinfo($path);
        if (!isset($fileInfo['extension'])) {
            return null;
        }
        return $fileInfo['extension'];
    }
} 