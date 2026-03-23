<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Tâches</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Nouvelle Tâche</button>
    </div>

    <!-- Status filter -->
    <div class="flex gap-2 flex-wrap">
      <button
        v-for="s in statusOptions" :key="s.value"
        @click="filterStatus = s.value; fetchTasks()"
        :class="['px-3 py-1 rounded-full text-xs font-semibold border transition', filterStatus === s.value ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400']"
      >{{ s.label }}</button>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Titre</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Échéance</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Priorité</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Statut</th>
            <th class="relative py-3.5 text-right pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="5" class="py-10 text-center text-gray-400 text-sm">Chargement...</td>
          </tr>
          <tr v-for="task in tasks" :key="task.id" class="hover:bg-gray-50 transition">
            <td class="py-4 pl-4 pr-3 text-sm">
              <div class="font-medium text-gray-900">{{ task.title }}</div>
              <div class="text-gray-500 text-xs mt-0.5">{{ task.description }}</div>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ task.due_date ? new Date(task.due_date).toLocaleDateString('fr-FR') : '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
              <span :class="priorityClass(task.priority)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ task.priority || '-' }}</span>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
              <span :class="statusClass(task.status)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ task.status }}</span>
            </td>
            <td class="relative whitespace-nowrap py-4 pr-6 text-right text-sm font-medium space-x-3">
              <button @click="openEdit(task)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="confirmDelete(task)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="!loading && tasks.length === 0">
            <td colspan="5" class="py-10 text-center text-gray-500">Aucune tâche trouvée.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2">
      <button v-for="p in pagination.last_page" :key="p" @click="currentPage = p; fetchTasks()"
        :class="['px-3 py-1 rounded text-sm border', p === pagination.current_page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
        {{ p }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md max-h-screen overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingId ? 'Modifier la Tâche' : 'Nouvelle Tâche' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Titre *</label>
            <input v-model="form.title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Échéance</label>
              <input v-model="form.due_date" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Priorité</label>
              <select v-model="form.priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                <option value="low">Basse</option>
                <option value="medium">Moyenne</option>
                <option value="high">Haute</option>
              </select>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Statut</label>
            <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="pending">En attente</option>
              <option value="in_progress">En cours</option>
              <option value="completed">Terminé</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Assigner à *</label>
            <select v-model="form.user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">-- Choisir un utilisateur --</option>
              <option v-for="u in usersList" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
            <button type="submit" :disabled="submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
              {{ submitting ? 'Enregistrement...' : (editingId ? 'Mettre à jour' : 'Créer') }}
            </button>
            <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete confirm -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer la Tâche</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ deletingTask?.title }}</strong> ?</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteTask" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
            {{ submitting ? 'Suppression...' : 'Supprimer' }}
          </button>
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">Annuler</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/axios'

const tasks = ref([])
const usersList = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)
const deletingTask = ref(null)
const currentPage = ref(1)
const filterStatus = ref('')
const pagination = ref({ last_page: 1, current_page: 1 })

const statusOptions = [
  { value: '', label: 'Toutes' },
  { value: 'pending', label: 'En attente' },
  { value: 'in_progress', label: 'En cours' },
  { value: 'completed', label: 'Terminées' },
]

const emptyForm = () => ({ title: '', description: '', due_date: '', priority: 'medium', status: 'pending', user_id: '' })
const form = ref(emptyForm())

const priorityClass = (p) => {
  const map = { low: 'bg-gray-100 text-gray-700', medium: 'bg-yellow-100 text-yellow-800', high: 'bg-red-100 text-red-800' }
  return map[p] || 'bg-gray-100 text-gray-700'
}
const statusClass = (s) => {
  const map = { pending: 'bg-orange-100 text-orange-800', in_progress: 'bg-blue-100 text-blue-800', completed: 'bg-green-100 text-green-800' }
  return map[s] || 'bg-gray-100 text-gray-700'
}

onMounted(async () => {
  try {
    const res = await api.get('/users')
    usersList.value = res.data.data || res.data
  } catch (e) { console.error(e) }
  fetchTasks()
})

async function fetchTasks() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (filterStatus.value) params.status = filterStatus.value
    const res = await api.get('/tasks', { params })
    tasks.value = res.data.data || res.data
    if (res.data.meta) pagination.value = res.data.meta
    else if (res.data.last_page) pagination.value = res.data
  } catch (e) { console.error(e) } finally { loading.value = false }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(task) {
  editingId.value = task.id
  form.value = {
    title: task.title, description: task.description || '',
    due_date: task.due_date ? task.due_date.substring(0, 10) : '',
    priority: task.priority || 'medium', status: task.status || 'pending',
    user_id: task.user_id || ''
  }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.due_date) delete payload.due_date
    if (editingId.value) {
      await api.put(`/tasks/${editingId.value}`, payload)
    } else {
      await api.post('/tasks', payload)
    }
    closeModal()
    fetchTasks()
  } catch (e) {
    console.error(e)
    alert('Une erreur est survenue.')
  } finally { submitting.value = false }
}

function confirmDelete(task) {
  deletingTask.value = task
  showDeleteModal.value = true
}

async function deleteTask() {
  submitting.value = true
  try {
    await api.delete(`/tasks/${deletingTask.value.id}`)
    showDeleteModal.value = false
    deletingTask.value = null
    fetchTasks()
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}
</script>
