<?php
namespace Queue\Shell;

use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Log\Log;
use Cake\Utility\Hash;
use Enqueue\SimpleClient\SimpleClient;
use Interop\Queue\Message;
use Queue\Queue\Processor;
use Queue\Queue\QueueExtension;

/**
 * Worker shell command.
 */
class WorkerShell extends Shell
{
    /**
     * Gets the option parser instance and configures it.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser->addOption('config', [
            'default' => 'default',
            'help' => 'Name of a queue config to use',
            'short' => 'c',
        ]);
        $parser->addOption('queue', [
            'default' => 'default',
            'help' => 'Name of queue to bind to',
            'short' => 'Q',
        ]);
        $parser->addOption('logger', [
            'help' => 'Name of a configured logger',
            'default' => 'stdout',
            'short' => 'l',
        ]);
        $parser->addOption('max-iterations', [
            'help' => 'Number of max iterations to run',
            'default' => 0,
            'short' => 'i',
        ]);
        $parser->addOption('max-runtime', [
            'help' => 'Seconds for max runtime',
            'default' => 0,
            'short' => 'r',
        ]);
        $parser->setDescription(__('Runs a Queuesadilla worker.'));

        return $parser;
    }

    /**
     * main() method.
     *
     * @return bool|int|null Success or error code.
     */
    public function main()
    {
        $config = Hash::get($this->params, 'config');
        $url = Configure::read(sprintf('Queue.%s.url', $config));
        $logger = Log::engine($this->params['logger']);

        $processor = new Processor();
        $extension = new QueueExtension(
            (int)$this->params['max-iterations'],
            (int)$this->params['max-runtime']
        );

        $client = new SimpleClient($url, $logger);
        $client->bindTopic($this->params['queue'], $processor);
        $client->consume($extension);
    }
}