<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Support Client / Tickets</h1>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
        + Nouveau Ticket
      </button>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
         <div class="p-3 bg-red-100 rounded-full">🚩</div>
         <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Urgent</p>
            <p class="text-xl font-bold">{{ tickets.filter(t => t.priority === 'urgent' && t.status !== 'closed').length }}</p>
         </div>
      </div>
       <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
         <div class="p-3 bg-orange-100 rounded-full">🔥</div>
         <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Ouverts</p>
            <p class="text-xl font-bold">{{ tickets.filter(t => t.status === 'open').length }}</p>
         </div>
      </div>
       <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
         <div class="p-3 bg-blue-100 rounded-full">⚙️</div>
         <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">En cours</p>
            <p class="text-xl font-bold">{{ tickets.filter(t => t.status === 'in_progress').length }}</p>
         </div>
      </div>
       <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 flex items-center space-x-4">
         <div class="p-3 bg-green-100 rounded-full">✅</div>
         <div>
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Résolus</p>
            <p class="text-xl font-bold">{{ tickets.filter(t => t.status === 'resolved' || t.status === 'closed').length }}</p>
         </div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sujet</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priorité</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigné à</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200 text-sm">
          <tr v-for="ticket in tickets" :key="ticket.id" class="hover:bg-gray-50">
            <td class="px-6 py-4">
              <div class="font-bold text-gray-900 truncate max-w-xs">{{ ticket.subject }}</div>
              <div class="text-xs text-gray-500">{{ formatDate(ticket.created_at) }}</div>
            </td>
            <td class="px-6 py-4">
              <span :class="priorityClass(ticket.priority)" class="px-2 py-1 text-xs font-bold rounded uppercase tracking-tighter">
                {{ ticket.priority }}
              </span>
            </td>
            <td class="px-6 py-4">
               <div>{{ ticket.contact?.first_name }} {{ ticket.contact?.last_name || '-' }}</div>
               <div class="text-xs text-gray-400 capitalize">{{ ticket.category }}</div>
            </td>
            <td class="px-6 py-4 text-gray-600">{{ ticket.assigned_to_user?.name || 'Non-assigné' }}</td>
            <td class="px-6 py-4">
               <span :class="statusClass(ticket.status)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                 {{ translateStatus(ticket.status) }}
               </span>
            </td>
            <td class="px-6 py-4 text-right space-x-2">
              <button @click="openEditModal(ticket)" class="text-indigo-600 hover:text-indigo-900 font-medium">Répondre</button>
              <button @click="deleteTicket(ticket.id)" class="text-red-500 hover:text-red-700">Supprimer</button>
            </td>
          </tr>
          <tr v-if="tickets.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Aucun ticket trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 px-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-lg">
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">{{ editingId ? 'Gestion du Ticket' : 'Nouveau Ticket' }}</h3>
          <form @submit.prevent="submitForm" class="space-y-4">
             <div>
                <label class="block text-sm font-medium text-gray-700">Sujet *</label>
                <input v-model="form.subject" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
             </div>
             <div>
                <label class="block text-sm font-medium text-gray-700">Description du problème *</label>
                <textarea v-model="form.description" rows="4" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border"></textarea>
             </div>
             <div class="grid grid-cols-2 gap-4">
                <div>
                   <label class="block text-sm font-medium text-gray-700">Contact / Client</label>
                   <select v-model="form.contact_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                      <option value="">Anonyme</option>
                      <option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                   </select>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700">Assigné à</label>
                  <select v-model="form.assigned_to" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                     <option value="">Non-assigné</option>
                     <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                  </select>
                </div>
                <div>
                   <label class="block text-sm font-medium text-gray-700">Priorité</label>
                   <select v-model="form.priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                      <option value="low">Basse</option>
                      <option value="medium">Moyenne</option>
                      <option value="high">Haute</option>
                      <option value="urgent">Urgent</option>
                   </select>
                </div>
                <div>
                   <label class="block text-sm font-medium text-gray-700">Statut</label>
                   <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-2 border">
                      <option value="open">Ouvert</option>
                      <option value="in_progress">En cours</option>
                      <option value="resolved">Résolu</option>
                      <option value="closed">Fermé / Clôturé</option>
                   </select>
                </div>
             </div>
             <div class="flex items-center justify-end space-x-3 pt-4">
                <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
                <button type="submit" :disabled="submitting" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                   {{ submitting ? 'Chargement...' : (editingId ? 'Valider modifications' : 'Créer Ticket') }}
                </button>
             </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/axios'

const tickets = ref([])
const contacts = ref([])
const users = ref([])
const showModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)

const emptyForm = () => ({
  subject: '', description: '', contact_id: '', assigned_to: '',
  priority: 'medium', status: 'open', category: 'general'
})
const form = ref(emptyForm())

const fetchAll = async () => {
  try {
    const [tRes, cRes, uRes] = await Promise.all([
      api.get('/tickets'),
      api.get('/contacts'),
      api.get('/users')
    ])
    tickets.value = tRes.data.data || tRes.data
    contacts.value = cRes.data.data || cRes.data
    users.value = uRes.data.data || uRes.data
  } catch (e) {
    console.error(e)
  }
}

onMounted(fetchAll)

const openCreateModal = () => {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

const openEditModal = (ticket) => {
  editingId.value = ticket.id
  form.value = { ...ticket, assigned_to: ticket.assigned_to || '', contact_id: ticket.contact_id || '' }
  showModal.value = true
}

const submitForm = async () => {
  submitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/tickets/${editingId.value}`, form.value)
    } else {
      await api.post('/tickets', form.value)
    }
    showModal.value = false
    fetchAll()
  } catch (e) {
    alert("Erreur")
  } finally {
    submitting.value = false
  }
}

const deleteTicket = async (id) => {
  if (!confirm("Effacer ce ticket ?")) return
  try {
    await api.delete(`/tickets/${id}`)
    fetchAll()
  } catch (e) { console.error(e) }
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('fr-FR', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' })
}

const statusClass = (s) => {
  const map = {
    open: 'bg-orange-100 text-orange-800',
    in_progress: 'bg-blue-100 text-blue-800',
    resolved: 'bg-green-100 text-green-800',
    closed: 'bg-gray-200 text-gray-500'
  }
  return map[s] || 'bg-gray-100 text-gray-800'
}

const priorityClass = (p) => {
  const map = {
    low: 'bg-gray-100 text-gray-600',
    medium: 'bg-blue-100 text-blue-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-600 text-white'
  }
  return map[p] || 'bg-gray-100 text-gray-600'
}

const translateStatus = (s) => {
  const map = { open: 'Ouvert', in_progress: 'En cours', resolved: 'Résolu', closed: 'Fermé' }
  return map[s] || s
}
</script>
