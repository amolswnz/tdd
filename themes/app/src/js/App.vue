<template>
  <div class="p-6">
    <!-- Filter dropdowns -->
    <FilterControls
      :selected-filters="selectedFilters"
      @filter-changed="handleFilterChange"
    />

    <div class="flex justify-between items-center mb-4">
      <h2 class="text-2xl font-bold text-emerald-600">Freshservice Tickets</h2>

      <!-- Page size selector -->
      <div v-if="!loading && !error && tickets && tickets.length > 0" class="flex items-center space-x-2">
        <label for="pageSize" class="text-sm text-gray-700">Show:</label>
        <select
          id="pageSize"
          v-model="pageLength"
          @change="changePageSize"
          class="border border-gray-300 rounded-md px-3 py-1 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 cursor-pointer"
        >
          <option value="10">10</option>
          <option value="20">20</option>
          <option value="50">50</option>
          <option value="100">100</option>
        </select>
        <span class="text-sm text-gray-700">per page</span>
      </div>
    </div>

    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="text-gray-600">Loading tickets...</div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
      Error loading tickets: {{ error.message }}
    </div>

    <div v-else-if="tickets && tickets.length > 0">
      <div class="overflow-x-auto mt-4">
        <table class="min-w-full bg-white shadow-sm rounded-lg overflow-hidden">
          <thead class="bg-gray-50">
            <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
              <th v-for="header in headerGroup.headers" :key="header.id" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
                <div v-if="!header.isPlaceholder"
                     :class="{ 'cursor-pointer select-none hover:text-gray-900': header.column.getCanSort() }"
                     @click="header.column.getToggleSortingHandler()?.($event)"
                     class="flex items-center space-x-1">
                  <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                  <span v-if="header.column.getCanSort()" class="flex flex-col">
                    <svg v-if="header.column.getIsSorted() === 'asc'" class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else-if="header.column.getIsSorted() === 'desc'" class="w-3 h-3 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                      <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    <svg v-else class="w-3 h-3 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M5 12l5-5 5 5H5z" />
                      <path d="M5 8l5 5 5-5H5z" opacity="0.3" />
                    </svg>
                  </span>
                </div>
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50 transition-colors">
              <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
                <span v-if="cell.column.id === 'priority'" :class="getPriorityBadgeClass(row.original.priority)" class="px-2 py-1 rounded-full text-xs font-medium">
                  <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </span>
                <span v-else-if="cell.column.id === 'status'" :class="getStatusBadgeClass(row.original.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </span>
                <FlexRender v-else :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <PaginationControls
        :pagination="pagination"
        :current-page="currentPage"
        :total-pages="totalPages"
        :total-items="totalItems"
        :page-length="pageLength"
        @prev-page="prevPage"
        @next-page="nextPage"
        @first-page="firstPage"
        @last-page="lastPage"
        @go-to-page="goToPage"
      />
    </div>

    <div v-else class="text-center py-8 text-gray-500">
      No tickets found.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { FlexRender, useVueTable, getCoreRowModel, getSortedRowModel, createColumnHelper, SortingState } from '@tanstack/vue-table'
import { apiFetch } from './utils/api'
import PaginationControls from './components/PaginationControls.vue'
import FilterControls from './components/FilterControls.vue'

const tickets = ref(null)
const loading = ref(false)
const error = ref(null)

// Filter state
const selectedFilters = ref({
  assigned_agent_id: '',
  priority: '',
  status: '',
  type: '',
  category: '',
  sub_category: ''
})

// Pagination state
const currentPage = ref(1)
const totalPages = ref(1)
const totalItems = ref(0)
const pageLength = ref(20)
const pagination = ref(null)

// Sorting state
const sorting = ref([])

// Sorting function


// Define columns for the table
const columnHelper = createColumnHelper()

const columns = [
  columnHelper.accessor('freshservice_id', {
    header: 'FreshService ID',
    cell: ({ getValue }) => getValue(),
    enableSorting: true,
  }),
  columnHelper.accessor('department_id', {
    header: 'Department ID',
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('group_id', {
    header: 'Group ID',
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('priority', {
    header: 'Priority',
    enableSorting: true,
    cell: ({ getValue }) => {
      const priority = getValue()
      // Convert priority number to text
      const priorityMap = {
        1: 'Low',
        2: 'Medium',
        3: 'High',
        4: 'Urgent'
      }
      return priorityMap[priority] || priority
    },
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    enableSorting: true,
    cell: ({ getValue }) => {
      const status = getValue()
      // Convert status number to text
      const statusMap = {
        2: 'Open',
        3: 'Pending',
        4: 'Resolved',
        5: 'Closed',
        6: 'New'
      }
      return statusMap[status] || status
    },
  }),
  columnHelper.accessor('type', {
    header: 'Type',
    enableSorting: true,
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('category', {
    header: 'Category',
    enableSorting: true,
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('sub_category', {
    header: 'Sub Category',
    enableSorting: true,
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('subject', {
    header: 'Subject',
    enableSorting: true,
    cell: ({ getValue }) => {
      const subject = getValue()
      return subject && subject.length > 50
        ? subject.substring(0, 50) + '...'
        : subject
    },
  }),
  columnHelper.accessor('created_at', {
    header: 'Created At',
    enableSorting: true,
    cell: ({ getValue }) => {
      const date = getValue()
      return date ? new Date(date).toLocaleString() : ''
    },
  }),
  columnHelper.accessor('updated_at', {
    header: 'Updated At',
    enableSorting: true,
    cell: ({ getValue }) => {
      const date = getValue()
      return date ? new Date(date).toLocaleString() : ''
    },
  }),
]

// Priority badge styling function
const getPriorityBadgeClass = (priority) => {
  // Handle both numeric values and text values
  const priorityStr = String(priority).toLowerCase()

  // Handle numeric values
  if (priority === 1 || priorityStr === 'low') {
    return 'bg-green-100 text-green-800'
  }
  if (priority === 2 || priorityStr === 'medium') {
    return 'bg-yellow-100 text-yellow-800'
  }
  if (priority === 3 || priorityStr === 'high') {
    return 'bg-orange-100 text-orange-800'
  }
  if (priority === 4 || priorityStr === 'urgent') {
    return 'bg-red-100 text-red-800'
  }

  return 'bg-gray-100 text-gray-800'
}

// Status badge styling function
const getStatusBadgeClass = (status) => {
  const statusStr = String(status).toLowerCase()

  // Handle both numeric values and text values
  if (status === 6 || statusStr === 'new') {
    return 'bg-blue-100 text-blue-800'
  }
  if (status === 2 || statusStr === 'open') {
    return 'bg-green-100 text-green-800'
  }
  if (status === 3 || statusStr === 'pending') {
    return 'bg-yellow-100 text-yellow-800'
  }
  if (status === 4 || statusStr === 'resolved') {
    return 'bg-purple-100 text-purple-800'
  }
  if (status === 5 || statusStr === 'closed') {
    return 'bg-gray-100 text-gray-800'
  }

  return 'bg-gray-100 text-gray-800'
}

// Create the table instance
const table = useVueTable({
  get data() {
    return tickets.value || []
  },
  columns,
  state: {
    get sorting() {
      return sorting.value
    },
  },
  onSortingChange: updaterOrValue => {
    sorting.value =
      typeof updaterOrValue === 'function'
        ? updaterOrValue(sorting.value)
        : updaterOrValue
  },
  getCoreRowModel: getCoreRowModel(),
  getSortedRowModel: getSortedRowModel(),
})

// Load tickets with pagination and filters
const loadTickets = async (page = 1) => {
  loading.value = true
  try {
    // Build query params for filters
    const params = new URLSearchParams()
    params.append('page', page)
    params.append('limit', pageLength.value)
    Object.entries(selectedFilters.value).forEach(([key, val]) => {
      if (val !== '' && val !== null && val !== undefined) {
        params.append(key, val)
      }
    })
    const response = await apiFetch(`/api/tickets/list?${params.toString()}`)
    if (response.status === 'success' && response.data) {
      tickets.value = response.data
      if (response.pagination) {
        currentPage.value = response.pagination.current_page
        totalPages.value = response.pagination.total_pages
        totalItems.value = response.pagination.total_items
        pagination.value = response.pagination
      }
    } else {
      throw new Error('Invalid response format')
    }
  } catch (err) {
    console.log("Error loading tickets:", err)
    error.value = err
  } finally {
    loading.value = false
  }
}

// When a filter changes, reload tickets
const applyFilters = () => {
  currentPage.value = 1
  loadTickets(1)
}

// Handle filter changes from FilterControls component
const handleFilterChange = (filterKey, value) => {
  selectedFilters.value[filterKey] = value
  applyFilters()
}

// Pagination functions
const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    loadTickets(page)
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    goToPage(currentPage.value + 1)
  }
}

const prevPage = () => {
  if (currentPage.value > 1) {
    goToPage(currentPage.value - 1)
  }
}

const firstPage = () => {
  goToPage(1)
}

const lastPage = () => {
  goToPage(totalPages.value)
}

// Change page size and reload data
const changePageSize = () => {
  currentPage.value = 1 // Reset to first page when changing page size
  loadTickets(1)
}

// Load tickets on component mount
onMounted(async () => {
  await loadTickets(1)
})
</script>
