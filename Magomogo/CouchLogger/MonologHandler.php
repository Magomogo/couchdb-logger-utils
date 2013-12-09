<?php

namespace Magomogo\CouchLogger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class MonologHandler extends AbstractProcessingHandler
{
    /**
     * @var string
     */
    private $channel;

    /**
     * @var string
     */
    private $serviceUri;

    public function __construct(
        $channel = 'default',
        $uri = 'http://127.0.0.1:5984/logger-application/_design/main/_rewrite/new',
        $level = Logger::WARNING,
        $bubble = true
    ) {
        parent::__construct($level, $bubble);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param  array $record
     * @return void
     */
    protected function write(array $record)
    {
        file_get_contents($this->serviceUri, null, $this->postStream($record));
    }

    /**
     * @param array $record
     * @return resource
     */
    private function postStream(array $record)
    {
        return stream_context_create(
            array(
                'http' => array(
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode(
                        array_merge(
                            $record,
                            array(
                                'message' => reset($record) ?: 'Error',
                                'channel' => $this->channel,
                            )
                        )
                    )
                )
            )
        );
    }

}