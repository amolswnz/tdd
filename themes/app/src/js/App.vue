<template>
  <div class="p-6">
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
                <span v-if="cell.column.id === 'priority'" :class="getPriorityBadgeClass(cell.getValue())" class="px-2 py-1 rounded-full text-xs font-medium">
                  <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </span>
                <span v-else-if="cell.column.id === 'status'" :class="getStatusBadgeClass(cell.getValue())" class="px-2 py-1 rounded-full text-xs font-medium">
                  <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
                </span>
                <FlexRender v-else :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="pagination && totalPages > 1" class="mt-12 flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
          <!-- Mobile pagination -->
          <button
            @click="prevPage"
            :disabled="currentPage <= 1"
            class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          <button
            @click="nextPage"
            :disabled="currentPage >= totalPages"
            class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </div>

        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing
              <span class="font-medium">{{ ((currentPage - 1) * pageLength) + 1 }}</span>
              to
              <span class="font-medium">{{ Math.min(currentPage * pageLength, totalItems) }}</span>
              of
              <span class="font-medium">{{ totalItems }}</span>
              results
            </p>
          </div>

          <div>
            <nav class="isolate inline-flex -space-x-px rounded-md shadow-sm" aria-label="Pagination">
              <!-- First page button -->
              <button
                @click="firstPage"
                :disabled="currentPage <= 1"
                class="cursor-pointer relative inline-flex items-center rounded-l-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
              >First
              </button>

              <!-- Previous page button -->
              <button
                @click="prevPage"
                :disabled="currentPage <= 1"
                class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="sr-only">Previous</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z" clip-rule="evenodd" />
                </svg>
              </button>

              <!-- Page numbers -->
              <template v-for="page in getVisiblePages()" :key="page">
                <button
                  v-if="page !== '...'"
                  @click="goToPage(page)"
                  :class="[
                    'cursor-pointer relative inline-flex items-center px-4 py-2 text-sm font-semibold ring-1 ring-inset ring-gray-300 hover:bg-blue-50 focus:z-20 focus:outline-offset-0',
                    page === currentPage
                      ? 'z-10 bg-emerald-600 text-white focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-emerald-600 hover:bg-emerald-700'
                      : 'text-gray-900'
                  ]"
                >
                  {{ page }}
                </button>
                <span v-else class="relative inline-flex items-center px-4 py-2 text-sm font-semibold text-gray-700 ring-1 ring-inset ring-gray-300 focus:outline-offset-0">
                  ...
                </span>
              </template>

              <!-- Next page button -->
              <button
                @click="nextPage"
                :disabled="currentPage >= totalPages"
                class="relative inline-flex items-center px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                <span class="sr-only">Next</span>
                <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                  <path fill-rule="evenodd" d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z" clip-rule="evenodd" />
                </svg>
              </button>

              <!-- Last page button -->
              <button
                @click="lastPage"
                :disabled="currentPage >= totalPages"
                class="cursor-pointer relative inline-flex items-center rounded-r-md px-2 py-2 text-gray-400 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0 disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Last
              </button>
            </nav>
          </div>
        </div>
      </div>
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

const tickets = ref(null)
const loading = ref(false)
const error = ref(null)

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
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('status', {
    header: 'Status',
    enableSorting: true,
    cell: ({ getValue }) => getValue(),
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
  console.log("Badge class for priority:", priority)

  // Handle the priority name from our API
  const priorityStr = String(priority).toLowerCase()

  if (priorityStr === 'low') {
    return 'bg-green-100 text-green-800'
  }
  if (priorityStr === 'medium') {
    return 'bg-yellow-100 text-yellow-800'
  }
  if (priorityStr === 'high') {
    return 'bg-orange-100 text-orange-800'
  }
  if (priorityStr === 'urgent') {
    return 'bg-red-100 text-red-800'
  }

  return 'bg-gray-100 text-gray-800'
}

// Status badge styling function
const getStatusBadgeClass = (status) => {
  const statusStr = String(status).toLowerCase()

  if (statusStr === 'new') {
    return 'bg-blue-100 text-blue-800'
  }
  if (statusStr === 'open') {
    return 'bg-green-100 text-green-800'
  }
  if (statusStr === 'pending') {
    return 'bg-yellow-100 text-yellow-800'
  }
  if (statusStr === 'resolved') {
    return 'bg-purple-100 text-purple-800'
  }
  if (statusStr === 'closed') {
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

// Load tickets with pagination
const loadTickets = async (page = 1) => {
  loading.value = true
  try {
    const response = await apiFetch(`/api/tickets/list?page=${page}&limit=${pageLength.value}`)
    // Our API returns data in a nested structure with status and data properties
    if (response.status === 'success' && response.data) {
      tickets.value = response.data

      // Update pagination info
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

// Generate visible page numbers for pagination
const getVisiblePages = () => {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    // Show all pages if total is 7 or less
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Complex pagination logic
    if (current <= 4) {
      // Near the beginning
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 3) {
      // Near the end
      pages.push(1)
      pages.push('...')
      for (let i = total - 4; i <= total; i++) {
        pages.push(i)
      }
    } else {
      // In the middle
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    }
  }

  return pages
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
