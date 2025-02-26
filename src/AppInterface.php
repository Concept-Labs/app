<?php
namespace Concept\App;

use Concept\Config\ConfigurableInterface;
use Concept\EventDispatcher\EventDispatcherAwareInterface;
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
     * @return static
     */
    public function addMiddleware(MiddlewareInterface $middleware): static;

    /**
     * Set the server request for the application.
     *
     * @param ServerRequestInterface $request
     * @return static
     */
    public function withServerRequest(ServerRequestInterface $request): static;
}