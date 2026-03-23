<template>
  <div v-if="loading" class="flex justify-center py-12">
    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
  </div>
  
  <div v-else-if="account" class="space-y-6">
    <!-- En-tête du Profil -->
    <div class="bg-white px-6 py-5 shadow sm:rounded-lg sm:p-6 flex flex-col sm:flex-row justify-between items-start sm:items-center">
      <div>
        <h1 class="text-3xl font-bold text-gray-900 flex items-center gap-3">
          🏢 {{ account.name }}
          <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-1 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-700/10">{{ account.industry || 'Industrie inconnue' }}</span>
        </h1>
        <div class="mt-2 text-sm text-gray-500 flex gap-4">
          <span v-if="account.website">🌐 <a :href="account.website" target="_blank" class="text-indigo-600 hover:underline">{{ account.website }}</a></span>
          <span v-if="account.country">📍 {{ account.country }}</span>
          <span v-if="account.size">👥 {{ account.size }} employés</span>
        </div>
      </div>
      <div class="flex space-x-4 bg-gray-50 p-3 rounded-xl border border-gray-100 shadow-inner">
         <div class="text-center px-4 border-r border-gray-200">
            <p class="text-xs text-gray-400 font-bold uppercase">Contacts</p>
            <p class="text-xl font-bold text-indigo-600">{{ account.contacts?.length || 0 }}</p>
         </div>
         <div class="text-center px-4">
            <p class="text-xs text-gray-400 font-bold uppercase">Pipe Value</p>
            <p class="text-xl font-bold text-green-600">{{ account.deals?.reduce((s,d) => s+parseFloat(d.amount),0)?.toFixed(0) || 0 }} €</p>
         </div>
      </div>
      <div class="mt-4 sm:mt-0 space-x-3 flex">
        <button @click="openEditModal" class="bg-white text-gray-700 px-4 py-2 border border-gray-300 rounded-md shadow-sm hover:bg-gray-50 transition text-sm font-medium">Modifier</button>
        <button @click="openDealModal" class="bg-indigo-600 text-white px-4 py-2 rounded-md shadow hover:bg-indigo-700 transition text-sm font-medium">+ Nouvelle Opportunité</button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      
      <!-- Colonne de Gauche : Contacts -->
      <div class="space-y-6 lg:col-span-1">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Contacts liés</h3>
            <button @click="openContactModal" class="text-indigo-600 text-sm hover:underline font-bold">+ Ajouter</button>
          </div>
          <ul class="divide-y divide-gray-200">
            <li v-for="contact in account.contacts" :key="contact.id" class="p-4 hover:bg-gray-50 transition">
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-bold text-gray-900">{{ contact.first_name }} {{ contact.last_name }}</p>
                  <p class="text-sm text-gray-500">{{ contact.job_title || 'Poste non défini' }}</p>
                </div>
                <div class="text-right text-sm">
                  <a :href="`mailto:${contact.email}`" class="text-indigo-600 truncate block hover:underline">{{ contact.email }}</a>
                  <p class="text-gray-500">{{ contact.phone }}</p>
                </div>
              </div>
            </li>
            <li v-if="!account.contacts || account.contacts.length === 0" class="p-4 text-sm text-gray-500 text-center italic">
              Aucun contact associé.
            </li>
          </ul>
        </div>
      </div>

      <!-- Colonne de Droite : Deals & Pipeline -->
      <div class="space-y-6 lg:col-span-2">
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
            <h3 class="text-lg leading-6 font-medium text-gray-900">Opportunités (Deals) en cours</h3>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Étape</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Probabilité</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="deal in account.deals" :key="deal.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ deal.name }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: deal.currency || 'EUR' }).format(deal.amount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800 uppercase">{{ deal.stage }}</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                      <div class="bg-green-500 h-2.5 rounded-full" :style="{ width: deal.probability + '%' }"></div>
                    </div>
                  </td>
                </tr>
                <tr v-if="!account.deals || account.deals.length === 0">
                  <td colspan="4" class="px-6 py-4 text-center text-sm text-gray-500 italic">Aucune opportunité rattachée.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Timeline Section (Historique des échanges) -->
        <div class="bg-white shadow sm:rounded-lg overflow-hidden">
          <div class="px-4 py-5 sm:px-6 border-b border-gray-200 flex justify-between items-center">
             <h3 class="text-lg leading-6 font-medium text-gray-900 italic">Historique des échanges</h3>
             <div class="flex items-center space-x-2">
               <button @click="syncEmails" :disabled="syncing" class="text-xs flex items-center gap-1 px-3 py-1.5 bg-indigo-50 border border-indigo-100 text-indigo-700 rounded-lg hover:bg-indigo-100 transition disabled:opacity-50">
                 <span v-if="syncing">⏳</span>
                 <span v-else>📧</span>
                 Synchroniser e-mails
               </button>
               <button @click="openActivityModal" class="text-gray-400 text-sm font-bold uppercase tracking-tighter hover:text-indigo-600 transition">+ Note / Activité</button>
             </div>
          </div>
          <div class="p-6">
             <div class="flow-root">
                <ul role="list" class="-mb-8">
                   <li v-for="(act, idx) in account.activities" :key="act.id">
                      <div class="relative pb-8">
                         <span v-if="idx !== account.activities.length - 1" class="absolute left-4 top-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                         <div class="relative flex space-x-3">
                             <div>
                                <span :class="activityIconClass(act.type)" class="h-8 w-8 rounded-full flex items-center justify-center ring-8 ring-white text-lg transition hover:scale-110">
                                  {{ activityIcon(act.type) }}
                                </span>
                             </div>
                             <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                <div class="w-full">
                                   <p class="text-sm font-bold text-gray-900 leading-tight truncate">{{ act.subject || (act.type === 'email' ? 'Réponse Email' : 'Activité') }}</p>
                                   <div class="text-[10px] text-gray-500 line-clamp-2 mt-1" v-html="act.description"></div>
                                </div>
                                <div class="whitespace-nowrap text-right text-[10px] text-gray-400 font-bold uppercase tracking-tighter">
                                   {{ new Date(act.date).toLocaleDateString() }}
                                </div>
                             </div>
                         </div>
                      </div>
                   </li>
                   <li v-if="!account.activities?.length" class="text-center text-gray-300 py-10 italic text-sm">Aucun historique d'échange.</li>
                </ul>
             </div>
          </div>
        </div>
      </div>
      
    </div>
  </div>
  
  <div v-else class="text-center py-12 text-gray-500 text-lg">
    Compte introuvable.
  </div>

  <!-- Modal Modifier Compte -->
  <div v-if="showEditModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
      <div class="p-6">
        <h3 class="text-xl font-black text-gray-900 mb-6">Modifier l'entreprise</h3>
        <form @submit.prevent="updateAccount" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div class="col-span-2">
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nom</label>
              <input v-model="accountForm.name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 transition outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Secteur</label>
              <input v-model="accountForm.industry" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 transition outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Pays</label>
              <input v-model="accountForm.country" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500 transition outline-none" />
            </div>
          </div>
          <div class="flex justify-end space-x-3 pt-6 border-t">
            <button type="button" @click="showEditModal = false" class="px-6 py-2 text-gray-500 hover:bg-gray-100 rounded-lg transition font-bold">Annuler</button>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 transition font-bold">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Ajouter Contact -->
  <div v-if="showContactModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden animate-in fade-in zoom-in duration-200">
      <div class="p-6">
        <h3 class="text-xl font-black text-gray-900 mb-6">Ajouter un contact</h3>
        <form @submit.prevent="addContact" class="space-y-4">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Prénom</label>
              <input v-model="contactForm.first_name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nom</label>
              <input v-model="contactForm.last_name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
            </div>
            <div class="col-span-2">
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Email</label>
              <input v-model="contactForm.email" type="email" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
            </div>
          </div>
          <div class="flex justify-end space-x-3 pt-6 border-t">
            <button type="button" @click="showContactModal = false" class="px-6 py-2 text-gray-500 font-bold">Annuler</button>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold">Ajouter</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Nouvelle Opportunité -->
  <div v-if="showDealModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
      <div class="p-6">
        <h3 class="text-xl font-black text-gray-900 mb-6">Nouvelle Opportunité</h3>
        <form @submit.prevent="createDeal" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Nom du deal</label>
            <input v-model="dealForm.name" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Montant Estimé</label>
              <input v-model="dealForm.amount" type="number" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Probabilité (%)</label>
              <input v-model="dealForm.probability" type="number" max="100" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
            </div>
          </div>
          <div class="flex justify-end space-x-3 pt-6 border-t">
            <button type="button" @click="showDealModal = false" class="px-6 py-2 text-gray-500 font-bold">Annuler</button>
            <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-lg font-bold">Créer</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Modal Nouvelle Activité -->
  <div v-if="showActivityModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
      <div class="p-6">
        <h3 class="text-xl font-black text-gray-900 mb-6">Ajouter une activité</h3>
        <form @submit.prevent="addActivity" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Type d'activité</label>
            <select v-model="activityForm.type" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none">
              <option value="note">📝 Note</option>
              <option value="call">📞 Appel</option>
              <option value="meeting">🤝 Réunion</option>
              <option value="email">📧 Email Sortant</option>
            </select>
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Objet / Titre</label>
            <input v-model="activityForm.subject" required class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none" />
          </div>
          <div>
            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Description</label>
            <textarea v-model="activityForm.description" rows="3" class="w-full px-4 py-2 bg-gray-50 border border-gray-200 rounded-lg outline-none"></textarea>
          </div>
          <div class="flex justify-end space-x-3 pt-6 border-t">
            <button type="button" @click="showActivityModal = false" class="px-6 py-2 text-gray-500 font-bold">Annuler</button>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white rounded-lg font-bold">Enregistrer</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../utils/axios'

const route = useRoute()
const account = ref(null)
const loading = ref(true)
const syncing = ref(false)
const showEditModal = ref(false)
const showContactModal = ref(false)
const showDealModal = ref(false)
const showActivityModal = ref(false)

const accountForm = ref({ name: '', industry: '', country: '' })
const contactForm = ref({ first_name: '', last_name: '', email: '' })
const dealForm = ref({ name: '', amount: 0, stage: 'prospecting', probability: 50 })
const activityForm = ref({ type: 'note', subject: '', description: '', date: '' })

const fetchData = async () => {
  try {
    const res = await api.get(`/accounts/${route.params.id}`)
    account.value = res.data
  } catch (error) {
    console.error(error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchData)

// Actions
const openEditModal = () => {
  accountForm.value = { ...account.value }
  showEditModal.value = true
}

const updateAccount = async () => {
  try {
    await api.put(`/accounts/${account.value.id}`, accountForm.value)
    showEditModal.value = false
    fetchData()
  } catch (e) { alert("Erreur lors de la mise à jour") }
}

const openContactModal = () => {
  contactForm.value = { first_name: '', last_name: '', email: '', account_id: account.value.id }
  showContactModal.value = true
}

const addContact = async () => {
  try {
    await api.post('/contacts', contactForm.value)
    showContactModal.value = false
    fetchData()
  } catch (e) { alert("Erreur lors de l'ajout du contact") }
}

const openDealModal = () => {
  dealForm.value = { 
    name: "Deal - " + account.value.name, 
    amount: 1000, 
    stage: 'prospecting', 
    probability: 20, 
    account_id: account.value.id 
  }
  showDealModal.value = true
}

const createDeal = async () => {
  try {
    await api.post('/deals', dealForm.value)
    showDealModal.value = false
    fetchData()
  } catch (e) { alert("Erreur lors de la création du deal") }
}

const openActivityModal = () => {
  activityForm.value = { 
    type: 'note', 
    subject: '', 
    description: '', 
    date: new Date().toISOString(),
    account_id: account.value.id,
    user_id: 1 // Default
  }
  showActivityModal.value = true
}

const addActivity = async () => {
  try {
    await api.post('/activities', activityForm.value)
    showActivityModal.value = false
    fetchData()
  } catch (e) { alert("Erreur lors de l'ajout de l'activité") }
}

const syncEmails = async () => {
  syncing.value = true
  try {
    const res = await api.post('/sync/emails')
    console.log("Sync Response:", res.data)
    await fetchData()
    alert("Synchronisation réussie !")
  } catch (e) {
    console.error("Sync Error:", e)
    const msg = e.response?.data?.error || e.message || "Erreur inconnue"
    alert("Erreur de synchronisation : " + msg)
  } finally {
    syncing.value = false
  }
}

const activityIcon = (type) => {
  const map = { call: '📞', meeting: '🤝', email: '📧', note: '📝', event: '📅' }
  return map[type] || '⚡'
}

const activityIconClass = (type) => {
  const map = {
    call: 'bg-green-100 text-green-800',
    meeting: 'bg-blue-100 text-blue-800',
    email: 'bg-indigo-100 text-indigo-800',
    note: 'bg-gray-100 text-gray-800',
    event: 'bg-orange-100 text-orange-800'
  }
  return map[type] || 'bg-gray-100 text-gray-500'
}
</script>
