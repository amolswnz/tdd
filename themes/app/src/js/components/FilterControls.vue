<template>
  <div class="flex flex-wrap gap-4 mb-6">
    <div>
      <label class="block text-xs text-gray-600 mb-1">Agent</label>
      <select
        :model-value="selectedFilters.assigned_agent_id"
        @update:model-value="updateFilter('assigned_agent_id', $event)"
        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
      >
        <option value="">All</option>
        <option v-for="agent in filters.assigned_agents" :key="agent.value" :value="agent.value">
          {{ agent.label }} ({{ agent.count }})
        </option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Priority</label>
      <select
        :model-value="selectedFilters.priority"
        @update:model-value="updateFilter('priority', $event)"
        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
      >
        <option value="">All</option>
        <option v-for="priority in filters.priorities" :key="priority.value" :value="priority.value">
          {{ priority.label }} ({{ priority.count }})
        </option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Status</label>
      <select
        :model-value="selectedFilters.status"
        @update:model-value="updateFilter('status', $event)"
        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
      >
        <option value="">All</option>
        <option v-for="status in filters.statuses" :key="status.value" :value="status.value">
          {{ status.label }} ({{ status.count }})
        </option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Type</label>
      <select
        :model-value="selectedFilters.type"
        @update:model-value="updateFilter('type', $event)"
        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
      >
        <option value="">All</option>
        <option v-for="type in filters.types" :key="type.value" :value="type.value">
          {{ type.label }} ({{ type.count }})
        </option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Category</label>
      <select
        :model-value="selectedFilters.category"
        @update:model-value="updateFilter('category', $event)"
        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
      >
        <option value="">All</option>
        <option v-for="cat in filters.categories" :key="cat.value" :value="cat.value">
          {{ cat.label }} ({{ cat.count }})
        </option>
      </select>
    </div>
    <div>
      <label class="block text-xs text-gray-600 mb-1">Sub Category</label>
      <select
        :model-value="selectedFilters.sub_category"
        @update:model-value="updateFilter('sub_category', $event)"
        class="border border-gray-300 rounded-md px-2 py-1 text-sm"
      >
        <option value="">All</option>
        <option v-for="sub in filters.sub_categories" :key="sub.value" :value="sub.value">
          {{ sub.label }} ({{ sub.count }})
        </option>
      </select>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { apiFetch } from '../utils/api'

const props = defineProps({
  selectedFilters: {
    type: Object,
    required: true,
    default: () => ({
      assigned_agent_id: '',
      priority: '',
      status: '',
      type: '',
      category: '',
      sub_category: ''
    })
  }
})

const emit = defineEmits(['filter-changed'])

// Internal filters state
const filters = ref({
  assigned_agents: [],
  priorities: [],
  statuses: [],
  types: [],
  categories: [],
  sub_categories: []
})

// Fetch filter options from API
const fetchFilters = async () => {
  try {
    const response = await apiFetch('/api/tickets/filters')
    if (response.status === 'success' && response.data) {
      filters.value = response.data
    }
  } catch (err) {
    console.log('Error fetching filters:', err)
  }
}

const updateFilter = (filterKey, value) => {
  emit('filter-changed', filterKey, value)
}

// Load filters on component mount
onMounted(async () => {
  await fetchFilters()
})
</script>
