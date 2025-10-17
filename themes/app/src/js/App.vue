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
          <tr>
            <th
              v-for="header in table.getFlatHeaders()"
              :key="header.id"
              class="px-4 py-3 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider border-b border-gray-200"
            >
              {{ header.column.columnDef.header }}
            </th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr
            v-for="row in table.getRowModel().rows"
            :key="row.id"
            class="hover:bg-gray-50 transition-colors"
          >
            <td
              v-for="cell in row.getVisibleCells()"
              :key="cell.id"
              class="px-4 py-3 text-sm text-gray-600 whitespace-nowrap"
            >
              {{ cell.getValue() }}
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
import { ref, onMounted, computed } from 'vue'
import { useVueTable, getCoreRowModel, createColumnHelper } from '@tanstack/vue-table'
import { apiFetch } from './utils/api'

const tickets = ref(null)
const loading = ref(false)
const error = ref(null)

// Define columns for the table
const columnHelper = createColumnHelper()

const columns = [
  columnHelper.accessor('id', {
    header: 'ID',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('department_id', {
    header: 'Department ID',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('group_id', {
    header: 'Group ID',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('priority', {
    header: 'Priority',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('type', {
    header: 'Type',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('category', {
    header: 'Category',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('sub_category', {
    header: 'Sub Category',
    cell: info => info.getValue(),
  }),
  columnHelper.accessor('subject', {
    header: 'Subject',
    cell: info => {
      const subject = info.getValue()
      return subject && subject.length > 50
        ? subject.substring(0, 50) + '...'
        : subject
    },
  }),
  columnHelper.accessor('created_at', {
    header: 'Created At',
    cell: info => {
      const date = info.getValue()
      return date ? new Date(date).toLocaleString() : ''
    },
  }),
  columnHelper.accessor('updated_at', {
    header: 'Updated At',
    cell: info => {
      const date = info.getValue()
      return date ? new Date(date).toLocaleString() : ''
    },
  }),
]

// Create the table instance
const table = computed(() =>
  useVueTable({
    data: tickets.value || [],
    columns,
    getCoreRowModel: getCoreRowModel(),
  })
)

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
