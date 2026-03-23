<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Leads</h1>
      <div class="flex items-center space-x-2">
        <button @click="exportCSV" class="bg-white text-gray-700 px-4 py-2 rounded-md shadow-sm border border-gray-300 hover:bg-gray-50 transition text-sm font-medium flex items-center">
          <span class="mr-1">📤</span> Export
        </button>
        <button @click="$refs.fileInput.click()" class="bg-white text-gray-700 px-4 py-2 rounded-md shadow-sm border border-gray-300 hover:bg-gray-50 transition text-sm font-medium flex items-center">
          <span class="mr-1">📥</span> Import
        </button>
        <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="importCSV">
        <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Nouveau Lead</button>
      </div>
    </div>

    <!-- Status filter -->
    <div class="flex gap-2 flex-wrap">
      <button
        v-for="s in statusOptions" :key="s.value"
        @click="filterStatus = s.value; fetchLeads()"
        :class="['px-3 py-1 rounded-full text-xs font-semibold border transition', filterStatus === s.value ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400']"
      >{{ s.label }}</button>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Nom</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Société</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Score</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Statut</th>
            <th class="relative py-3.5 text-right pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="5" class="py-10 text-center text-gray-400 text-sm">Chargement...</td>
          </tr>
          <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50 transition">
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900">{{ lead.name }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ lead.email || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ lead.company || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
              <div class="flex items-center">
                 <div class="w-16 bg-gray-200 rounded-full h-1.5 mr-2">
                    <div class="bg-indigo-600 h-1.5 rounded-full" :style="{ width: (lead.score || 0) + '%' }"></div>
                 </div>
                 <span class="text-xs font-bold text-gray-700">{{ lead.score || 0 }}</span>
              </div>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
              <span :class="statusClass(lead.status)" class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold">{{ lead.status }}</span>
            </td>
            <td class="relative whitespace-nowrap py-4 pr-6 text-right text-sm font-medium space-x-3">
              <button @click="openEdit(lead)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="confirmDelete(lead)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="!loading && leads.length === 0">
            <td colspan="5" class="py-10 text-center text-gray-500">Aucun lead trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2">
      <button v-for="p in pagination.last_page" :key="p" @click="currentPage = p; fetchLeads()"
        :class="['px-3 py-1 rounded text-sm border', p === pagination.current_page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
        {{ p }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingId ? 'Modifier le Lead' : 'Nouveau Lead' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom *</label>
            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Email</label>
              <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Téléphone</label>
              <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Société</label>
            <input v-model="form.company" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Statut</label>
            <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option v-for="s in statusOptions.filter(x => x.value)" :key="s.value" :value="s.value">{{ s.label }}</option>
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
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer le Lead</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ deletingLead?.name }}</strong> ? Cette action est irréversible.</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteLead" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
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

const leads = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)
const deletingLead = ref(null)
const currentPage = ref(1)
const filterStatus = ref('')
const pagination = ref({ last_page: 1, current_page: 1 })

const statusOptions = [
  { value: '', label: 'Tous' },
  { value: 'new', label: 'Nouveau' },
  { value: 'contacted', label: 'Contacté' },
  { value: 'qualified', label: 'Qualifié' },
  { value: 'won', label: 'Gagné' },
  { value: 'lost', label: 'Perdu' },
]

const emptyForm = () => ({ name: '', email: '', phone: '', company: '', status: 'new' })
const form = ref(emptyForm())

const statusClass = (status) => {
  const map = {
    new: 'bg-blue-100 text-blue-800',
    contacted: 'bg-orange-100 text-orange-800',
    qualified: 'bg-purple-100 text-purple-800',
    won: 'bg-green-100 text-green-800',
    lost: 'bg-red-100 text-red-800',
  }
  return map[status] || 'bg-gray-100 text-gray-800'
}

onMounted(fetchLeads)

async function fetchLeads() {
  loading.value = true
  try {
    const params = { page: currentPage.value }
    if (filterStatus.value) params.status = filterStatus.value
    const res = await api.get('/leads', { params })
    leads.value = res.data.data || res.data
    if (res.data.meta) pagination.value = res.data.meta
    else if (res.data.last_page) pagination.value = res.data
  } catch (e) { console.error(e) } finally { loading.value = false }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(lead) {
  editingId.value = lead.id
  form.value = { name: lead.name, email: lead.email || '', phone: lead.phone || '', company: lead.company || '', status: lead.status || 'new' }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  submitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/leads/${editingId.value}`, form.value)
    } else {
      await api.post('/leads', form.value)
    }
    closeModal()
    fetchLeads()
  } catch (e) {
    console.error(e)
    alert('Une erreur est survenue.')
  } finally { submitting.value = false }
}

function confirmDelete(lead) {
  deletingLead.value = lead
  showDeleteModal.value = true
}

async function deleteLead() {
  submitting.value = true
  try {
    await api.delete(`/leads/${deletingLead.value.id}`)
    showDeleteModal.value = false
    deletingLead.value = null
    fetchLeads()
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}

async function exportCSV() {
  try {
    const res = await api.get('/leads/export', { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'leads.csv')
    document.body.appendChild(link)
    link.click()
  } catch (e) { console.error(e) }
}

async function importCSV(event) {
  const file = event.target.files[0]
  if (!file) return
  const formData = new FormData()
  formData.append('file', file)
  try {
    const res = await api.post('/leads/import', formData)
    alert(`Importation réussie : ${res.data.imported} leads importés.`)
    fetchLeads()
  } catch (e) { alert("Erreur d'importation") }
  event.target.value = ''
}
</script>
