<?php

namespace App\Tasks;

use App\Models\Ticket;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use SilverStripe\Dev\BuildTask;
use SilverStripe\PolyExecution\PolyOutput;
use SilverStripe\SiteConfig\SiteConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Throwable;

/**
 * Fetch FreshService Latest Data Task
 *
 * Fetches tickets from FreshService API and updates/creates local Ticket models
 *
 * Usage: vendor/bin/sake fetch-freshservice-latest-data
 * or via web: /dev/tasks/FetchFreshserviceLatestData
 */
class FetchFreshserviceLatestData extends BuildTask
{
    protected static string $commandName = 'fetch-freshservice-latest-data';

    protected string $title = 'Fetch FreshService Latest Data';

    protected static string $description = 'Fetches tickets from FreshService API and updates local Ticket models';

    protected function execute(InputInterface $input, PolyOutput $output): int
    {
        $output->writeln('Starting FreshService data fetch...');

        // Check if FreshService is configured
        $siteConfig = SiteConfig::current_site_config();
        if (!$siteConfig->isFreshServiceConfigured()) {
            $output->writeln('<error>FreshService API is not configured. Please configure it in Site Settings.</error>');
            return Command::FAILURE;
        }

        $output->writeln('FreshService API configured. Endpoint: ' . $siteConfig->getFreshServiceApiUrl());

        // Get pagination parameters
        $perPage = (int) $input->getOption('per-page');
        $maxPages = (int) $input->getOption('max-pages');
        $updateExisting = $input->getOption('update-existing');

        $output->writeln("Parameters: per-page={$perPage}, max-pages={$maxPages}, update-existing=" . ($updateExisting ? 'yes' : 'no'));

        $totalFetched = 0;
        $totalCreated = 0;
        $totalUpdated = 0;
        $totalErrors = 0;

        try {
            for ($page = 1; $page <= $maxPages; $page++) {
                $output->writeln("Fetching page {$page}...");

                $tickets = $this->fetchTicketsFromAPI($siteConfig, [
                    'per_page' => $perPage,
                    'page' => $page,
                    'query' => 'priority:3',
                ]);

                if (empty($tickets['tickets'])) {
                    $output->writeln('No more tickets found.');
                    break;
                }

                $pageCount = count($tickets['tickets']);
                $output->writeln("Processing {$pageCount} tickets from page {$page}...");

                foreach ($tickets['tickets'] as $ticketData) {
                    try {
                        $result = $this->processTicket($ticketData, $updateExisting, $output);
                        $totalFetched++;

                        if ($result['created']) {
                            $totalCreated++;
                            $output->writeln("  ✓ Created ticket #{$ticketData['id']}: {$ticketData['subject']}");
                        } elseif ($result['updated']) {
                            $totalUpdated++;
                            $output->writeln("  ✓ Updated ticket #{$ticketData['id']}: {$ticketData['subject']}");
                        } else {
                            $output->writeln("  - Skipped ticket #{$ticketData['id']} (exists, update-existing=false)");
                        }
                    } catch (Throwable $e) {
                        $totalErrors++;
                        $output->writeln("  ✗ Error processing ticket #{$ticketData['id']}: " . $e->getMessage());
                    }
                }

                // If we got fewer tickets than requested, we've reached the end
                if ($pageCount < $perPage) {
                    $output->writeln('Reached end of tickets.');
                    break;
                }
            }
        } catch (Throwable $e) {
            $output->writeln('<error>Failed to fetch tickets: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }

        $output->writeln('');
        $output->writeln('=== Summary ===');
        $output->writeln("Total fetched: {$totalFetched}");
        $output->writeln("Created: {$totalCreated}");
        $output->writeln("Updated: {$totalUpdated}");
        $output->writeln("Errors: {$totalErrors}");
        $output->writeln('Task completed successfully!');

        return Command::SUCCESS;
    }

    /**
     * Process a single ticket from the API data
     */
    private function processTicket(array $ticketData, bool $updateExisting, PolyOutput $output): array
    {
        // Check if ticket already exists
        $existingTicket = Ticket::get()->filter('FreshServiceID', $ticketData['id'])->first();

        if ($existingTicket && !$updateExisting) {
            return ['created' => false, 'updated' => false];
        }

        // Create or update the ticket using the model's static method
        $ticket = Ticket::createFromFreshServiceData($ticketData);

        return [
            'created' => !$existingTicket,
            'updated' => (bool) $existingTicket,
            'ticket' => $ticket
        ];
    }

    /**
     * Fetch tickets from FreshService API
     */
    private function fetchTicketsFromAPI(SiteConfig $siteConfig, array $params = []): array
    {
        $url = $siteConfig->getFreshServiceApiUrl() . '/tickets';

        // curl -v -u YOUR_API_KEY:X -H "Content-Type: application/json" -X GET 'API_URL/tickets/filter?per_page=4&query="priority:3"'
        // curl -v -u YOUR_API_KEY:X -H "Content-Type: application/json" -X GET 'API_URL/tickets?per_page=4'

        $prepend = '?';
        $query = '';
        if (count($params) > 0) {
            if (array_key_exists('query', $params)) {
                // Query parameter cannot be encoded within http_build_query
                $query = sprintf('&query="%s"', $params['query']);
                $prepend = '/filter?';
                unset($params['query']);
            }
            $url .= $prepend . http_build_query($params) . $query;
        }

        return $this->makeAPIRequest($siteConfig, 'GET', $url);
    }

    /**
     * Make an authenticated API request to FreshService
     */
    private function makeAPIRequest(SiteConfig $siteConfig, string $method, string $url, ?array $data = null): array
    {
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
                throw new Exception('Invalid JSON response: ' . json_last_error_msg());
            }

            return $decodedResponse;
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                $body = $response->getBody()->getContents();

                throw new Exception('HTTP Error ' . $statusCode . ': ' . $body);
            }

            throw new Exception('Request Error: ' . $e->getMessage());
        } catch (Throwable $e) {
            throw new Exception('API Request Error: ' . $e->getMessage());
        }
    }

    /**
     * Get available command options
     */
    public function getOptions(): array
    {
        return [
            new InputOption('per-page', 'p', InputOption::VALUE_OPTIONAL, 'Number of tickets to fetch per page', '30'),
            new InputOption('max-pages', 'm', InputOption::VALUE_OPTIONAL, 'Maximum number of pages to fetch', '10'),
            new InputOption('update-existing', 'u', InputOption::VALUE_NONE, 'Update existing tickets if they already exist in the database'),
        ];
    }
}
