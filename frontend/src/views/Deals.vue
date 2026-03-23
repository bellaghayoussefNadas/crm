<template>
  <div class="h-full flex flex-col">
    <div class="mb-4 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Pipeline de Vente (Deals)</h1>
        <p class="text-sm text-gray-500">Gérez vos opportunités en glisser-déposer (Kanban)</p>
      </div>
      <button @click="showModal = true" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium shadow transition">
        + Nouveau Deal
      </button>
    </div>
    
    <div class="flex-1 flex space-x-6 pb-4 overflow-x-auto min-h-[70vh]">
      <div 
        v-for="stage in stages" 
        :key="stage.id" 
        class="w-80 flex-shrink-0 flex flex-col bg-gray-100/80 rounded-xl p-4 shadow-sm border border-gray-200"
        @dragover.prevent 
        @drop="drop($event, stage.id)"
      >
        <div class="flex justify-between items-center mb-4">
          <h3 class="font-bold text-gray-700" :class="stage.textColor">{{ stage.name }}</h3>
          <span class="bg-white text-gray-500 text-xs font-bold px-2 py-1 rounded-full shadow-sm">{{ getDealsByStage(stage.id).length }}</span>
        </div>
        
        <div class="flex-1 overflow-y-auto space-y-3 pr-1 pb-2">
          <div
            v-for="deal in getDealsByStage(stage.id)"
            :key="deal.id"
            draggable="true"
            @dragstart="dragStart($event, deal)"
            class="bg-white p-4 rounded-lg shadow-sm cursor-grab active:cursor-grabbing hover:shadow-md border border-gray-100 hover:border-indigo-300 transition-all group"
          >
            <div class="font-bold text-gray-800">{{ deal.name }}</div>
            <div class="text-xs text-gray-500 mt-1 uppercase font-semibold" v-if="deal.account">{{ deal.account.name }}</div>
            <div class="mt-4 flex justify-between items-center text-sm">
              <span class="font-bold text-green-600">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: deal.currency || 'EUR' }).format(deal.amount) }}</span>
              <span class="bg-indigo-50 text-indigo-700 border border-indigo-100 px-2.5 py-0.5 rounded text-xs font-semibold">{{ deal.probability }}%</span>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-end gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
              <button @click.stop="openEdit(deal)" class="text-xs text-indigo-600 hover:text-indigo-900 font-medium">Modifier</button>
              <button @click.stop="confirmDelete(deal)" class="text-xs text-red-500 hover:text-red-700 font-medium">Supprimer</button>
            </div>
          </div>
          
          <div v-if="getDealsByStage(stage.id).length === 0" class="text-center text-gray-400 text-sm py-4 italic border-2 border-dashed border-gray-200 rounded-lg">
            Glissez un deal ici
          </div>
        </div>
      </div>
    </div>

    <!-- Modal d'ajout de Deal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 transition-opacity">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md transform transition-all">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Nouvelle Opportunité</h3>
        <form @submit.prevent="createDeal" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Titre *</label>
            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Montant</label>
              <input v-model="form.amount" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Probabilité (%)</label>
              <input v-model="form.probability" type="number" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Étape initiale *</label>
            <select v-model="form.stage" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Compte (Entreprise)</label>
            <select v-model="form.account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">-- Aucun --</option>
              <option v-for="acc in accountsList" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Contact</label>
            <select v-model="form.contact_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option value="">-- Aucun --</option>
              <option v-for="c in contactsList" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
            </select>
          </div>
          <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
            <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" :disabled="submitting">
              {{ submitting ? 'Création...' : 'Créer' }}
            </button>
            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
              Annuler
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

    <!-- Edit Deal Modal -->
    <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-md">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Modifier le Deal</h3>
        <form @submit.prevent="updateDeal" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700">Titre *</label>
            <input v-model="editForm.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Montant</label>
              <input v-model="editForm.amount" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Probabilité (%)</label>
              <input v-model="editForm.probability" type="number" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700">Étape</label>
            <select v-model="editForm.stage" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm px-3 py-2 border">
              <option v-for="stage in stages" :key="stage.id" :value="stage.id">{{ stage.name }}</option>
            </select>
          </div>
          <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
            <button type="submit" :disabled="submitting" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50">
              {{ submitting ? 'Enregistrement...' : 'Mettre à jour' }}
            </button>
            <button type="button" @click="showEditModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">Annuler</button>
          </div>
        </form>
      </div>
    </div>

    <!-- Delete Deal Modal -->
    <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50">
      <div class="bg-white rounded-lg p-6 shadow-xl w-full max-w-sm">
        <h3 class="text-lg font-bold text-gray-900 mb-2">Supprimer le Deal</h3>
        <p class="text-sm text-gray-500 mb-6">Êtes-vous sûr de vouloir supprimer <strong>{{ deletingDeal?.name }}</strong> ? Cette action est irréversible.</p>
        <div class="flex flex-row-reverse gap-3">
          <button @click="deleteDeal" :disabled="submitting" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50">
            {{ submitting ? 'Suppression...' : 'Supprimer' }}
          </button>
          <button @click="showDeleteModal = false" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm font-medium hover:bg-gray-50">Annuler</button>
        </div>
      </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../utils/axios'

const showModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const deletingDeal = ref(null)
const editingDeal = ref(null)
const editForm = ref({ name: '', amount: 0, stage: 'lead', probability: 10 })
const submitting = ref(false)
const form = ref({
  name: '',
  amount: 0,
  stage: 'lead',
  probability: 10,
  account_id: '',
  contact_id: ''
})

const stages = ref([
  { id: 'lead', name: 'Nouveau Lead', textColor: 'text-blue-600' },
  { id: 'contacted', name: 'Contacté', textColor: 'text-orange-500' },
  { id: 'negotiation', name: 'En Négociation', textColor: 'text-purple-600' },
  { id: 'won', name: 'Gagné', textColor: 'text-green-600' },
  { id: 'lost', name: 'Perdu', textColor: 'text-red-600' }
])

const deals = ref([])
const accountsList = ref([])
const contactsList = ref([])

onMounted(async () => {
  try {
    const [dealsRes, accountsRes, contactsRes] = await Promise.all([
      api.get('/deals'),
      api.get('/accounts'),
      api.get('/contacts')
    ])
    deals.value = dealsRes.data
    accountsList.value = accountsRes.data.data || accountsRes.data
    contactsList.value = contactsRes.data.data || contactsRes.data
  } catch (error) {
    console.error("Erreur chargement:", error)
  }
})

const getDealsByStage = (stageId) => {
  return deals.value.filter(deal => deal.stage === stageId)
}

const dragStart = (event, deal) => {
  event.dataTransfer.setData('dealId', deal.id)
  event.dataTransfer.effectAllowed = 'move'
}

const drop = async (event, stageId) => {
  const dealId = parseInt(event.dataTransfer.getData('dealId'))
  const deal = deals.value.find(d => d.id === dealId)
  
  if (deal && deal.stage !== stageId) {
    const oldStage = deal.stage
    deal.stage = stageId // UI Update optimiste
    
    try {
      await api.put(`/deals/${deal.id}`, { stage: stageId })
    } catch (error) {
      deal.stage = oldStage // Rollback sur erreur
      console.error("Erreur mise à jour deal:", error)
    }
  }
}

const createDeal = async () => {
  submitting.value = true
  try {
    const payload = { ...form.value }
    if (!payload.account_id) delete payload.account_id
    if (!payload.contact_id) delete payload.contact_id
    
    const res = await api.post('/deals', payload)
    deals.value.push(res.data) // Optimistic interface update
    showModal.value = false
    
    // reset form
    form.value = {
      name: '',
      amount: 0,
      stage: 'lead',
      probability: 10,
      account_id: '',
      contact_id: ''
    }
  } catch (error) {
    console.error("Erreur création:", error)
    alert("Une erreur est survenue.")
  } finally {
    submitting.value = false
  }
}

function openEdit(deal) {
  editingDeal.value = deal
  editForm.value = { name: deal.name, amount: deal.amount, stage: deal.stage, probability: deal.probability }
  showEditModal.value = true
}

async function updateDeal() {
  submitting.value = true
  try {
    const res = await api.put(`/deals/${editingDeal.value.id}`, editForm.value)
    const idx = deals.value.findIndex(d => d.id === editingDeal.value.id)
    if (idx !== -1) deals.value[idx] = { ...deals.value[idx], ...res.data }
    showEditModal.value = false
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la mise à jour.')
  } finally { submitting.value = false }
}

function confirmDelete(deal) {
  deletingDeal.value = deal
  showDeleteModal.value = true
}

async function deleteDeal() {
  submitting.value = true
  try {
    await api.delete(`/deals/${deletingDeal.value.id}`)
    deals.value = deals.value.filter(d => d.id !== deletingDeal.value.id)
    showDeleteModal.value = false
    deletingDeal.value = null
  } catch (e) {
    console.error(e)
    alert('Erreur lors de la suppression.')
  } finally { submitting.value = false }
}
</script>

<style scoped>
/* Scrollbar styling for kanban columns */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}
.overflow-y-auto::-webkit-scrollbar-track {
  background: transparent;
}
.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 4px;
}
</style>
