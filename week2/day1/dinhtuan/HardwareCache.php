<?php
namespace www\week2\day1\dinhtuan;

class HardwareCache implements CacheInterface
{
    private $tempPath = "\cache";

    /**
     * initialize
     */
    public function __construct($uniqueFolderName)
    {
        $this->tempPath = __DIR__ . $this->tempPath . $uniqueFolderName . DIRECTORY_SEPARATOR;
        if (!is_dir($this->tempPath)) {
            mkdir($this->tempPath);
        }
    }

    /**
     * generate a hashkey
     * @return string hashkey
     */
    private function getHash($string)
    {
        return md5($string);
    }

    /**
     * this function is used to check weather the key exist
     * @param string $key key to check
     * @return true/false
     */
    public function check_exist($key)
    {
        $hashKey = $this->getHash($key);

        // split the string by 7 characters and add a DIRECTORY_SEPARATOR after each chunk
        $hashKey = chunk_split($hashKey, 7, DIRECTORY_SEPARATOR);
        
        // $fileDir = $this->tempPath . rtrim($hashKey,DIRECTORY_SEPARATOR) . "_*";
        // $search = glob($fileDir);
        // if (1 == count($search)) {
        //     return true;
        // } else {
        //     return false;
        // }

        $fileDir = $this->tempPath . rtrim($hashKey, DIRECTORY_SEPARATOR);
        return file_exists($fileDir);
    }

    /**
     * this function create a file with the name: hashkey_value
     * @param string $key key of the value
     * @param mixed $value value of the key
     * @return void
     */
    public function add($key, $value)
    {
        $hashKey = $this->getHash($key);

        // split the string by 7 characters and add a DIRECTORY_SEPARATOR after each chunk
        $hashKey = chunk_split($hashKey, 7, DIRECTORY_SEPARATOR);
        $dir = $this->tempPath . substr($hashKey,0,32);
        is_dir($dir) or mkdir($dir, 0600, true);

        // $fileName = $this->tempPath . rtrim($hashKey,DIRECTORY_SEPARATOR) . "_" . $value;

        $fileName = $this->tempPath . rtrim($hashKey,DIRECTORY_SEPARATOR);
        
        // fclose(fopen($fileName, "w"));

        $handler = fopen($fileName, "w");
        fwrite($handler, $value);
        fclose($handler);
    }

    /**
     * this function is used to get the value of the key
     * @param string $key the key to get the value
     * @return mixed value of the key
     */
    public function get($key)
    {
        $hashKey = $this->getHash($key);

        // split the string by 7 characters and add a DIRECTORY_SEPARATOR after each chunk
        $hashKey = chunk_split($hashKey, 7, DIRECTORY_SEPARATOR);

        // $fileName = $this->tempPath . rtrim($hashKey,DIRECTORY_SEPARATOR) . "_*";

        $fileName = $this->tempPath . rtrim($hashKey,DIRECTORY_SEPARATOR);
        
        // $search = glob($fileName);
        // $trueFileName = $search[0];
        // $value = substr($trueFileName, 37);
        // return $value;

        $handler = fopen($fileName, 'r');
        $value = fgets($handler);
        fclose($handler);
        return $value;
    }

    /**
     * check weather the key exist or not. if it exists, get the value
     * @param string $key the key to get the value
     * @return mixed value of the key or false(if the key had not existed)
     */
    public function checkToGet($key)
    {
        $hashKey = $this->getHash($key);

        // split the string by 7 characters and add a DIRECTORY_SEPARATOR after each chunk
        $hashKey = chunk_split($hashKey, 7, DIRECTORY_SEPARATOR);

        $fileName = $this->tempPath . rtrim($hashKey,DIRECTORY_SEPARATOR);

        if (file_exists($fileName)) {
            $handler = fopen($fileName, 'r');
            $value = fgets($handler);
            fclose($handler);
            return $value;
        } else {
            return false;
        }
    }

}