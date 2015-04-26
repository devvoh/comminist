#!/usr/bin/env php
<?php
/**
 * Comminist - php css combiner/minifier
 * 
 * By devvoh, 2014, MIT licensed
 * 
 * https://github.com/devvoh/comminist
 */

class Comminist
{
    public $version = '0.2.0';
    protected $configFilename = 'comminist.config.json';
    protected $config = array();
    protected $errors = array();
    protected $messages = array();

    /**
     * Initialize an example configuration file and exit
     */
    public function initialize() {
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
        // Add message
        $this->addMessage('blank comminist.config.json generated.');
        // And stop running
        $this->stop();
    }

    public function addMessage($message) {
        $this->messages[] = $message;
    }

    public function addError($error) {
        $this->errors[] = $error;
    }

    public function loadConfig()
    {
        // Get & json_decode config
        if (is_file($this->configFilename)) {
            $this->addMessage('comminist.config.json found...');
            $this->config = json_decode(file_get_contents('./' . $this->configFilename));
            if ($this->config === null) {
                $this->addError('comminist.config.json invalid, check the configuration markup.');
                return false;
            }
        } else {
            $this->addError('no comminist.config.json file found, run \'comminist init\' to generate a blank one.');
            return false;
        }
        
        return $this->config;
    }

    public function combine($params) {
        $temp = null;
        
        // Loop and store in memory
        foreach ($params->src as $filename) {
            if (file_exists($filename)) {
                $temp .= file_get_contents($filename);
            } else {
                $this->addError('to be combined file ' . $filename . ' does not exist.');
                $this->stop();
            }
        }
        // And save
        file_put_contents($params->dest, $temp);
        $this->addMessage('combined... ' . $params->dest);
    }

    public function minify($params) {
        // Get file content
        if (file_exists($params->src)) {
            $temp = file_get_contents($params->src);
        } else {
            $this->addError('to be minified file ' . $filename . ' does not exist.');
            $this->stop();
        }
        
        // Get the length before minification
        $lengthBefore = strlen($temp);
        
        // Remove comments
        $temp = preg_replace('!/\*.*?\*/!s','', $temp);
        $temp = preg_replace('/\n\s*\n/',"\n", $temp);
        
        // Remove spaces
        $temp = preg_replace('/[\n\r \t]/',' ', $temp);
        $temp = preg_replace('/ +/',' ', $temp);
        $temp = preg_replace('/ ?([,:;{}]) ?/','$1', $temp);
        
        // Remove trailing ;
        $temp = preg_replace('/;}/','}', $temp);

        // And get the length after minification
        $lengthAfter = strlen($temp);

        $percentage = 0;
        if ($lengthBefore > 0) {
            $percentage = number_format(100 - (100 / $lengthBefore) * $lengthAfter, 2);
        }

        // And save
        file_put_contents($params->dest, $temp);

        $this->addMessage('  minified... ' . $params->dest . ' saved ' . $percentage . '%...');
    }

    public function delete($params) {
        if (file_exists($params->src)) {
            if (unlink($params->src)) {
                $this->addMessage('   deleted... ' . $params->src . '...');
            } else {
                $this->addError('couldn\'t delete ' . $params->src . ' for unknown reason, check permissions...');
            }
        } else {
            $this->addError('couldn\'t delete ' . $params->src . ' because it didn\'t exist...');
        }
    }
    
    public function stop()
    {
        echo PHP_EOL;
        if (count($this->messages)) {
            foreach ($this->messages as $message) {
                echo $message . PHP_EOL;
            }
        }
        if (count($this->errors)) {
            foreach ($this->errors as $error) {
                echo 'ERROR: ' . $error . PHP_EOL;
            }
        }
        echo PHP_EOL;
        exit();
    }
}

// Instantiate Comminist class
$comminist = new Comminist();
$comminist->addMessage('Comminist ' . $comminist->version);
$comminist->addMessage('-------------------------------');

// Check for parameters and run initialize if init given
if (isset($argv[1]) && $argv[1] === 'init') {
    $comminist->initialize();
}

// Load configuration & returns either the config or false based on whether it could be loaded or not
$config = $comminist->loadConfig();
if ($config) {
    foreach ($config as $order => $params) {
        // Instead of doing a switch, let's just check if the method exists
        $method = $params->task;
        if (method_exists($comminist, $method)) {
            $comminist->$method($params);
        }
    }
}

// We're always going to call stop to make sure we show all messages and exit properly
$comminist->stop();