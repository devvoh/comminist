#!/usr/bin/env php
<?php
/**
 * Comminist - php css combiner/minifier
 * 
 * By devvoh, 2014, MIT licensed
 * 
 * https://github.com/devvoh/comminist
 */

// Check for parameters
if (isset($argv[1]) && $argv[1] === 'init') {
    $config = array(
        '1' => array(
            'task' => 'combine',
            'src' => array(
                './file1.css',
                './file2.css'
            ),
            'dest' => './combined.css'
        ),
        '2' => array(
            'task' => 'minify',
            'src' => './combined.css',
            'dest' => './combined.min.css'
        ),
        '3' => array(
            'task' => 'delete',
            'src' => './combined.css'
        )
    );
    
    $config = json_encode($config, JSON_PRETTY_PRINT);
    $config = str_replace('\/', '/', $config);
    file_put_contents('./comminist.config.json', $config);
    // Die before we run this broken example config
    die('blank comminist.config.json generated.' . PHP_EOL);
}

// Actually instantiate & run class
$cm = new Comminist();

class Comminist
{

    protected $version = '0.1.0';

    protected $configFilename = 'comminist.config.json';

    protected $config = array();

    public function __construct()
    {
        $this->loadConfig();
    }

    protected function loadConfig()
    {
        // Get & json_decode config
        if (is_file($this->configFilename)) {
            echo 'comminist.config.json found, executing...' . PHP_EOL . PHP_EOL;
            $this->config = json_decode(file_get_contents('./' . $this->configFilename));
            if ($this->config !== null) {
                $this->execute();
            } else {
                die('ERROR: comminist.config.json invalid, check the configuration markup.' . PHP_EOL);
            }
        } else {
            die('ERROR: no comminist.config.json file found, run \'comminist init\' to generate a blank one.' . PHP_EOL);
        }
    }

    protected function execute()
    {
        foreach ($this->config as $task => $params) {
            $temp = '';
            
            switch ($params->task) {
                
                case 'combine':
                    
                    // Loop and store in memory
                    foreach ($params->src as $filename) {
                        if (is_file($filename)) {
                            $temp .= file_get_contents($filename);
                        } else {
                            die('ERROR: to be combined file ' . $filename . ' does not exist.' . PHP_EOL);
                        }
                    }
                    
                    // And save
                    file_put_contents($params->dest, $temp);
                    
                    $this->debug('  combined... ' . $params->dest);
                    break;
                
                case 'minify':
                    
                    // Get file content
                    if (is_file($params->src)) {
                        $temp = file_get_contents($params->src);
                    } else {
                        die('ERROR: to be minified file ' . $filename . ' does not exist.' . PHP_EOL);
                    }
                    
                    $lengthBefore = strlen($temp);
                    
                    // Remove comments
                    $temp = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $temp);
                    // Remove whitespace
                    $temp = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $temp);
                    // Remove specific spaces
                    $temp = str_replace(': ', ':', $temp);
                    $temp = str_replace(' {', '{', $temp);
                    $temp = str_replace(' > ', '>', $temp);
                    $temp = str_replace(' + ', '+', $temp);
                    $temp = str_replace(' ~ ', '~', $temp);
                    $temp = str_replace('; ', ';', $temp);
                    $temp = str_replace(', ', ',', $temp);
                    $temp = str_replace(' !important', '!important', $temp);
                    
                    $lengthAfter = strlen($temp);
                    
                    $percentage = 0;
                    if ($lengthBefore > 0) {
                        $percentage = number_format(100 - (100 / $lengthBefore) * $lengthAfter, 2);
                    }
                    
                    // And save
                    file_put_contents($params->dest, $temp);
                    
                    $this->debug('  minified... ' . $params->dest . ' saved ' . $percentage . '%...');
                    break;
                
                case 'delete':
                    if (is_file($params->src)) {
                        if (unlink($params->src)) {
                            $this->debug('   deleted... ' . $params->src . '...');
                        } else {
                            $this->debug('couldn\'t delete ' . $params->src . ' for unknown reason, check permissions...');
                        }
                    } else {
                        $this->debug('couldn\'t delete ' . $params->src . ' because it didn\'t exist...');
                    }
                    break;
            }
        }
        
        echo PHP_EOL . 'Done!' . PHP_EOL;
    }

    public function debug($message)
    {
        echo $message;
        echo PHP_EOL;
    }
}
