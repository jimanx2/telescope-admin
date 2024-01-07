<?php namespace Laravel\Telescope\Lib;

use GuzzleHttp\TransferStats;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Client\Response as ClientResponse;
use Laravel\Telescope\Watchers\FetchesStackTrace;
use Illuminate\Contracts\Events\Dispatcher;

class GuzzleClientDecorator extends \GuzzleHttp\Client
{
    use FetchesStackTrace;

    /**
     * Create a new GuzzleClient instance
     *
     * @param  array  $config Configuration to pass to GuzzleClient constructor
     * @return void
     */
    public function __construct(array $config = [])
    {
        $config['on_stats'] = function (TransferStats $stats) {
            // need to convert from PSR-7 to HttpRequest
            $request = new ClientRequest($stats->getRequest());
            if ($stats->hasResponse()) {
                $response = new ClientResponse($stats->getResponse());
                app(Dispatcher::class)->dispatch(
                    new ResponseReceived($request, $response)
                );
                return;
            }

            app(Dispatcher::class)->dispatch(
                new ConnectionFailed($request)
            );
        };

        parent::__construct($config);
    }
}