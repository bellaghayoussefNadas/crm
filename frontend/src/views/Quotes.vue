<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Gestion des Devis</h1>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
        + Nouveau Devis
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        <p class="text-sm text-gray-500">En attente (Brouillon)</p>
        <p class="text-2xl font-bold">{{ quotes.filter(q => q.status === 'draft').length }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        <p class="text-sm text-gray-500">Envoyés</p>
        <p class="text-2xl font-bold text-blue-600">{{ quotes.filter(q => q.status === 'sent').length }}</p>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        <p class="text-sm text-gray-500">Acceptés</p>
        <p class="text-2xl font-bold text-green-600">{{ quotes.filter(q => q.status === 'accepted').length }}</p>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Référence</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titre / Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Montant</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Validité</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="quote in quotes" :key="quote.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-indigo-600">{{ quote.reference }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ quote.title }}</div>
              <div class="text-xs text-gray-500">{{ quote.account?.name || quote.contact?.first_name || 'Inconnu' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(quote.total) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ quote.valid_until || '-' }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="statusClass(quote.status)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                {{ translateStatus(quote.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 no-print">
              <button @click="generatePDF(quote)" class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-2 py-1 rounded">📄 PDF</button>
              <button @click="convertToInvoice(quote)" v-if="quote.status === 'accepted'" class="text-green-600 hover:text-green-900 font-medium">Facturer</button>
              <button @click="openEditModal(quote)" class="text-indigo-600 hover:text-indigo-900">Modifier</button>
              <button @click="deleteQuote(quote.id)" class="text-red-600 hover:text-red-900">Supprimer</button>
            </td>
          </tr>
          <tr v-if="quotes.length === 0">
            <td colspan="6" class="px-6 py-10 text-center text-gray-500">Aucun devis trouvé.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 px-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">{{ editingId ? 'Modifier le Devis' : 'Nouveau Devis' }}</h3>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Titre du Devis *</label>
                <input v-model="form.title" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Compte (Optionnel)</label>
                <select v-model="form.account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  <option value="">Sélectionner</option>
                  <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Contact (Optionnel)</label>
                <select v-model="form.contact_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  <option value="">Sélectionner</option>
                  <option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Valide jusqu'au</label>
                <input v-model="form.valid_until" type="date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Statut</label>
                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  <option value="draft">Brouillon</option>
                  <option value="sent">Envoyé</option>
                  <option value="accepted">Accepté</option>
                  <option value="rejected">Rejeté</option>
                </select>
              </div>
            </div>

            <!-- Items -->
            <div class="border-t pt-4">
              <h4 class="text-sm font-semibold text-gray-900 mb-2">Montants</h4>
              <div class="grid grid-cols-2 gap-4">
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Sous-total HT *</label>
                    <input v-model="form.subtotal" type="number" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                 </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Taux TVA (%)</label>
                    <input v-model="form.tax_rate" type="number" step="0.1" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                 </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Notes / Conditions</label>
              <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
              <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
              <button type="submit" :disabled="submitting" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                {{ submitting ? 'Chargement...' : (editingId ? 'Enregistrer' : 'Créer') }}
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
import { useSettingsStore } from '../stores/settings'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

const settings = useSettingsStore()
const quotes = ref([])
const accounts = ref([])
const contacts = ref([])
const showModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)

const emptyForm = () => ({
  title: '', account_id: '', contact_id: '', subtotal: 0, tax_rate: 20,
  currency: 'EUR', status: 'draft', valid_until: '', notes: ''
})
const form = ref(emptyForm())

const fetchAll = async () => {
  try {
    const [qRes, aRes, cRes] = await Promise.all([
      api.get('/quotes'),
      api.get('/accounts'),
      api.get('/contacts')
    ])
    quotes.value = qRes.data.data || qRes.data
    accounts.value = aRes.data.data || aRes.data
    contacts.value = cRes.data.data || cRes.data
  } catch (e) { console.error(e) }
}

onMounted(fetchAll)

const openCreateModal = () => {
  editingId.value = null
  form.value = emptyForm()
  showModal.value = true
}

const openEditModal = (quote) => {
  editingId.value = quote.id
  form.value = { ...quote, 
    account_id: quote.account_id || '', 
    contact_id: quote.contact_id || '',
    valid_until: quote.valid_until ? quote.valid_until.split('T')[0] : ''
  }
  showModal.value = true
}

const submitForm = async () => {
  submitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/quotes/${editingId.value}`, form.value)
    } else {
      await api.post('/quotes', form.value)
    }
    showModal.value = false
    fetchAll()
  } catch (e) {
    alert("Erreur lors de l'enregistrement")
    console.error(e)
  } finally {
    submitting.value = false
  }
}

const deleteQuote = async (id) => {
  if (!confirm("Supprimer ce devis ?")) return
  try {
    await api.delete(`/quotes/${id}`)
    fetchAll()
  } catch (e) { console.error(e) }
}

const convertToInvoice = async (quote) => {
  if (!confirm("Transformer ce devis accepté en facture ?")) return
  try {
    await api.post(`/quotes/${quote.id}/convert-to-invoice`)
    alert("Facture générée avec succès !")
    fetchAll()
  } catch (e) { console.error(e) }
}

const statusClass = (s) => {
  const map = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    accepted: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
    expired: 'bg-yellow-100 text-yellow-800'
  }
  return map[s] || 'bg-gray-100 text-gray-800'
}

const translateStatus = (s) => {
  const map = { draft: 'Brouillon', sent: 'Envoyé', accepted: 'Accepté', rejected: 'Refusé', expired: 'Expiré' }
  return map[s] || s
}

const generatePDF = (quote) => {
  const doc = new jsPDF()
  
  // Header
  doc.setFontSize(22)
  doc.setTextColor(79, 70, 229)
  doc.text(settings.site_name, 14, 22)
  
  doc.setFontSize(10)
  doc.setTextColor(100)
  doc.text("DEVIS : " + quote.reference, 14, 30)
  doc.text("Date : " + new Date().toLocaleDateString(), 14, 35)
  doc.text("Valide jusqu'au : " + (quote.valid_until || 'N/A'), 14, 40)

  // Client info
  doc.setFontSize(12)
  doc.setTextColor(0)
  doc.text("Client :", 120, 30)
  doc.setFontSize(10)
  doc.text(quote.account?.name || "Client Direct", 120, 35)
  if (quote.contact) {
    doc.text(quote.contact.first_name + " " + quote.contact.last_name, 120, 40)
  }

  // Items Table
  const items = quote.line_items || [{ description: quote.title, qty: 1, unit_price: quote.total, total: quote.total }]
  const tableData = items.map(item => [
    item.description,
    item.qty,
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(item.unit_price),
    new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(item.qty * item.unit_price)
  ])

  autoTable(doc, {
    startY: 60,
    head: [['Description', 'Qté', 'Prix Unitaire', 'Total']],
    body: tableData,
    theme: 'grid',
    headStyles: { fillColor: [79, 70, 229] }
  })

  // Totals
  const finalY = (doc).lastAutoTable.finalY || 150
  doc.setFontSize(14)
  doc.setFont('helvetica', 'bold')
  const totalStr = "TOTAL DEVIS : " + new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(quote.total)
  doc.text(totalStr, 14, finalY + 15)

  doc.save(`Devis_${quote.reference}.pdf`)
}
</script>

<style>
@media print {
  .no-print, .sidebar, nav, header, button, .modal {
    display: none !important;
  }
}
</style>
