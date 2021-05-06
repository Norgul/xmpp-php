<?php

namespace Norgul\Xmpp;

use Exception;
use Norgul\Xmpp\Buffers\Response;
use Norgul\Xmpp\Exceptions\DeadSocket;

class Socket
{
    public $connection;

    protected $responseBuffer;
    protected $options;

    /**
     * Period in microseconds for imposed timeout while doing socket_read()
     */
    protected $timeout = 150000;

    /**
     * Socket constructor.
     * @param Options $options
     * @throws DeadSocket
     */
    public function __construct(Options $options)
    {
        $this->responseBuffer = new Response();
        $this->connection = stream_socket_client($options->fullSocketAddress());

        if (!$this->isAlive($this->connection)) {
            throw new DeadSocket();
        }

        //stream_set_blocking($this->connection, true);
        stream_set_timeout($this->connection, 0, $this->timeout);
        $this->options = $options;
    }

    public function disconnect()
    {
        fclose($this->connection);
    }

    /**
     * Sending XML stanzas to open socket
     * @param $xml
     */
    public function send(string $xml)
    {
        try {
            $fwrite_ret = fwrite($this->connection, $xml);
            if($fwrite_ret === 0)
            {
                throw new DeadSocket("Server gone away!");
            }
            $this->options->getLogger()->logRequest(__METHOD__ . '::' . __LINE__ . " $xml");
        } catch (Exception $e) {
            $this->options->getLogger()->error(__METHOD__ . '::' . __LINE__ . " fwrite() failed " . $e->getMessage());
            return false;
        }

        $this->receive();
    }

    public function receive()
    {
        $response = '';
        while ($out = fgets($this->connection)) {
            $response .= $out;
        }

        if (!$response) {
            return;
        }

        $this->responseBuffer->write($response);
        $this->options->getLogger()->logResponse(__METHOD__ . '::' . __LINE__ . " $response");
    }

    protected function isAlive($socket)
    {
        return $socket !== false;
    }

    public function setTimeout($timeout)
    {
        $this->timeout = $timeout;
    }

    public function getResponseBuffer(): Response
    {
        return $this->responseBuffer;
    }

    public function getOptions(): Options
    {
        return $this->options;
    }

    protected function checkSocketStatus(): void
    {
        $status = socket_get_status($this->connection);

        //echo print_r($status);

        if ($status['eof']) {
            $this->options->getLogger()->logResponse(
                __METHOD__ . '::' . __LINE__ .
                " ---Probably a broken pipe, restart connection\n"
            );
        }
    }
}
