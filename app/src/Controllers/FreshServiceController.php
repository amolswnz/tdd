<?php

namespace App\Controllers;

use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\SiteConfig\SiteConfig;
use SilverStripe\Core\Convert;
use SilverStripe\View\Requirements;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * FreshServiceController
 *
 * API Controller for FreshService integration - Read-only operations
 */
class FreshServiceController extends Controller
{
    /**
     * Allowed actions for this controller
     */
    private static $allowed_actions = [
        'tickets',
        'ticket',
        'test',
        'dashboard',
        'index',
    ];

    /**
     * URL handlers for custom routes
     */
    private static $url_handlers = [
        'tickets/$ID' => 'ticket',
        'tickets' => 'tickets',
        'test' => 'test',
        'dashboard' => 'dashboard',
        '' => 'index',
    ];

    /**
     * Default index action - shows API documentation or redirects to dashboard
     */
    public function index(HTTPRequest $request)
    {
        return $this->redirect($this->Link('dashboard'));
    }

    /**
     * Dashboard view showing FreshService integration status
     */
    public function dashboard(HTTPRequest $request)
    {
        // No longer requiring Bootstrap since we're using Tailwind
        Requirements::css('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

        return $this->customise([
            'Title' => 'FreshService Dashboard',
            'FreshServiceData' => $this->getFreshServiceData(),
        ])->renderWith(['FreshServiceDashboard', 'Page']);
    }

    /**
     * Get list of tickets from FreshService API
     */
    public function tickets(HTTPRequest $request): HTTPResponse
    {
        $siteConfig = SiteConfig::current_site_config();

        if (!$siteConfig->isFreshServiceConfigured()) {
            return $this->httpError(500, 'FreshService API is not configured');
        }

        if (!$this->canAccessAPI()) {
            return $this->httpError(403, 'Access denied');
        }

        try {
            $tickets = $this->fetchTicketsFromAPI([
                'per_page' => $request->getVar('per_page') ?: 30,
                'page' => $request->getVar('page') ?: 1,
            ]);

            return $this->getResponse()
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode($tickets));

        } catch (\Exception $e) {
            return $this->httpError(500, 'Failed to fetch tickets: ' . $e->getMessage());
        }
    }

    /**
     * Get a specific ticket by ID
     */
    public function ticket(HTTPRequest $request): HTTPResponse
    {
        $siteConfig = SiteConfig::current_site_config();

        if (!$siteConfig->isFreshServiceConfigured()) {
            return $this->httpError(500, 'FreshService API is not configured');
        }

        if (!$this->canAccessAPI()) {
            return $this->httpError(403, 'Access denied');
        }

        $ticketId = $request->param('ID');
        if (!$ticketId || !is_numeric($ticketId)) {
            return $this->httpError(400, 'Invalid ticket ID');
        }

        try {
            $ticket = $this->fetchTicketFromAPI($ticketId);

            return $this->getResponse()
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode($ticket));

        } catch (\Exception $e) {
            return $this->httpError(500, 'Failed to fetch ticket: ' . $e->getMessage());
        }
    }

    /**
     * Test API connection
     */
    public function test(HTTPRequest $request): HTTPResponse
    {
        $siteConfig = SiteConfig::current_site_config();

        if (!$siteConfig->isFreshServiceConfigured()) {
            return $this->getResponse()
                ->addHeader('Content-Type', 'application/json')
                ->setStatusCode(500)
                ->setBody(json_encode([
                    'status' => 'error',
                    'message' => 'FreshService API is not configured'
                ]));
        }

        try {
            $testResult = $this->testAPIConnection();

            return $this->getResponse()
                ->addHeader('Content-Type', 'application/json')
                ->setBody(json_encode([
                    'status' => 'success',
                    'message' => 'API connection successful',
                    'endpoint' => $siteConfig->getFreshServiceApiUrl(),
                    'result' => $testResult
                ]));

        } catch (\Exception $e) {
            return $this->getResponse()
                ->addHeader('Content-Type', 'application/json')
                ->setStatusCode(500)
                ->setBody(json_encode([
                    'status' => 'error',
                    'message' => 'API connection failed: ' . $e->getMessage()
                ]));
        }
    }

    /**
     * Fetch tickets from FreshService API using cURL
     */
    private function fetchTicketsFromAPI(array $params = []): array
    {
        $siteConfig = SiteConfig::current_site_config();
        $url = $siteConfig->getFreshServiceApiUrl() . '/tickets';

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        return $this->makeAPIRequest('GET', $url);
    }

    /**
     * Fetch a specific ticket from FreshService API
     */
    private function fetchTicketFromAPI(int $ticketId): array
    {
        $siteConfig = SiteConfig::current_site_config();
        $url = $siteConfig->getFreshServiceApiUrl() . "/tickets/{$ticketId}";

        return $this->makeAPIRequest('GET', $url);
    }



    /**
     * Test API connection
     */
    private function testAPIConnection(): array
    {
        $siteConfig = SiteConfig::current_site_config();
        $url = $siteConfig->getFreshServiceApiUrl() . '/tickets?per_page=1';

        return $this->makeAPIRequest('GET', $url);
    }

    /**
     * Make an authenticated API request to FreshService using Guzzle
     */
    private function makeAPIRequest(string $method, string $url, array $data = null): array
    {
        $siteConfig = SiteConfig::current_site_config();

        $client = new Client([
            'timeout' => 30,
            'auth' => [$siteConfig->FreshServiceAPIKey, 'X'],
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        try {
            $options = [];

            if ($data && in_array(strtoupper($method), ['POST', 'PUT', 'PATCH'])) {
                $options['json'] = $data;
            }

            $response = $client->request($method, $url, $options);

            $body = $response->getBody()->getContents();
            $decodedResponse = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response: " . json_last_error_msg());
            }

            return $decodedResponse;

        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();
                throw new \Exception("HTTP Error {$statusCode}: {$body}");
            } else {
                throw new \Exception("Request Error: " . $e->getMessage());
            }
        } catch (\Exception $e) {
            throw new \Exception("API Request Error: " . $e->getMessage());
        }
    }

    /**
     * Template data for the page
     */
    public function getFreshServiceData(): array
    {
        $siteConfig = SiteConfig::current_site_config();

        return [
            'IsConfigured' => $siteConfig->isFreshServiceConfigured(),
            'Domain' => $siteConfig->FreshServiceDomain,
            'ApiUrl' => $siteConfig->getFreshServiceApiUrl(),
            'HasApiKey' => !empty($siteConfig->FreshServiceAPIKey),
            'CanAccess' => $this->canAccessAPI(),
        ];
    }

    /**
     * Check if the current user can access the API functionality
     */
    public function canAccessAPI(): bool
    {
        // Add your permission logic here
        // For example, check if user is logged in or has specific permissions
        $member = $this->getCurrentUser();
        return $member && $member->exists();
    }

    /**
     * Get current logged in member
     */
    public function getCurrentUser()
    {
        return $this->getRequest()->getSession()->get('loggedInAs')
            ? \SilverStripe\Security\Member::get()->byID($this->getRequest()->getSession()->get('loggedInAs'))
            : null;
    }
}
