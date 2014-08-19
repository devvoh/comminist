#!/usr/bin/env php
<?php
/**
 * Communist - php css combiner/minifier
 */

// Check for parameters
if(isset($argv[1]) && $argv[1] === 'init') {
    $config  = '{' . PHP_EOL;
    $config .= '    "1": {' . PHP_EOL;
    $config .= '        "task": "combine",' . PHP_EOL;
    $config .= '        "src": [' . PHP_EOL;
    $config .= '            "../file1.ext",' . PHP_EOL;
    $config .= '            "../file2.ext"' . PHP_EOL;
    $config .= '        ],' . PHP_EOL;
    $config .= '        "dest": "./combined.css"' . PHP_EOL;
    $config .= '    },' . PHP_EOL;
    $config .= '    "2": {' . PHP_EOL;
    $config .= '        "task": "minify",' . PHP_EOL;
    $config .= '        "src": "./combined.css",' . PHP_EOL;
    $config .= '        "dest": "./combined.min.css"' . PHP_EOL;
    $config .= '    },' . PHP_EOL;
    $config .= '    "3": {' . PHP_EOL;
    $config .= '        "task": "delete",' . PHP_EOL;
    $config .= '        "src": "./combined.css"' . PHP_EOL;
    $config .= '    }' . PHP_EOL;
    $config .= '}';
    
    file_put_contents('comminist.config.json', $config);
    die('blank comminist.config.json generated.' . PHP_EOL );
}

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
        // Get & json_decode config
        if (is_file($this->configFilename)) {
            echo 'comminist.config.json found, executing...' . PHP_EOL . PHP_EOL;
            $this->config = json_decode(file_get_contents('./' . $this->configFilename));
            $this->execute();
        } else {
            die('ERROR: no comminist.config.json file found, run \'comminist init\' to generate a blank one.' . PHP_EOL );
        }
    }
    
    protected function execute() {
    
        foreach ($this->config as $task => $params) {
            $temp = '';
            
            switch ($params->task) {
            
                case 'combine':
                    // Loop and store in memory
                    foreach($params->src as $filename) {
                        $temp .= file_get_contents($filename);
                    }
                    
                    // And save
                    file_put_contents($params->dest, $temp);
                    
                    $this->debug('combined... ' . $params->dest);
                break;
                
                case 'minify':
                    // Get file content
                    $temp = file_get_contents($params->src);
                    
                    // Remove comments
                    $temp = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $temp);
                    // Remove space after colons
                    $temp = str_replace(': ', ':', $temp);
                    // Remove whitespace
                    $temp = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $temp);
                    
                    // And save
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
