<template>
  <div class="p-6">
    <h2 class="text-2xl font-bold text-emerald-600 mb-4">Freshservice Tickets</h2>

    <div v-if="loading" class="flex justify-center items-center py-8">
      <div class="text-gray-600">Loading tickets...</div>
    </div>

    <div v-else-if="error" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md">
      Error loading tickets: {{ error.message }}
    </div>

    <div v-else-if="tickets && tickets.length > 0" class="overflow-x-auto mt-4">
      <table class="min-w-full bg-white shadow-sm rounded-lg overflow-hidden">
        <thead class="bg-gray-50">
          <tr v-for="headerGroup in table.getHeaderGroups()" :key="headerGroup.id">
            <th v-for="header in headerGroup.headers" :key="header.id" class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200">
              <FlexRender v-if="!header.isPlaceholder" :render="header.column.columnDef.header" :props="header.getContext()" />
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-for="row in table.getRowModel().rows" :key="row.id" class="hover:bg-gray-50 transition-colors">
            <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap">
              <span v-if="cell.column.id === 'priority'" :class="getPriorityBadgeClass(cell.getValue())" class="px-2 py-1 rounded-full text-xs font-medium">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </span>
              <FlexRender v-else :render="cell.column.columnDef.cell" :props="cell.getContext()" />
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="text-center py-8 text-gray-500">
      No tickets found.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { FlexRender, useVueTable, getCoreRowModel, createColumnHelper } from '@tanstack/vue-table'
import { apiFetch } from './utils/api'

const tickets = ref(null)
const loading = ref(false)
const error = ref(null)

// Define columns for the table
const columnHelper = createColumnHelper()

const columns = [
  columnHelper.accessor('id', {
    header: 'ID',
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
    cell: ({ getValue }) => {
      const priority = parseInt(getValue())
      switch (priority) {
        case 1:
          return 'Low'
        case 2:
          return 'Medium'
        case 3:
          return 'High'
        case 4:
          return 'Urgent'
        default:
          console.log("Unmatched priority value:", rawValue)
          return rawValue || 'Unknown'
      }
    },
  }),
  columnHelper.accessor('type', {
    header: 'Type',
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('category', {
    header: 'Category',
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('sub_category', {
    header: 'Sub Category',
    cell: ({ getValue }) => getValue(),
  }),
  columnHelper.accessor('subject', {
    header: 'Subject',
    cell: ({ getValue }) => {
      const subject = getValue()
      return subject && subject.length > 50
        ? subject.substring(0, 50) + '...'
        : subject
    },
  }),
  columnHelper.accessor('created_at', {
    header: 'Created At',
    cell: ({ getValue }) => {
      const date = getValue()
      return date ? new Date(date).toLocaleString() : ''
    },
  }),
  columnHelper.accessor('updated_at', {
    header: 'Updated At',
    cell: ({ getValue }) => {
      const date = getValue()
      return date ? new Date(date).toLocaleString() : ''
    },
  }),
]

// Priority badge styling function
const getPriorityBadgeClass = (priority) => {
  console.log("Badge class for priority:", priority)

  // Handle both converted text and raw values
  const priorityStr = String(priority).toLowerCase()

  if (priorityStr === 'low' || priorityStr === '1') {
    return 'bg-green-100 text-green-800'
  }
  if (priorityStr === 'medium' || priorityStr === '2') {
    return 'bg-yellow-100 text-yellow-800'
  }
  if (priorityStr === 'high' || priorityStr === '3') {
    return 'bg-orange-100 text-orange-800'
  }
  if (priorityStr === 'urgent' || priorityStr === '4') {
    return 'bg-red-100 text-red-800'
  }

  return 'bg-gray-100 text-gray-800'
}

// Create the table instance
const table = useVueTable({
  get data() {
    return tickets.value || []
  },
  columns,
  getCoreRowModel: getCoreRowModel(),
})

// Load tickets on component mount
onMounted(async () => {
  loading.value = true
  try {
    tickets.value = await apiFetch('/api/freshservice/tickets')
  } catch (err) {
    console.log("Error loading tickets:", err)
    error.value = err
  } finally {
    loading.value = false
  }
})
</script>
