<?php

namespace Magomogo\CouchLogger;

use Pimple\Container;
use Silex\Application;
use Pimple\ServiceProviderInterface;

class SilexExceptionLoggerProvider implements ServiceProviderInterface
{
    public function register(Container $pimple): void
    {
        $pimple['couchLogger.uri'] = 'http://127.0.0.1:5984/logger-application/_design/main/_rewrite/new';
        $pimple['couchLogger.channel'] = 'default';
    }

    /**
     * @param Application $app An Application instance
     */
    public function boot(Application $app): void
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
