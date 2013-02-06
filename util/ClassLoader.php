<?php
namespace util;
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ClassLoader
 *
 * @author Administrator
 */
class ClassLoader {
    //put your code here
    private $namespace;
    private $path;
    private $namespaceSeparator = '\\';
    private $fileExtension = '.php';
    
    public function __construct($namespace=null, $path=null) {
        $this->setNamespace($namespace);
        $this->setPath($path);
    }
    
    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }
    
    public function setPath($path) {
        $this->path = $path;
    }
    
    public function setNamespaceSeparator($namespaceSeparator) {
        $this->namespaceSeparator = $namespaceSeparator;
    }
    
    public function setFileExtension($fileExtension) {
        $this->fileExtension = $fileExtension;
    }

    public function register() {
        spl_autoload_register(array($this, 'loader'));
    }
    
    public function unregister() {
        spl_autoload_unregister(array($this, 'loader'));
    }
    
    public function loader($className) {
        if ($this->namespace !== null && strpos($className, $this->namespace.$this->namespaceSeparator !== 0)) {
            return FALSE;
        }
        $ds = DIRECTORY_SEPARATOR;
        require ($this->path != null ? $this->path .$ds : '')
            . str_replace($this->namespaceSeparator, $ds, $className)
            . $this->fileExtension;
        return TRUE;
    }
}
?>
