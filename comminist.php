#!/usr/bin/env php
<?php
/**
 * Communist - php css combiner/minifier
 */

// Actually instantiate & run class
$cm = new Communist();

class Communist
{
    protected $version = '0.1.0';
    protected $configFilename = 'comminist.config.json';
    protected $config = array();
    
    public function __construct() {
        $this->loadConfig();
    }
    
    protected function loadConfig() {
        // store & json_decode config
        if (is_file($this->configFilename)) {
            echo 'comminist.config.json found, executing...' . PHP_EOL . PHP_EOL;
            $this->config = json_decode(file_get_contents('./' . $this->configFilename))[0];
            $this->execute();
        } else {
            die('ERROR: no comminist.config.json file found' . PHP_EOL );
        }
    }
    
    protected function execute() {
    
        foreach ($this->config as $task => $params) {
            $temp = '';
            
            switch ($params->task) {
            
                case 'combine':
                    // loop and store in memory
                    foreach($params->src as $filename) {
                        $temp .= file_get_contents($filename);
                    }
                    
                    // and save
                    file_put_contents($params->dest, $temp);
                    
                    $this->debug('combined... ' . $params->dest);
                break;
                
                case 'minify':
                    // get file content
                    $temp = file_get_contents($params->src);
                    
                    // remove comments
                    $temp = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $temp);
                    // remove space after colons
                    $temp = str_replace(': ', ':', $temp);
                    // remove whitespace
                    $temp = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $temp);
                    
                    // and save
                    file_put_contents($params->dest, $temp);
                    
                    $this->debug('minified... ' . $params->dest);
                break;
                
                case 'delete':
                    if (is_file($params->src)) {
                        unlink($params->src);
                        $this->debug('deleted ' . $params->src . '...');
                    } else {
                        $this->debug('couldn\'t delete ' . $params->src . '...');
                    }
                break;
                
            }
        }
        
        echo PHP_EOL . 'Done!' . PHP_EOL;
        
    }
    
    public function debug($message) {
        echo $message;
        echo PHP_EOL;
    }
}
