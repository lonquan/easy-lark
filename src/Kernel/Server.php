<?php

declare(strict_types=1);

namespace AntCool\EasyLark\Kernel;

use AntCool\EasyLark\Exceptions\BadRequestException;
use AntCool\EasyLark\Exceptions\InvalidArgumentException;
use AntCool\EasyLark\Kernel\Event;
use AntCool\EasyLark\Support\Logger;
use AntCool\EasyLark\Support\Utils;
use AntCool\EasyLark\Traits\InteractWithHandlers;
use AntCool\EasyLark\Traits\InteractWithServerRequest;
use Psr\Http\Message\ResponseInterface;
use Nyholm\Psr7\Response;

class Server
{
    use InteractWithServerRequest;
    use InteractWithHandlers;

    protected ?Event $event = null;

    protected string $rawBody;

    public function __construct(protected Config $config, protected ?Logger $logger)
    {
    }

    public function serve(): ResponseInterface
    {
        if ($this->getRequestEvent()->isEventType('url_verification')) {
            return $this->handleUrlVerifyEvent();
        }

        // Response code 200 and empty body.
        return $this->handle(new Response(200, [], null), $this->getRequestEvent());
    }

    public function getRequestEvent(): Event
    {
        if (is_null($this->event)) {
            $this->event = new Event($this->createContentFromRequest(), $this->config->event['encrypt_key'] ?? null);

            if (($this->config->event['verify_request'] ?? false) && !$this->event->isEventType('url_verification')) {
                $this->validateHeaderSign();
            }

            ($this->config->event['verify_token'] ?? false) && $this->validateToken();
        }

        return $this->event;
    }

    public function addEventListener(string $eventName, callable $handler): static
    {
        $this->withHandler(
            function (Event $event, \Closure $next) use ($eventName, $handler): mixed {
                return $event->getEventType() === $eventName ? $handler($event, $next) : $next($event);
            }
        );

        return $this;
    }

    protected function validateHeaderSign(): bool
    {
        if (!($encryptKey = $this->config->event['encrypt_key'] ?? false)) {
            throw new InvalidArgumentException('Encrypt key is required.');
        }

        $signature = Utils::signature(
            $this->getRequest()->getHeaderLine('X-Lark-Request-Timestamp'),
            $this->getRequest()->getHeaderLine('X-Lark-Request-Nonce'),
            $encryptKey,
            $this->rawBody
        );

        if ($signature !== $this->getRequest()->getHeaderLine('X-Lark-Signature')) {
            throw new BadRequestException('Invalid signature.');
        }

        return true;
    }

    protected function validateToken(): bool
    {
        if (($this->config->event['verify_token'] ?? null) === $this->event->getToken()) {
            return true;
        }

        throw new InvalidArgumentException('Request token invalid.');
    }

    protected function transformToResponseBody(string|array|null $response): string
    {
        if (is_array($response)) {
            return json_encode($response, JSON_UNESCAPED_UNICODE);
        }

        return $response ?? '';
    }

    protected function handleUrlVerifyEvent(): ResponseInterface
    {
        return new Response(200, [], $this->transformToResponseBody(['challenge' => $this->event->get('challenge')]));
    }

    protected function createContentFromRequest(): array
    {
        $this->rawBody = $this->getRequest()->getBody()->getContents();
        $content = json_decode($this->rawBody, true);

        if (json_last_error() !== 0) {
            throw new BadRequestException('Failed to decode request contents.');
        }

        return $content;
    }
}
