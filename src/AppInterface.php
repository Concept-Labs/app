<?php
namespace Concept\App;

use Concept\Config\ConfigurableInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;

interface AppInterface extends ConfigurableInterface
{

    /**
     * Run the application.
     */
    public function run(): void;

    /**
     * Add a middleware to the application.
     *
     * @param MiddlewareInterface $middleware
     * @return self
     */
    public function addMiddleware(MiddlewareInterface $middleware): self;

    /**
     * Set the server request for the application.
     *
     * @param ServerRequestInterface $request
     * @return self
     */
    public function withServerRequest(ServerRequestInterface $request): self;
}