<?php

namespace Magomogo\CouchLogger;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\LogRecord;

class MonologHandler extends AbstractProcessingHandler
{
    private string $channel;
    private string $serviceUri;

    public function __construct(
        $channel = 'default',
        $uri = 'http://127.0.0.1:5984/logger-application/_design/main/_rewrite/new',
        $level = \Monolog\Level::Warning,
        $bubble = true
    ) {
        $this->channel = $channel;
        $this->serviceUri = $uri;
        parent::__construct($level, $bubble);
    }

    /**
     * Writes the record down to the log of the implementing handler
     *
     * @param LogRecord $record
     * @return void
     */
    protected function write(LogRecord $record): void
    {
        file_get_contents($this->serviceUri, null, $this->postStream($record->toArray()));
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
