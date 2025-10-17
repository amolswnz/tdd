<?php

namespace App\Controllers;

use App\Models\SupportGroup;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Control\HTTPResponse;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\DataList;
use SilverStripe\Model\List\PaginatedList;
use SilverStripe\Security\SecurityToken;

/**
 * SupportGroupApiController
 *
 * API Controller for fetching support group data from the local database
 * Provides JSON endpoints for retrieving support group information
 *
 * @package App\Controllers
 */
class SupportGroupApiController extends Controller
{
    /**
     * Allowed actions for this controller
     */
    private static array $allowed_actions = [
        'index',
        'group',
        'list',
        'search'
    ];

    /**
     * URL handlers for custom routes
     */
    private static array $url_handlers = [
        'group/$ID!' => 'group',
        'search' => 'search',
        'list' => 'list',
        '' => 'index',
    ];

    /**
     * Default index action - API documentation
     */
    public function index(HTTPRequest $request): HTTPResponse
    {
        $endpoints = [
            'GET /api/support-groups/' => 'List all available API endpoints',
            'GET /api/support-groups/list' => 'Get paginated list of support groups',
            'GET /api/support-groups/group/{id}' => 'Get specific support group by ID',
            'GET /api/support-groups/search?q={query}' => 'Search support groups by name or group ID'
        ];

        $response = [
            'status' => 'success',
            'message' => 'Support Groups API v1.0',
            'endpoints' => $endpoints,
            'documentation' => 'Available endpoints for fetching support group data'
        ];

        return $this->jsonResponse($response);
    }

    /**
     * Get a paginated list of support groups
     */
    public function list(HTTPRequest $request): HTTPResponse
    {
        try {
            // Get query parameters
            $page = (int) $request->getVar('page') ?: 1;
            $limit = min((int) $request->getVar('limit') ?: 20, 100); // Max 100 per page
            $sort = $request->getVar('sort') ?: 'Name';
            $dir = strtoupper($request->getVar('dir') ?: 'ASC');

            // Validate sort direction
            if (!in_array($dir, ['ASC', 'DESC'])) {
                $dir = 'ASC';
            }

            // Get base support group list
            $supportGroups = SupportGroup::get();

            // Apply sorting
            $validSortFields = ['Name', 'GroupID', 'Created', 'LastEdited', 'AllowedImports'];
            if (in_array($sort, $validSortFields)) {
                $supportGroups = $supportGroups->sort("$sort $dir");
            } else {
                $supportGroups = $supportGroups->sort("Name ASC");
            }

            // Apply filters if provided
            if ($allowedImports = $request->getVar('allowed_imports')) {
                $allowedImports = filter_var($allowedImports, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($allowedImports !== null) {
                    $supportGroups = $supportGroups->filter('AllowedImports', $allowedImports);
                }
            }

            // Create paginated list
            $paginatedGroups = PaginatedList::create($supportGroups, $request);
            $paginatedGroups->setPageLength($limit);
            $paginatedGroups->setCurrentPage($page);

            $data = [];
            foreach ($paginatedGroups as $group) {
                $data[] = $this->formatSupportGroupData($group);
            }

            $response = [
                'status' => 'success',
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginatedGroups->CurrentPage(),
                    'total_pages' => $paginatedGroups->TotalPages(),
                    'page_length' => $paginatedGroups->getPageLength(),
                    'total_items' => $paginatedGroups->getTotalItems(),
                    'has_next' => $paginatedGroups->CurrentPage() < $paginatedGroups->TotalPages(),
                    'has_prev' => $paginatedGroups->CurrentPage() > 1
                ]
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch support groups: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Get a specific support group by ID
     */
    public function group(HTTPRequest $request): HTTPResponse
    {
        try {
            $groupId = (int) $request->param('ID');

            if (!$groupId) {
                return $this->errorResponse('Invalid support group ID provided', 400);
            }

            $supportGroup = SupportGroup::get()->byID($groupId);

            if (!$supportGroup) {
                return $this->errorResponse('Support group not found', 404);
            }

            $response = [
                'status' => 'success',
                'data' => $this->formatSupportGroupData($supportGroup, true) // Include detailed info
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to fetch support group: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Search support groups by name or group ID
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

            // Search across name and GroupID fields
            $supportGroups = SupportGroup::get()->filterAny([
                'Name:PartialMatch' => $query,
                'GroupID:PartialMatch' => $query
            ]);

            // Apply additional filters if provided
            if ($allowedImports = $request->getVar('allowed_imports')) {
                $allowedImports = filter_var($allowedImports, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($allowedImports !== null) {
                    $supportGroups = $supportGroups->filter('AllowedImports', $allowedImports);
                }
            }

            // Sort by name
            $supportGroups = $supportGroups->sort('Name ASC');

            // Paginate results
            $paginatedGroups = PaginatedList::create($supportGroups, $request);
            $paginatedGroups->setPageLength($limit);
            $paginatedGroups->setCurrentPage($page);

            $data = [];
            foreach ($paginatedGroups as $group) {
                $data[] = $this->formatSupportGroupData($group);
            }

            $response = [
                'status' => 'success',
                'query' => $query,
                'data' => $data,
                'pagination' => [
                    'current_page' => $paginatedGroups->CurrentPage(),
                    'total_pages' => $paginatedGroups->TotalPages(),
                    'page_length' => $paginatedGroups->getPageLength(),
                    'total_items' => $paginatedGroups->getTotalItems(),
                    'has_next' => $paginatedGroups->CurrentPage() < $paginatedGroups->TotalPages(),
                    'has_prev' => $paginatedGroups->CurrentPage() > 1
                ]
            ];

            return $this->jsonResponse($response);

        } catch (\Exception $e) {
            return $this->errorResponse('Search failed: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Format support group data for API response
     */
    private function formatSupportGroupData(SupportGroup $group, bool $detailed = false): array
    {
        $data = [
            'id' => $group->ID,
            'group_id' => $group->GroupID,
            'name' => $group->Name,
            'allowed_imports' => (bool) $group->AllowedImports,
            'created' => $group->Created,
            'updated' => $group->LastEdited
        ];

        if ($detailed) {
            $data = array_merge($data, [
                'title' => $group->getTitle(),
                'can_view' => $group->canView(),
                'can_edit' => $group->canEdit(),
                'can_delete' => $group->canDelete(),
                'class_name' => $group->ClassName
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
