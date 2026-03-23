<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Gestion des Factures</h1>
      <button @click="openCreateModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
        + Nouvelle Facture
      </button>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 items-center flex justify-between">
        <div>
           <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Total Encaissé</p>
           <p class="text-2xl font-bold text-green-600">{{ totalPaid.toFixed(2) }} €</p>
        </div>
        <div class="text-2xl">💰</div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 items-center flex justify-between">
        <div>
           <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">En Attente</p>
           <p class="text-2xl font-bold text-blue-600">{{ totalPending.toFixed(2) }} €</p>
           <p class="text-2xl font-bold text-blue-600">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(totalPending) }}</p>
        </div>
        <div class="text-2xl">⏳</div>
      </div>
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 items-center flex justify-between">
         <div>
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Impayés</p>
            <p class="text-2xl font-bold text-red-600">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(totalOverdue) }}</p>
         </div>
         <div class="text-2xl">⚠️</div>
      </div>
       <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 items-center flex justify-between">
         <div>
            <p class="text-xs text-gray-500 font-semibold uppercase tracking-wider">Nombre</p>
            <p class="text-2xl font-bold">{{ invoices.length }}</p>
         </div>
         <div class="text-2xl">📄</div>
      </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md border border-gray-200">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facture</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Client</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payé</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date Limite</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Statut</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="inv in invoices" :key="inv.id" class="hover:bg-gray-50">
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-indigo-600">{{ inv.reference }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">{{ inv.account?.name || 'Client Direct' }}</div>
              <div class="text-xs text-gray-500">{{ inv.contact?.first_name }} {{ inv.contact?.last_name || '-' }}</div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(inv.total) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600 font-bold bg-green-50/50">{{ new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(inv.amount_paid) }}</td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(inv.due_date) }}</td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="statusClass(inv.status)" class="px-2 py-1 text-xs font-semibold rounded-full capitalize">
                {{ translateStatus(inv.status) }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm space-x-2 no-print">
              <button @click="generatePDF(inv)" class="text-indigo-600 hover:text-indigo-900 font-bold bg-indigo-50 px-2 py-1 rounded">📄 PDF</button>
              <button v-if="inv.status !== 'paid'" @click="markAsPaid(inv)" class="text-green-600 hover:text-green-900 font-medium">Encaisser</button>
              <button @click="openEditModal(inv)" class="text-indigo-600 hover:text-indigo-900">Gérer</button>
              <button @click="deleteInvoice(inv.id)" class="text-red-600 hover:text-red-900">Supprimer</button>
            </td>
          </tr>
          <tr v-if="invoices.length === 0">
            <td colspan="7" class="px-6 py-10 text-center text-gray-500">Aucune facture trouvée.</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Create/Edit Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 px-4">
      <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
        <div class="p-6">
          <h3 class="text-lg font-bold text-gray-900 mb-6">{{ editingId ? 'Gestion Facture' : 'Nouvelle Facture' }}</h3>
          <form @submit.prevent="submitForm" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700">Compte / Entreprise</label>
                <select v-model="form.account_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  <option value="">Sélectionner</option>
                  <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
              </div>
              <div>
                 <label class="block text-sm font-medium text-gray-700">Contact</label>
                 <select v-model="form.contact_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  <option value="">Sélectionner</option>
                  <option v-for="c in contacts" :key="c.id" :value="c.id">{{ c.first_name }} {{ c.last_name }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Date d'échéance (due date)</label>
                <input v-model="form.due_date" type="date" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700">Statut</label>
                <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                  <option value="draft">Brouillon</option>
                  <option value="sent">Envoyé</option>
                  <option value="paid">Payée</option>
                  <option value="overdue">En retard</option>
                  <option value="cancelled">Annulée</option>
                </select>
              </div>
            </div>

            <div class="border-t pt-4">
              <div class="grid grid-cols-2 gap-4">
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Montant Total TTC *</label>
                    <input v-model="form.subtotal" type="number" step="0.01" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border">
                 </div>
                 <div>
                    <label class="block text-sm font-medium text-gray-700">Montant déjà Payé</label>
                    <input v-model="form.amount_paid" type="number" step="0.01" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border text-green-600 font-bold">
                 </div>
              </div>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700">Notes / Informations de paiement</label>
              <textarea v-model="form.notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm p-2 border"></textarea>
            </div>

            <div class="flex items-center justify-end space-x-3 pt-4">
              <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Annuler</button>
              <button type="submit" :disabled="submitting" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700 disabled:opacity-50">
                {{ submitting ? 'Chargement...' : (editingId ? 'Mettre à Jour' : 'Créer') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../utils/axios'
import { useSettingsStore } from '../stores/settings'
import { jsPDF } from 'jspdf'
import autoTable from 'jspdf-autotable'

const settings = useSettingsStore()
const invoices = ref([])
const accounts = ref([])
const contacts = ref([])
const showModal = ref(false)
const submitting = ref(false)
const editingId = ref(null)

const emptyForm = () => ({
  account_id: '', contact_id: '', subtotal: 0, amount_paid: 0,
  tax_rate: 20, currency: 'EUR', status: 'sent', due_date: '', notes: ''
})
const form = ref(emptyForm())

// Computed stats
const totalPaid = computed(() => invoices.value.reduce((sum, i) => sum + parseFloat(i.amount_paid || 0), 0))
const totalPending = computed(() => invoices.value.filter(i => i.status !== 'paid' && i.status !== 'cancelled').reduce((sum, i) => sum + parseFloat(i.total || 0), 0))
const totalOverdue = computed(() => invoices.value.filter(i => i.status === 'overdue' || (i.status === 'sent' && new Date(i.due_date) < new Date())).reduce((sum, i) => sum + parseFloat(i.total || 0), 0))

const fetchAll = async () => {
  try {
    const [invRes, aRes, cRes] = await Promise.all([
      api.get('/invoices'),
      api.get('/accounts'),
      api.get('/contacts')
    ])
    invoices.value = invRes.data.data || invRes.data
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

const openEditModal = (inv) => {
  editingId.value = inv.id
  form.value = { ...inv, 
    account_id: inv.account_id || '', 
    contact_id: inv.contact_id || '',
    due_date: inv.due_date ? inv.due_date.split('T')[0] : ''
  }
  showModal.value = true
}

const submitForm = async () => {
  submitting.value = true
  try {
    if (editingId.value) {
      await api.put(`/invoices/${editingId.value}`, form.value)
    } else {
      await api.post('/invoices', form.value)
    }
    showModal.value = false
    fetchAll()
  } catch (e) {
    alert("Erreur")
  } finally {
    submitting.value = false
  }
}

const deleteInvoice = async (id) => {
  if (!confirm("Effacer définitivement cette facture ?")) return
  try {
    await api.delete(`/invoices/${id}`)
    fetchAll()
  } catch (e) { console.error(e) }
}

const markAsPaid = async (inv) => {
  try {
    await api.put(`/invoices/${inv.id}`, { status: 'paid', amount_paid: inv.total })
    fetchAll()
  } catch (e) { console.error(e) }
}

const formatDate = (dateString) => {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString()
}

const statusClass = (s) => {
  const map = {
    draft: 'bg-gray-100 text-gray-800',
    sent: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800',
    cancelled: 'bg-gray-400 text-white'
  }
  return map[s] || 'bg-gray-100 text-gray-800'
}

const translateStatus = (s) => {
  const map = { draft: 'Brouillon', sent: 'Envoyée', paid: 'Payée', overdue: 'Retard', cancelled: 'Annulée' }
  return map[s] || s
}

const generatePDF = (inv) => {
  const doc = new jsPDF()
  const logo = settings.logo_url
  
  // Header
  doc.setFontSize(22)
  doc.setTextColor(79, 70, 229) // Indigo-600
  doc.text(settings.site_name, 14, 22)
  
  doc.setFontSize(10)
  doc.setTextColor(100)
  doc.text("FACTURE : " + inv.reference, 14, 30)
  doc.text("Date : " + formatDate(inv.created_at || new Date()), 14, 35)
  doc.text("Échéance : " + formatDate(inv.due_date), 14, 40)

  // Client info
  doc.setFontSize(12)
  doc.setTextColor(0)
  doc.text("Client :", 120, 30)
  doc.setFontSize(10)
  doc.text(inv.account?.name || "Client Direct", 120, 35)
  if (inv.contact) {
    doc.text(inv.contact.first_name + " " + inv.contact.last_name, 120, 40)
    doc.text(inv.contact.email, 120, 45)
  }

  // Items Table
  const items = inv.line_items || [{ description: 'Prestation CRM', qty: 1, unit_price: inv.total, total: inv.total }]
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
    theme: 'striped',
    headStyles: { fillColor: [79, 70, 229] }
  })

  // Totals
  const finalY = (doc).lastAutoTable.finalY || 150
  doc.setFontSize(14)
  doc.setFont('helvetica', 'bold')
  const totalStr = "TOTAL TTC : " + new Intl.NumberFormat('fr-FR', { style: 'currency', currency: settings.currency }).format(inv.total)
  doc.text(totalStr, 14, finalY + 15)

  if (inv.notes) {
    doc.setFontSize(9)
    doc.setFont('helvetica', 'normal')
    doc.text("Notes : " + inv.notes, 14, finalY + 25)
  }

  doc.save(`Facture_${inv.reference}.pdf`)
}
</script>

<style>
@media print {
  .no-print, .sidebar, nav, header, button {
    display: none !important;
  }
  .bg-indigo-900, .bg-indigo-800, .bg-indigo-600 {
    background-color: transparent !important;
    color: black !important;
  }
  table {
    width: 100% !important;
    border: 1px solid #eee !important;
  }
  body {
    background: white !important;
  }
}
</style>
