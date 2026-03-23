<template>
  <div class="space-y-6 relative">
    <div class="flex justify-between items-center">
      <h1 class="text-2xl font-bold text-gray-900">Utilisateurs & Accès</h1>
      <button @click="openCreate" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Inviter Utilisateur</button>
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900">Utilisateur</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Email</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Rôle</th>
            <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Créé le</th>
            <th class="relative py-3.5 text-right pr-6"><span class="sr-only">Actions</span></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="5" class="py-10 text-center text-gray-400 text-sm">Chargement...</td>
          </tr>
          <tr v-for="user in users" :key="user.id" class="hover:bg-gray-50 transition">
            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm">
              <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs flex-shrink-0">
                  {{ user.name.charAt(0).toUpperCase() }}
                </div>
                <span class="font-medium text-gray-900">{{ user.name }}</span>
              </div>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ user.email }}</td>
            <td class="whitespace-nowrap px-3 py-4 text-sm">
              <span v-for="role in user.roles" :key="role.id" class="mr-1 inline-flex rounded-full bg-purple-100 px-2.5 py-0.5 text-xs font-semibold text-purple-800">{{ role.name }}</span>
              <span v-if="!user.roles || user.roles.length === 0" class="text-gray-400 text-xs">-</span>
            </td>
            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ new Date(user.created_at).toLocaleDateString('fr-FR') }}</td>
            <td class="relative whitespace-nowrap py-4 pr-6 text-right text-sm font-medium space-x-3">
              <button @click="openEdit(user)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="confirmDelete(user)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="!loading && users.length === 0">
            <td colspan="5" class="py-10 text-center text-gray-500">Aucun utilisateur trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-900 mb-4">{{ editingId ? 'Modifier l\'Utilisateur' : 'Nouvel Utilisateur' }}</h3>
        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Nom complet *</label>
            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Email *</label>
            <input v-model="form.email" type="email" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">{{ editingId ? 'Nouveau mot de passe (laisser vide pour ne pas changer)' : 'Mot de passe *' }}</label>
            <input v-model="form.password" type="password" :required="!editingId" :placeholder="editingId ? '••••••••' : ''" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Rôle</label>
            <select v-model="form.role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">Aucun rôle</option>
              <option value="admin">Admin</option>
              <option value="manager">Manager</option>
              <option value="agent">Agent</option>
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
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer l'Utilisateur</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ deletingUser?.name }}</strong> ? Cette action est irréversible.</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteUser" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
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

const users = ref([])
const loading = ref(true)
const showModal = ref(false)
const showDeleteModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)
const deletingUser = ref(null)

const emptyForm = () => ({ name: '', email: '', password: '', role: '' })
const form = ref(emptyForm())

onMounted(fetchUsers)

async function fetchUsers() {
  loading.value = true
  try {
    const res = await api.get('/users')
    users.value = res.data.data || res.data
  } catch (e) { console.error(e) } finally { loading.value = false }
}

function openCreate() {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

function openEdit(user) {
  editingId.value = user.id
  form.value = {
    name: user.name, email: user.email, password: '',
    role: user.roles && user.roles.length ? user.roles[0].name : ''
  }
  showModal.value = true
}

function closeModal() { showModal.value = false }

async function submitForm() {
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.password) delete payload.password
    if (!payload.role) delete payload.role
    if (editingId.value) {
      await api.put(`/users/${editingId.value}`, payload)
    } else {
      await api.post('/users', payload)
    }
    closeModal()
    fetchUsers()
  } catch (e) {
    console.error(e)
    alert('Une erreur est survenue.')
  } finally { submitting.value = false }
}

function confirmDelete(user) {
  deletingUser.value = user
  showDeleteModal.value = true
}

async function deleteUser() {
  submitting.value = true
  try {
    await api.delete(`/users/${deletingUser.value.id}`)
    showDeleteModal.value = false
    deletingUser.value = null
    fetchUsers()
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}
</script>
