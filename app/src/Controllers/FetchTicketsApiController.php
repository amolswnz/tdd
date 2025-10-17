<?php

namespace App\Controllers;

use App\Models\Ticket;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataList;
use SilverStripe\Model\List\PaginatedList;
use SilverStripe\Security\SecurityToken;

/**
 * FetchTicketsApiController
 *
 * API Controller for fetching ticket data from the local database
 * Provides JSON endpoints for retrieving ticket information
 *
 * @package App\Controllers
 */
class FetchTicketsApiController extends Controller
{
    /**
     * Allowed actions for this controller
     */
    private static array $allowed_actions = [
        'index',
        'ticket',
        'list',
        'search',
        'stats'
    ];

    /**
     * URL handlers for custom routes
     */
    private static array $url_handlers = [
        'ticket/$ID!' => 'ticket',
        'search' => 'search',
        'stats' => 'stats',
        'list' => 'list',
        '' => 'index',
    ];

    /**
     * Default index action - API documentation
     */
    public function index(HTTPRequest $request): HTTPResponse
    {
        $endpoints = [
            'GET /api/tickets/' => 'List all available API endpoints',
            'GET /api/tickets/list' => 'Get paginated list of tickets',
            'GET /api/tickets/ticket/{id}' => 'Get specific ticket by ID',
            'GET /api/tickets/search?q={query}' => 'Search tickets by various criteria',
            'GET /api/tickets/stats' => 'Get ticket statistics'
        ];

        $availableParameters = [
            'list endpoint' => [
                'page' => 'Page number (default: 1)',
                'limit' => 'Items per page (max: 100, default: 20)',
                'sort' => 'Sort field (CreatedAt, UpdatedAt, FreshServiceID, Priority, Status, Subject, Type, Category, SubCategory)',
                'dir' => 'Sort direction (ASC, DESC, default: DESC)',
                'priority' => 'Filter by priority (1-4: Low, Medium, High, Urgent)',
                'status' => 'Filter by status (1: New, 2: Open, 3: Pending, 4: Resolved, 5: Closed)',
                'group_id' => 'Filter by support group ID',
                'department_id' => 'Filter by department ID',
                'assigned_agent_id' => 'Filter by assigned agent ID'
            ],
            'search endpoint' => [
                'q' => 'Search query (searches Subject, Category, SubCategory, Type, FreshServiceID)',
                'page' => 'Page number',
                'limit' => 'Items per page',
                'priority' => 'Filter by priority',
                'status' => 'Filter by status',
                'assigned_agent_id' => 'Filter by assigned agent ID'
            ]
        ];

        $response = [
            'status' => 'success',
            'message' => 'Tickets API v1.0',
            'endpoints' => $endpoints,
            'parameters' => $availableParameters,
            'documentation' => 'Available endpoints for fetching ticket data from the local database'
        ];

        return $this->jsonResponse($response);
    }

    /**
     * Get a paginated list of tickets
     */
    public function list(HTTPRequest $request): HTTPResponse
    {
        try {
            // Get query parameters
            $page = (int) $request->getVar('page') ?: 1;
            $limit = min((int) $request->getVar('limit') ?: 20, 100); // Max 100 per page
            $sort = $request->getVar('sort') ?: 'CreatedAt';
            $dir = strtoupper($request->getVar('dir') ?: 'DESC');

            // Validate sort direction
            if (!in_array($dir, ['ASC', 'DESC'])) {
                $dir = 'DESC';
            }

            // Get base ticket list
            $tickets = Ticket::get();

            // Apply sorting
            $validSortFields = ['Created', 'LastEdited', 'FreshServiceID', 'Priority', 'Status', 'Subject', 'CreatedAt', 'UpdatedAt', 'Type', 'Category', 'SubCategory'];
            if (in_array($sort, $validSortFields)) {
                $tickets = $tickets->sort("$sort $dir");
            } else {
                $tickets = $tickets->sort("CreatedAt DESC");
            }

            // Apply filters if provided
            if ($priority = $request->getVar('priority')) {
                $tickets = $tickets->filter('Priority', $priority);
            }

            if ($status = $request->getVar('status')) {
                $tickets = $tickets->filter('Status', $status);
            }

            if ($groupId = $request->getVar('group_id')) {
                $tickets = $tickets->filter('GroupID', $groupId);
            }

            if ($departmentId = $request->getVar('department_id')) {
                $tickets = $tickets->filter('DepartmentID', $departmentId);
            }

            if ($assignedAgentId = $request->getVar('assigned_agent_id')) {
                $tickets = $tickets->filter('AssignedAgentID', $assignedAgentId);
            }

            // Create paginated list
            $paginatedTickets = PaginatedList::create($tickets, $request);
            $paginatedTickets->setPageLength($limit);
            $paginatedTickets->setCurrentPage($page);

            $data = [];
            foreach ($paginatedTickets as $ticket) {
                $data[] = $this->formatTicketData($ticket);
            }

            $response = [
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginatedTickets->CurrentPage(),
                    'total_pages' => $paginatedTickets->TotalPages(),
                    'page_length' => $paginatedTickets->getPageLength(),
                    'total_items' => $paginatedTickets->getTotalItems(),
                    'has_next' => $paginatedTickets->hasNext(),
                    'has_prev' => $paginatedTickets->hasPrev()
                ]
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch tickets: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific ticket by ID
     */
    public function ticket(HTTPRequest $request): HTTPResponse
    {
        try {
            $ticketId = (int) $request->param('ID');

            if (!$ticketId) {
                return $this->errorResponse('Invalid ticket ID provided', 400);
            }

            $ticket = Ticket::get()->byID($ticketId);

            if (!$ticket) {
                return $this->errorResponse('Ticket not found', 404);
            }

            $response = [
                'status' => 'success',
                'data' => $this->formatTicketData($ticket, true) // Include detailed info
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch ticket: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search tickets by various criteria
     */
    public function search(HTTPRequest $request): HTTPResponse
    {
        try {
            $query = $request->getVar('q');
            $page = (int) $request->getVar('page') ?: 1;
            $limit = min((int) $request->getVar('limit') ?: 20, 100);

            if (empty($query)) {
                return $this->errorResponse('Search query parameter "q" is required', 400);
            }

            // Search across multiple fields that exist in the Ticket model
            $tickets = Ticket::get()->filterAny([
                'Subject:PartialMatch' => $query,
                'Category:PartialMatch' => $query,
                'SubCategory:PartialMatch' => $query,
                'Type:PartialMatch' => $query,
                'FreshServiceID' => $query
            ]);

            // Apply additional filters if provided
            if ($priority = $request->getVar('priority')) {
                $tickets = $tickets->filter('Priority', $priority);
            }

            if ($status = $request->getVar('status')) {
                $tickets = $tickets->filter('Status', $status);
            }

            if ($assignedAgentId = $request->getVar('assigned_agent_id')) {
                $tickets = $tickets->filter('AssignedAgentID', $assignedAgentId);
            }

            // Sort by relevance (most recent first)
            $tickets = $tickets->sort('CreatedAt DESC');

            // Paginate results
            $paginatedTickets = PaginatedList::create($tickets, $request);
            $paginatedTickets->setPageLength($limit);
            $paginatedTickets->setCurrentPage($page);

            $data = [];
            foreach ($paginatedTickets as $ticket) {
                $data[] = $this->formatTicketData($ticket);
            }

            $response = [
                'status' => 'success',
                'query' => $query,
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginatedTickets->CurrentPage(),
                    'total_pages' => $paginatedTickets->TotalPages(),
                    'page_length' => $paginatedTickets->getPageLength(),
                    'total_items' => $paginatedTickets->getTotalItems(),
                    'has_next' => $paginatedTickets->hasNext(),
                    'has_prev' => $paginatedTickets->hasPrev()
                ]
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Search failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get ticket statistics
     */
    public function stats(HTTPRequest $request): HTTPResponse
    {
        try {
            $allTickets = Ticket::get();

            // Priority statistics
            $priorityStats = [];
            for ($i = 1; $i <= 4; $i++) {
                $priorityStats[$i] = $allTickets->filter('Priority', $i)->count();
            }

            // Status statistics
            $statusStats = [];
            $statuses = $allTickets->column('Status');
            $uniqueStatuses = array_unique(array_filter($statuses));
            foreach ($uniqueStatuses as $status) {
                $statusStats[$status] = $allTickets->filter('Status', $status)->count();
            }

            // Type statistics
            $typeStats = [];
            $types = $allTickets->column('Type');
            $uniqueTypes = array_unique(array_filter($types));
            foreach ($uniqueTypes as $type) {
                $typeStats[$type] = $allTickets->filter('Type', $type)->count();
            }

            $response = [
                'status' => 'success',
                'data' => [
                    'total_tickets' => $allTickets->count(),
                    'priority_breakdown' => [
                        'low' => $priorityStats[1] ?? 0,
                        'medium' => $priorityStats[2] ?? 0,
                        'high' => $priorityStats[3] ?? 0,
                        'urgent' => $priorityStats[4] ?? 0
                    ],
                    'status_breakdown' => $statusStats,
                    'type_breakdown' => $typeStats,
                    'last_updated' => date('c')
                ]
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to generate statistics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format ticket data for API response
     */
    private function formatTicketData(Ticket $ticket, bool $detailed = false): array
    {
        $data = [
            'id' => $ticket->ID,
            'freshservice_id' => $ticket->FreshServiceID,
            'subject' => $ticket->Subject,
            'priority' => $ticket->Priority,
            'priority_name' => $ticket->getPriorityName(),
            'status' => $ticket->Status,
            'status_name' => $ticket->getStatusName(),
            'type' => $ticket->Type,
            'category' => $ticket->Category,
            'sub_category' => $ticket->SubCategory,
            'created_at' => $ticket->CreatedAt,
            'updated_at' => $ticket->UpdatedAt,
            'created' => $ticket->Created,
            'updated' => $ticket->LastEdited
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'department_id' => $ticket->DepartmentID,
                'group_id' => $ticket->GroupID,
                'assigned_agent_id' => $ticket->AssignedAgentID,
                'resolved_at' => $ticket->ResolvedAt,
                'closed_at' => $ticket->ClosedAt,
                'last_synced_at' => $ticket->LastSyncedAt,
                'priority_css_class' => $ticket->getPriorityCSSClass(),
                'status_css_class' => $ticket->getStatusCSSClass(),
                'is_open' => $ticket->isOpen(),
                'is_resolved' => $ticket->isResolved(),
                'is_closed' => $ticket->isClosed()
            ]);
        }

        return $data;
    }

    /**
     * Return a JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): HTTPResponse
    {
        $response = HTTPResponse::create();
        $response->addHeader('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
        $response->setBody(json_encode($data));

        return $response;
    }

    /**
     * Return an error response
     */
    private function errorResponse(string $message, int $statusCode = 400): HTTPResponse
    {
        $data = [
            'status' => 'error',
            'message' => $message,
            'code' => $statusCode
        ];

        return $this->jsonResponse($data, $statusCode);
    }
}
