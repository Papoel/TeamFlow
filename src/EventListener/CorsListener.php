<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CorsListener implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger) {}

    public function onKernelRequest(RequestEvent $event): void
    {
        // Don't do anything if it's not the main request
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        $this->logger->info('🔍 CORS Listener - Request received', [
            'method' => $request->getMethod(),
            'path' => $request->getPathInfo(),
            'origin' => $request->headers->get('Origin'),
        ]);

        // Only handle OPTIONS requests (CORS preflight)
        if ($request->getMethod() !== 'OPTIONS') {
            $this->logger->info('❌ CORS Listener - Not an OPTIONS request, skipping');
            return;
        }

        $this->logger->info('✅ CORS Listener - OPTIONS request detected');

        // Only handle API requests
        if (!str_starts_with($request->getPathInfo(), '/api/')) {
            $this->logger->info('❌ CORS Listener - Not an API path, skipping');
            return;
        }

        $this->logger->info('✅ CORS Listener - API path detected');

        $origin = $request->headers->get('Origin');

        // Validate origin (allow localhost on any port for development, both HTTP and HTTPS)
        $allowedOriginPattern = '/^https?:\/\/(localhost|127\.0\.0\.1)(:[0-9]+)?$/';
        if (!$origin || !preg_match($allowedOriginPattern, $origin)) {
            $this->logger->warning('❌ CORS Listener - Invalid origin', ['origin' => $origin, 'pattern' => $allowedOriginPattern]);
            return; // Let the normal flow handle invalid origins
        }

        $this->logger->info('✅ CORS Listener - Valid origin, sending CORS response', ['origin' => $origin]);

        // Create response for preflight
        $response = new Response();
        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');
        $response->headers->set('Access-Control-Max-Age', '3600');
        $response->setStatusCode(Response::HTTP_NO_CONTENT);

        $event->setResponse($response);

        $this->logger->info('🎉 CORS Listener - Preflight response set successfully');
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 9999], // High priority to run before security
        ];
    }
}
