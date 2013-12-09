<?php

namespace Magomogo\CouchLogger;

use Silex\Application;
use Silex\ServiceProviderInterface;

class SilexExceptionLoggerProvider implements ServiceProviderInterface
{
    /**
     * @param Application $app An Application instance
     */
    public function register(Application $app)
    {
        $app['couchLogger.uri'] = 'http://127.0.0.1:5984/logger-application/_design/main/_rewrite/new';
        $app['couchLogger.channel'] = 'default';
    }

    /**
     * @param Application $app An Application instance
     */
    public function boot(Application $app)
    {
        $app->error(
            function (\Exception $ex, $code) use ($app)
            {
                file_get_contents(
                    $app['couchLogger.uri'],
                    null,
                    self::postStream($ex, $app['couchLogger.channel'])
                );
            },
            -4 // register before standard handlers
        );
    }

    /**
     * @param \Exception $ex
     * @param $channel
     * @return resource
     */
    private static function postStream($ex, $channel)
    {
        return stream_context_create(
            array(
                'http' => array(
                    'header' => "Content-type: application/json\r\n",
                    'method' => 'POST',
                    'content' => json_encode(
                        array(
                            'message' => $ex->getMessage() ?: get_class($ex),
                            'channel' => $channel,
                            'trace' => $ex->getTraceAsString()
                        )
                    )
                )
            )
        );
    }
}