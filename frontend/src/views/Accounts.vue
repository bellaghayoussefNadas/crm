<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Comptes Entreprises</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Ajouter</button>
    </div>
    
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Nom</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Industrie</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Taille</th>
            <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Pays</th>
            <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 bg-white">
          <tr v-if="loading">
            <td colspan="5" class="py-10 text-center text-gray-400 text-sm">Chargement...</td>
          </tr>
          <tr v-for="account in accounts" :key="account.id" class="hover:bg-gray-50 transition">
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-bold text-indigo-600 hover:text-indigo-900 sm:pl-6 cursor-pointer" @click="$router.push(`/accounts/${account.id}`)">{{ account.name }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ account.industry || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ account.size || '-' }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ account.country || '-' }}</td>
            <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6 space-x-3">
              <router-link :to="`/accounts/${account.id}`" class="text-gray-500 hover:text-indigo-600">Profil</router-link>
              <button @click="openEdit(account)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="confirmDelete(account)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="accounts.length === 0 && !loading">
            <td colspan="5" class="py-10 text-center text-gray-500">Aucun compte trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="pagination.last_page > 1" class="flex justify-center gap-2">
      <button v-for="p in pagination.last_page" :key="p" @click="currentPage = p; fetchAccounts()"
        :class="['px-3 py-1 rounded text-sm border', p === pagination.current_page ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:bg-gray-50']">
        {{ p }}
      </button>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md transform transition-all">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingId ? 'Modifier le Compte' : 'Ajouter une structure (Compte)' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom de l'entreprise *</label>
            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Secteur / Industrie</label>
              <input v-model="form.industry" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Pays</label>
              <input v-model="form.country" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Taille</label>
              <select v-model="form.size" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
                <option value="">-</option>
                <option value="1-10">1-10</option>
                <option value="11-50">11-50</option>
                <option value="51-200">51-200</option>
                <option value="201-500">201-500</option>
                <option value="500+">500+</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Site web</label>
              <input v-model="form.website" type="url" placeholder="https://..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
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
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer le Compte</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ deletingAccount?.name }}</strong> ? Cette action est irréversible.</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteAccount" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
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

const accounts = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)
const deletingAccount = ref(null)
const currentPage = ref(1)
const pagination = ref({ last_page: 1, current_page: 1 })

const emptyForm = () => ({ name: '', industry: '', country: '', size: '', website: '' })
const form = ref(emptyForm())

onMounted(fetchAccounts)

async function fetchAccounts() {
  loading.value = true
  try {
    const res = await api.get('/accounts', { params: { page: currentPage.value } })
    accounts.value = res.data.data ? res.data.data : res.data
    if (res.data.meta) pagination.value = res.data.meta
    else if (res.data.last_page) pagination.value = res.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(account) {
  editingId.value = account.id
  form.value = { name: account.name, industry: account.industry || '', country: account.country || '', size: account.size || '', website: account.website || '' }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.website) delete payload.website
    if (editingId.value) {
      await api.put(`/accounts/${editingId.value}`, payload)
    } else {
      await api.post('/accounts', payload)
    }
    closeModal()
    fetchAccounts()
  } catch (error) {
    console.error("Erreur lors de l'enregistrement", error)
    alert("Une erreur est survenue.")
  } finally {
    submitting.value = false
  }
}

function confirmDelete(account) {
  deletingAccount.value = account
  showDeleteModal.value = true
}

async function deleteAccount() {
  submitting.value = true
  try {
    await api.delete(`/accounts/${deletingAccount.value.id}`)
    showDeleteModal.value = false
    deletingAccount.value = null
    fetchAccounts()
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}
</script>
