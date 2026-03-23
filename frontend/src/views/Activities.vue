<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Activités</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Nouvelle Activité</button>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Type</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Description</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Date</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Responsable</th>
            <th class="relative py-3.5 text-right pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="5" class="py-10 text-center text-gray-400 text-sm">Chargement...</td>
          </tr>
          <tr v-for="act in activities" :key="act.id" class="hover:bg-gray-50 transition">
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
              <span :class="typeClass(act.type)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-bold uppercase tracking-wide">{{ act.type }}</span>
            </td>
            <td class="px-3 py-4 text-sm text-gray-700 max-w-xs truncate">{{ act.description }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ new Date(act.date).toLocaleString('fr-FR') }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ act.user?.name || '-' }}</td>
            <td class="relative whitespace-nowrap py-4 pr-6 text-right text-sm font-medium space-x-3">
              <button @click="openEdit(act)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="confirmDelete(act)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="!loading && activities.length === 0">
            <td colspan="5" class="py-10 text-center text-gray-500">Aucune activité trouvée.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2">
      <button v-for="p in pagination.last_page" :key="p" @click="currentPage = p; fetchActivities()"
        :class="['px-3 py-1 rounded text-sm border', p === pagination.current_page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
        {{ p }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md max-h-screen overflow-y-auto">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingId ? 'Modifier l\'Activité' : 'Nouvelle Activité' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Type *</label>
            <select v-model="form.type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="call">Appel</option>
              <option value="email">Email</option>
              <option value="meeting">Réunion</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Description *</label>
            <textarea v-model="form.description" required rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border"></textarea>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Date *</label>
            <input v-model="form.date" type="datetime-local" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Responsable *</label>
            <select v-model="form.user_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">-- Choisir un utilisateur --</option>
              <option v-for="u in usersList" :key="u.id" :value="u.id">{{ u.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Lead associé (optionnel)</label>
            <select v-model="form.lead_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">-- Aucun --</option>
              <option v-for="l in leadsList" :key="l.id" :value="l.id">{{ l.name }} {{ l.company ? '(' + l.company + ')' : '' }}</option>
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
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer l'Activité</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer cette activité ? Cette action est irréversible.</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteActivity" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
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

const activities = ref([])
const usersList = ref([])
const leadsList = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)
const deletingActivity = ref(null)
const currentPage = ref(1)
const pagination = ref({ last_page: 1, current_page: 1 })

const typeClass = (type) => {
  const map = { call: 'bg-blue-100 text-blue-800', email: 'bg-green-100 text-green-800', meeting: 'bg-purple-100 text-purple-800' }
  return map[type] || 'bg-gray-100 text-gray-700'
}

const emptyForm = () => ({ type: 'call', description: '', date: '', user_id: '', lead_id: '', client_id: '' })
const form = ref(emptyForm())

onMounted(async () => {
  try {
    const [usersRes, leadsRes] = await Promise.all([
      api.get('/users'),
      api.get('/leads')
    ])
    usersList.value = usersRes.data.data || usersRes.data
    leadsList.value = leadsRes.data.data || leadsRes.data
  } catch (e) { console.error(e) }
  fetchActivities()
})

async function fetchActivities() {
  loading.value = true
  try {
    const res = await api.get('/activities', { params: { page: currentPage.value } })
    activities.value = res.data.data || res.data
    if (res.data.meta) pagination.value = res.data.meta
    else if (res.data.last_page) pagination.value = res.data
  } catch (e) { console.error(e) } finally { loading.value = false }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(act) {
  editingId.value = act.id
  // Format date for datetime-local input
  const d = new Date(act.date)
  const formatted = d.toISOString().slice(0, 16)
  form.value = {
    type: act.type, description: act.description,
    date: formatted, user_id: act.user_id || '',
    lead_id: act.lead_id || '', client_id: act.client_id || ''
  }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.lead_id) delete payload.lead_id
    if (!payload.client_id) delete payload.client_id
    if (editingId.value) {
      await api.put(`/activities/${editingId.value}`, payload)
    } else {
      await api.post('/activities', payload)
    }
    closeModal()
    fetchActivities()
  } catch (e) {
    console.error(e)
    alert('Une erreur est survenue.')
  } finally { submitting.value = false }
}

function confirmDelete(act) {
  deletingActivity.value = act
  showDeleteModal.value = true
}

async function deleteActivity() {
  submitting.value = true
  try {
    await api.delete(`/activities/${deletingActivity.value.id}`)
    showDeleteModal.value = false
    deletingActivity.value = null
    fetchActivities()
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}
</script>
