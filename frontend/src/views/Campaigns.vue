<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Campagnes Marketing</h1>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
        + Nouvelle Campagne
      </button>
    </div>

    <!-- Campaigns List -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="cam in campaigns" :key="cam.id" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden group">
        <div class="p-6">
           <div class="flex items-center justify-between mb-4">
              <span :class="typeClass(cam.type)" class="px-2 py-1 text-[10px] font-bold rounded uppercase tracking-widest">{{ cam.type }}</span>
              <span :class="statusClass(cam.status)" class="px-2 py-1 text-xs font-semibold rounded-full lowercase">{{ cam.status }}</span>
           </div>
           <h3 class="text-lg font-bold text-gray-900 mb-2 truncate group-hover:text-indigo-600 transition">{{ cam.name }}</h3>
           <p class="text-sm text-gray-500 mb-6 font-primary line-clamp-2">{{ cam.subject || 'Aucun objet défini' }}</p>
           
           <!-- Metrics -->
           <div class="grid grid-cols-3 gap-2 border-t pt-4">
              <div class="text-center">
                 <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Envois</p>
                 <p class="font-bold text-gray-800">{{ cam.recipients_count || 0 }}</p>
              </div>
              <div class="text-center border-l border-r border-gray-50 px-2">
                 <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Ouvertures</p>
                 <p class="font-bold text-gray-800">{{ cam.opens_count || 0 }}</p>
              </div>
              <div class="text-center">
                 <p class="text-[10px] text-gray-400 font-bold uppercase mb-1">Clics</p>
                 <p class="font-bold text-blue-600">{{ cam.clicks_count || 0 }}</p>
              </div>
           </div>
        </div>
        <div class="bg-gray-50 px-6 py-3 flex items-center justify-between border-t border-gray-100">
           <span class="text-[10px] text-gray-400 font-medium">Créée par {{ cam.user?.name || 'Inconnu' }}</span>
           <div class="space-x-4 flex items-center">
              <button @click="sendCampaign(cam)" v-if="cam.status !== 'sent'" :disabled="submitting" class="text-sm text-green-600 font-bold hover:underline disabled:opacity-50">Lancer l'envoi</button>
              <button @click="openEditModal(cam)" class="text-sm text-indigo-600 font-semibold hover:underline">Gérer</button>
              <button @click="deleteCampaign(cam.id)" class="text-sm text-red-500 font-semibold hover:underline">Supprimer</button>
           </div>
        </div>
      </div>
      
      <div v-if="campaigns.length === 0" class="col-span-full py-20 bg-white border-2 border-dashed border-gray-200 rounded-xl text-center text-gray-400">
         Aucune campagne pour le moment.
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 px-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl">
        <div class="p-6">
           <h3 class="text-xl font-bold text-gray-900 mb-6 font-primary">{{ editingId ? 'Paramètres Campagne' : 'Créer une Campagne' }}</h3>
           <form @submit.prevent="submitForm" class="space-y-4">
              <div class="grid grid-cols-2 gap-4">
                 <div class="col-span-2">
                    <label class="block text-sm font-bold text-gray-700">Nom Interne *</label>
                    <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-3 border">
                 </div>
                 <div>
                    <label class="block text-sm font-bold text-gray-700">Canal *</label>
                    <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-3 border">
                       <option value="email">E-mail</option>
                       <option value="sms">SMS</option>
                       <option value="newsletter">Newsletter</option>
                    </select>
                 </div>
                 <div>
                    <label class="block text-sm font-bold text-gray-700">Statut</label>
                    <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-3 border">
                       <option value="draft">Brouillon</option>
                       <option value="scheduled">Planifié</option>
                       <option value="sent">Envoyé</option>
                       <option value="cancelled">Annulé</option>
                    </select>
                 </div>
              </div>
              <div>
                 <label class="block text-sm font-bold text-gray-700">Objet de l'email</label>
                 <input v-model="form.subject" type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-3 border">
              </div>
              <div>
                 <label class="block text-sm font-bold text-gray-700">Corps du message (Contenu HTML)</label>
                 <textarea v-model="form.body" rows="6" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm sm:text-sm p-3 border font-mono text-xs"></textarea>
              </div>
              <div class="flex items-center justify-end space-x-3 pt-6 border-t font-primary">
                 <button type="button" @click="showModal = false" class="px-6 py-2 border border-gray-300 rounded-md text-sm font-bold text-gray-700 hover:bg-gray-50 uppercase tracking-tighter transition">Annuler</button>
                 <button type="submit" :disabled="submitting" class="px-6 py-2 bg-indigo-600 text-white rounded-md text-sm font-bold hover:bg-indigo-700 disabled:opacity-50 uppercase tracking-tighter transition shadow-indigo-200 shadow-lg">
                    {{ submitting ? 'Envoi...' : (editingId ? 'Mettre à Jour' : 'Lancer') }}
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

const campaigns = ref([])
const showModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)

const emptyForm = () => ({ name: '', type: 'email', subject: '', body: '', status: 'draft' })
const form = ref(emptyForm())

const fetchAll = async () => {
  try {
    const res = await api.get('/campaigns')
    campaigns.value = res.data.data || res.data
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

const openEditModal = (cam) => {
  editingId.value = cam.id
  form.value = { ...cam }
  showModal.value = true
}

const submitForm = async () => {
  submitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/campaigns/${editingId.value}`, form.value)
    } else {
      await api.post('/campaigns', form.value)
    }
    showModal.value = false
    fetchAll()
  } catch (e) {
    alert("Erreur")
  } finally {
    submitting.value = false
  }
}

const deleteCampaign = async (id) => {
  if (!confirm("Effacer cette campagne ?")) return
  try {
    await api.delete(`/campaigns/${id}`)
    fetchAll()
  } catch (e) { console.error(e) }
}

const sendCampaign = async (cam) => {
  if (!confirm(`Voulez-vous vraiment envoyer cette campagne à tous vos contacts (${cam.name}) ?`)) return
  submitting.value = true
  try {
    const res = await api.post(`/campaigns/${cam.id}/send`)
    alert(res.data.message)
    fetchAll()
  } catch (e) {
    alert("Erreur lors de l'envoi. Vérifiez la configuration SMTP.")
    console.error(e)
  } finally {
    submitting.value = false
  }
}

const typeClass = (t) => {
  const map = {
    email: 'bg-indigo-100 text-indigo-800',
    sms: 'bg-teal-100 text-teal-800',
    newsletter: 'bg-purple-100 text-purple-800'
  }
  return map[t] || 'bg-gray-100 text-gray-800'
}

const statusClass = (s) => {
  const map = {
    draft: 'bg-gray-100 text-gray-500',
    scheduled: 'bg-blue-100 text-blue-600',
    sent: 'bg-green-100 text-green-700',
    cancelled: 'bg-red-100 text-red-700 font-bold'
  }
  return map[s] || 'bg-gray-100 text-gray-800'
}
</script>
