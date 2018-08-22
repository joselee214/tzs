<?php
class Photo
{
    public $host = ''; //host
    public $documentRoot = ''; //document root
    private static $_instance = null;

    public function __construct($host, $documentRoot)
    {
        $this->host = $host;
        $this->documentRoot = $documentRoot;
    }

    public function savePhoto($fileName, $byte)
    {
        $fileHandle = fopen($this->documentRoot . $fileName, 'wb');
        try {
            self::_checkFileName($fileName);
        } catch (J7Exception $e) {
            return @unlink($this->documentRoot . $fileName);
        }
        if (!is_writable($this->documentRoot . $fileName)) {
            chmod($this->documentRoot . $fileName, 666);
        }
        return fwrite($fileHandle, $byte);
    }

    public static function getInstance($host, $documentRoot)
    {
        if (null === self::$_instance) {
            self::$_instance = new self($host, $documentRoot);
        }
        return self::$_instance;
    }

    public function checkPath($path)
    {
        if (is_dir($path)) {
            return true;
        } else {
            mkdir($path);
        }
    }

    protected static function _checkFileName($fileName)
    {
        if (preg_match('/[^0-9\_\__].png/i', $fileName)) {
            throw new J7Exception('Filename illegal');
        }
    }
}