<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <h1 class="text-2xl font-semibold text-gray-900">Gestion des Documents</h1>
      <div class="flex items-center space-x-2">
        <button @click="openFolderModal" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 shadow-sm transition">
          📂 Nouveau Dossier
        </button>
        <button @click="openUploadModal" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 transition">
          📤 Téléverser
        </button>
      </div>
    </div>

    <!-- Breadcrumbs -->
    <nav class="flex px-5 py-3 text-gray-700 border border-gray-200 rounded-lg bg-gray-50">
      <ol class="inline-flex items-center space-x-1 md:space-x-3">
        <li class="inline-flex items-center">
          <button @click="navigateTo(null)" class="text-sm font-bold text-gray-700 hover:text-indigo-600 flex items-center">
            🏠 Racine
          </button>
        </li>
        <li v-for="crumb in breadcrumbs" :key="crumb.id">
          <div class="flex items-center">
            <span class="mx-2 text-gray-400">/</span>
            <button @click="navigateTo(crumb.id)" class="text-sm font-bold text-gray-700 hover:text-indigo-600 transition">
              {{ crumb.name }}
            </button>
          </div>
        </li>
      </ol>
    </nav>

    <!-- Category Filters -->
    <div class="flex space-x-2 overflow-x-auto pb-2">
      <button v-for="cat in categories" :key="cat.id" @click="activeCategory = cat.id"
         :class="activeCategory === cat.id ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'"
         class="px-4 py-2 rounded-full text-sm font-medium border border-gray-200 transition whitespace-nowrap">
        {{ cat.name }} ({{ countByCategory(cat.id) }})
      </button>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4">
      <div v-for="doc in filteredDocuments" :key="doc.id" 
           @dragstart="onDragStart($event, doc)"
           @dragover.prevent="onDragOver($event, doc)"
           @drop="onDrop($event, doc)"
           draggable="true"
           @dblclick="doc.type === 'folder' ? navigateTo(doc.id, doc.name) : null"
           :class="[
             'bg-white rounded-xl shadow-sm border p-4 transition group cursor-pointer relative',
             doc.type === 'folder' ? 'border-yellow-100 hover:border-yellow-400' : 'border-gray-100 hover:border-indigo-400'
           ]">
        
        <div class="flex items-center justify-between mb-2">
           <span class="text-2xl">{{ doc.type === 'folder' ? '📁' : getFileIcon(doc.mime_type) }}</span>
           <div class="flex space-x-1 opacity-0 group-hover:opacity-100 transition">
             <button @click.stop="deleteDoc(doc.id)" class="p-1.5 hover:bg-red-50 text-red-500 rounded-md">🗑️</button>
           </div>
        </div>

        <div class="mt-2 text-center">
          <h3 class="font-bold text-gray-900 text-xs mb-0.5 truncate">{{ doc.name }}</h3>
          <p v-if="doc.type === 'file'" class="text-[9px] text-gray-400 truncate">{{ formatSize(doc.file_size) }}</p>
          <p v-else class="text-[9px] text-yellow-600 font-bold">Dossier</p>
        </div>
        
        <!-- File overlay actions -->
        <div v-if="doc.type === 'file'" class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 flex items-start justify-end p-2 pointer-events-none">
           <a :href="getFileUrl(doc)" target="_blank" class="p-2 bg-indigo-600 text-white rounded-lg shadow-lg pointer-events-auto">👁️</a>
        </div>
      </div>

      <!-- Empty state -->
      <div v-if="filteredDocuments.length === 0" class="col-span-full border-2 border-dashed border-gray-100 rounded-2xl py-12 text-center">
         <p class="text-gray-300 font-bold uppercase tracking-widest text-sm">Espace vide</p>
         <button @click="openUploadModal" class="mt-2 text-indigo-500 font-bold hover:underline">Déposer un fichier</button>
      </div>
    </div>

    <!-- Upload Modal -->
    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 px-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-md animate-in slide-in-from-bottom duration-200">
        <div class="p-6">
          <h3 class="text-xl font-black text-gray-900 mb-6 font-primary uppercase tracking-tighter">📤 Ajouter un Document</h3>
          <form @submit.prevent="submitForm" class="space-y-4">
             <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nom du document *</label>
                <input v-model="form.name" type="text" required class="w-full rounded-xl bg-gray-50 border-gray-200 shadow-sm focus:ring-2 focus:ring-indigo-500 p-2.5 border outline-none font-bold">
             </div>
             <div class="grid grid-cols-2 gap-4">
               <div>
                 <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Catégorie</label>
                 <select v-model="form.category" class="w-full rounded-xl bg-gray-50 border-gray-200 p-2.5 border outline-none">
                    <option v-for="cat in categories.slice(1)" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                 </select>
               </div>
               <div>
                  <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Fichier *</label>
                  <label class="flex items-center justify-center w-full px-4 py-2 bg-indigo-50 text-indigo-700 rounded-xl cursor-pointer hover:bg-indigo-100 transition border-2 border-dashed border-indigo-200">
                    <span class="text-xs font-bold">Choisir...</span>
                    <input type="file" @change="handleFile" required class="hidden">
                  </label>
               </div>
             </div>
             <div>
                <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Lier à un compte</label>
                <select v-model="form.account_id" class="w-full rounded-xl bg-gray-50 border-gray-200 p-2.5 border outline-none">
                   <option value="">-- Aucun --</option>
                   <option v-for="acc in accounts" :key="acc.id" :value="acc.id">{{ acc.name }}</option>
                </select>
             </div>
             <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-50">
                <button type="button" @click="showModal = false" class="px-6 py-2 text-gray-400 font-bold hover:text-gray-600">Annuler</button>
                <button type="submit" :disabled="submitting" class="px-6 py-2 bg-indigo-600 text-white rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-600/20 disabled:opacity-50 transition">
                   {{ submitting ? 'Envoi...' : 'Téléverser' }}
                </button>
             </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Folder Modal -->
    <div v-if="showFolderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900 bg-opacity-50 px-4">
      <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 animate-in zoom-in duration-150">
        <h3 class="text-lg font-bold text-gray-900 mb-4 font-primary uppercase tracking-tighter">📂 Nouveau Dossier</h3>
        <input v-model="folderName" type="text" placeholder="Nom du dossier..." class="w-full px-4 py-3 bg-gray-50 border rounded-xl outline-none focus:ring-2 focus:ring-yellow-400 border-gray-200 font-bold" required @keyup.enter="submitFolder">
        <div class="flex justify-end space-x-3 mt-6">
          <button @click="showFolderModal = false" class="text-gray-400 font-bold px-4 py-2">Annuler</button>
          <button @click="submitFolder" class="bg-yellow-400 text-yellow-900 px-8 py-2 rounded-xl font-bold hover:bg-yellow-500 shadow-lg shadow-yellow-400/20 transition">Créer</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import api from '../utils/axios'

const documents = ref([])
const accounts = ref([])
const showModal = ref(false)
const showFolderModal = ref(false)
const folderName = ref('')
const parentId = ref(null)
const breadcrumbs = ref([])
const submitting = ref(false)
const activeCategory = ref('all')
const categories = [
  { id: 'all', name: 'Tous' },
  { id: 'contrat', name: 'Contrats' },
  { id: 'facture', name: 'Factures' },
  { id: 'offre', name: 'Offres' },
  { id: 'autre', name: 'Autres' }
]

const form = ref({ name: '', category: 'autre', account_id: '', file: null })

const fetchAll = async () => {
  try {
    const params = { parent_id: parentId.value }
    const [dRes, aRes] = await Promise.all([
      api.get('/documents', { params }),
      api.get('/accounts')
    ])
    documents.value = dRes.data.data || dRes.data
    accounts.value = aRes.data.data || aRes.data
  } catch (e) { console.error(e) }
}

onMounted(fetchAll)

const navigateTo = (id, name = null) => {
  if (id === null) {
    breadcrumbs.value = []
    parentId.value = null
  } else {
    // Basic crumb management
    const idx = breadcrumbs.value.findIndex(c => c.id === id)
    if (idx !== -1) {
      breadcrumbs.value = breadcrumbs.value.slice(0, idx + 1)
    } else if (name) {
      breadcrumbs.value.push({ id, name })
    }
    parentId.value = id
  }
  fetchAll()
}

// Drag and Drop
let draggedDoc = null
const onDragStart = (e, doc) => {
  draggedDoc = doc
  e.dataTransfer.effectAllowed = 'move'
}
const onDragOver = (e, target) => {
  if (target.type === 'folder' && draggedDoc && draggedDoc.id !== target.id) {
    e.dataTransfer.dropEffect = 'move'
  } else {
    e.dataTransfer.dropEffect = 'none'
  }
}
const onDrop = async (e, target) => {
  if (target.type === 'folder' && draggedDoc && draggedDoc.id !== target.id) {
    try {
      await api.patch(`/documents/${draggedDoc.id}/move`, { parent_id: target.id })
      fetchAll()
    } catch (e) { console.error(e) }
  }
}

const filteredDocuments = computed(() => {
  if (activeCategory.value === 'all') return documents.value
  return documents.value.filter(d => d.category === activeCategory.value)
})

const countByCategory = (catId) => {
  if (catId === 'all') return documents.value.length
  return documents.value.filter(d => d.category === catId).length
}

const openUploadModal = () => {
  form.value = { name: '', category: 'autre', account_id: '', file: null }
  showModal.value = true
}

const handleFile = (e) => {
  form.value.file = e.target.files[0]
}

const openFolderModal = () => {
  folderName.value = ''
  showFolderModal.value = true
}

const submitFolder = async () => {
  if (!folderName.value) return
  try {
    await api.post('/documents/folders', { 
      name: folderName.value, 
      parent_id: parentId.value 
    })
    showFolderModal.value = false
    fetchAll()
  } catch (e) { alert("Erreur création dossier") }
}

const submitForm = async () => {
  if (!form.value.file) return
  submitting.value = true
  
  const formData = new FormData()
  formData.append('file', form.value.file)
  formData.append('name', form.value.name)
  formData.append('category', form.value.category)
  formData.append('parent_id', parentId.value || '')
  if (form.value.account_id) formData.append('account_id', form.value.account_id)

  try {
    await api.post('/documents', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    showModal.value = false
    fetchAll()
  } catch (e) {
    alert("Erreur upload")
  } finally {
    submitting.value = false
  }
}

const deleteDoc = async (id) => {
  if (!confirm("Effacer ce fichier ?")) return
  try {
    await api.delete(`/documents/${id}`)
    fetchAll()
  } catch (e) { console.error(e) }
}

const formatSize = (bytes) => {
  if (!bytes) return '0 B'
  const k = 1024
  const sizes = ['B', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
}

const getFileIcon = (mime) => {
  if (!mime) return '📄'
  if (mime.includes('pdf')) return '📕'
  if (mime.includes('image')) return '🖼️'
  if (mime.includes('word')) return '📘'
  if (mime.includes('excel') || mime.includes('sheet')) return '📗'
  return '📄'
}

const getFileUrl = (doc) => {
  return `${api.defaults.baseURL.replace('/api', '')}/storage/${doc.file_path}`
}
</script>

<style scoped>
.grid > div {
  user-select: none;
}
</style>
