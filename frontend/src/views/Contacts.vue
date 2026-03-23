<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Contacts</h1>
      <div class="flex items-center space-x-2">
        <button @click="exportCSV" class="bg-white text-gray-700 px-4 py-2 rounded-md shadow-sm border border-gray-300 hover:bg-gray-50 transition text-sm font-medium flex items-center">
          <span class="mr-1">📤</span> Export CSV
        </button>
        <button @click="$refs.fileInput.click()" class="bg-white text-gray-700 px-4 py-2 rounded-md shadow-sm border border-gray-300 hover:bg-gray-50 transition text-sm font-medium flex items-center">
          <span class="mr-1">📥</span> Import CSV
        </button>
        <input type="file" ref="fileInput" class="hidden" accept=".csv" @change="importCSV">
        <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Nouveau</button>
      </div>
    </div>
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nom Complet</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Téléphone</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Compte (Entreprise)</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Poste</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Tags</th>
            <th class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-if="loading">
            <td colspan="6" class="py-10 text-center text-gray-400 text-sm">Chargement...</td>
          </tr>
          <tr v-for="contact in contacts" :key="contact.id" class="hover:bg-gray-50 transition">
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">{{ contact.first_name }} {{ contact.last_name }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ contact.email || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ contact.phone || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
              <span v-if="contact.account" class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">{{ contact.account.name }}</span>
              <span v-else>-</span>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ contact.job_title || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
               <div class="flex flex-wrap gap-1">
                  <span v-for="tag in contact.tags" :key="tag.id" :style="{ backgroundColor: tag.color + '1A', color: tag.color, borderColor: tag.color + '33' }" class="px-2 py-0.5 rounded text-[10px] font-bold uppercase border">
                    {{ tag.name }}
                  </span>
                  <span v-if="!contact.tags?.length" class="text-gray-300">-</span>
               </div>
            </td>
            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-3">
              <button @click="openEdit(contact)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="confirmDelete(contact)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="contacts.length === 0 && !loading">
            <td colspan="6" class="py-10 text-center text-gray-500">Aucun contact trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2">
      <button v-for="p in pagination.last_page" :key="p" @click="currentPage = p; fetchContacts()"
        :class="['px-3 py-1 rounded text-sm border', p === pagination.current_page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
        {{ p }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md transform transition-all">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingId ? 'Modifier le Contact' : 'Nouveau Contact' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Prénom *</label>
              <input v-model="form.first_name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Nom *</label>
              <input v-model="form.last_name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input v-model="form.email" type="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Téléphone</label>
              <input v-model="form.phone" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Poste</label>
              <input v-model="form.job_title" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Compte (Entreprise)</label>
            <select v-model="form.account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">-- Aucun --</option>
              <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
            </select>
          </div>
          <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" :disabled="submitting">
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
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer le Contact</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ deletingContact?.first_name }} {{ deletingContact?.last_name }}</strong> ?</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteContact" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
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

const contacts = ref([])
const accounts = ref([]) // for dropdown
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)
const deletingContact = ref(null)
const currentPage = ref(1)
const pagination = ref({ last_page: 1, current_page: 1 })

const emptyForm = () => ({ first_name: '', last_name: '', email: '', phone: '', job_title: '', account_id: '' })
const form = ref(emptyForm())

onMounted(async () => {
  await Promise.all([fetchContacts(), fetchAccountsList()])
})

async function fetchContacts() {
  loading.value = true
  try {
    const res = await api.get('/contacts', { params: { page: currentPage.value } })
    contacts.value = res.data.data ? res.data.data : res.data
    if (res.data.meta) pagination.value = res.data.meta
    else if (res.data.last_page) pagination.value = res.data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function fetchAccountsList() {
  try {
    const res = await api.get('/accounts')
    accounts.value = res.data.data || res.data
  } catch (e) { console.error(e) }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(contact) {
  editingId.value = contact.id
  form.value = {
    first_name: contact.first_name, last_name: contact.last_name,
    email: contact.email || '', phone: contact.phone || '',
    job_title: contact.job_title || '', account_id: contact.account_id || ''
  }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.account_id) delete payload.account_id
    if (editingId.value) {
      await api.put(`/contacts/${editingId.value}`, payload)
    } else {
      await api.post('/contacts', payload)
    }
    closeModal()
    fetchContacts()
  } catch (error) {
    console.error(error)
    alert("Erreur lors de l'enregistrement")
  } finally {
    submitting.value = false
  }
}

function confirmDelete(contact) {
  deletingContact.value = contact
  showDeleteModal.value = true
}

async function deleteContact() {
  submitting.value = true
  try {
    await api.delete(`/contacts/${deletingContact.value.id}`)
    showDeleteModal.value = false
    deletingContact.value = null
    fetchContacts()
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}

async function exportCSV() {
  try {
    const res = await api.get('/contacts/export', { responseType: 'blob' })
    const url = window.URL.createObjectURL(new Blob([res.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', 'contacts.csv')
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
    const res = await api.post('/contacts/import', formData)
    alert(`Importation réussie : ${res.data.imported} contacts importés, ${res.data.skipped} doublons ignorés.`)
    fetchContacts()
  } catch (e) { alert("Erreur d'importation") }
  event.target.value = ''
}
</script>
