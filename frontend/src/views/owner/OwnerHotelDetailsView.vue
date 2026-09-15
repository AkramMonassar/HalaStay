<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import api from '../../services/api'

const route = useRoute()
const hotel = ref(null)
const loading = ref(true)
const storageBase = (import.meta.env.VITE_API_BASE_URL || '').replace('/api/v1', '') + '/storage/'

const form = ref({})
const savingInfo = ref(false)
const infoMessage = ref('')
const infoError = ref('')

const newImages = ref([])
const uploadingImages = ref(false)
const imagesMessage = ref('')

const roomForm = ref({ name: '', stay_type: 'room', description: '', max_adults: 2, max_children: 0, total_units: 1, base_price: 100 })
const savingRoom = ref(false)
const roomMessage = ref('')
const roomError = ref('')

const editingId = ref(null)
const editForm = ref({})

onMounted(async () => {
  try {
    const { data } = await api.get(`/owner/hotels/${route.params.id}`)
    hotel.value = data.data
    form.value = {
      name: data.data.name,
      description: data.data.description || '',
      address: data.data.address || '',
      phone: data.data.phone || '',
      email: data.data.email || '',
      star_rating: data.data.star_rating,
    }
  } finally {
    loading.value = false
  }
})

async function saveInfo() {
  infoError.value = ''
  infoMessage.value = ''
  savingInfo.value = true
  try {
    const { data } = await api.put(`/owner/hotels/${hotel.value.id}`, form.value)
    hotel.value = { ...hotel.value, ...data.data }
    infoMessage.value = 'تم حفظ البيانات.'
  } catch (e) {
    const errors = e.response?.data?.errors
    infoError.value = errors ? Object.values(errors).flat()[0] : (e.response?.data?.message || 'تعذر الحفظ.')
  } finally {
    savingInfo.value = false
  }
}

async function uploadImages() {
  if (!newImages.value.length) return
  uploadingImages.value = true
  imagesMessage.value = ''
  try {
    const fd = new FormData()
    newImages.value.forEach((img) => fd.append('images[]', img))
    const { data } = await api.post(`/owner/hotels/${hotel.value.id}/images`, fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    hotel.value.images = data.data
    imagesMessage.value = 'تم رفع الصور.'
    newImages.value = []
  } catch (e) {
    imagesMessage.value = e.response?.data?.message || 'تعذر رفع الصور.'
  } finally {
    uploadingImages.value = false
  }
}

async function saveRoom() {
  roomError.value = ''
  roomMessage.value = ''
  savingRoom.value = true
  try {
    const { data } = await api.post(`/owner/hotels/${hotel.value.id}/rooms`, roomForm.value)
    hotel.value.accommodation_types = [...(hotel.value.accommodation_types || []), data.data]
    roomMessage.value = 'تم إضافة نوع الإقامة.'
    roomForm.value = { name: '', stay_type: 'room', description: '', max_adults: 2, max_children: 0, total_units: 1, base_price: 100 }
  } catch (e) {
    const errors = e.response?.data?.errors
    roomError.value = errors ? Object.values(errors).flat()[0] : (e.response?.data?.message || 'تعذر الإضافة.')
  } finally {
    savingRoom.value = false
  }
}

function startEdit(t) {
  editingId.value = t.id
  editForm.value = {
    name: t.name,
    stay_type: t.stay_type,
    max_adults: t.max_adults,
    max_children: t.max_children,
    total_units: t.total_units,
    base_price: Number(t.base_price),
  }
}

async function saveEdit(t) {
  roomError.value = ''
  roomMessage.value = ''
  try {
    const { data } = await api.put(`/owner/hotels/${hotel.value.id}/rooms/${t.id}`, editForm.value)
    const idx = hotel.value.accommodation_types.findIndex((x) => x.id === t.id)
    if (idx >= 0) hotel.value.accommodation_types[idx] = data.data
    editingId.value = null
    roomMessage.value = 'تم تحديث نوع الإقامة.'
  } catch (e) {
    const errors = e.response?.data?.errors
    roomError.value = errors ? Object.values(errors).flat()[0] : (e.response?.data?.message || 'تعذر التحديث.')
  }
}

async function toggleType(t) {
  roomError.value = ''
  try {
    const { data } = await api.patch(`/owner/hotels/${hotel.value.id}/rooms/${t.id}/toggle`)
    const idx = hotel.value.accommodation_types.findIndex((x) => x.id === t.id)
    if (idx >= 0) hotel.value.accommodation_types[idx] = data.data
  } catch (e) {
    roomError.value = e.response?.data?.message || 'تعذر تغيير الحالة.'
  }
}
</script>

<template>
  <div class="container py-4">
    <div v-if="loading" class="text-center py-5">جارِ التحميل...</div>

    <div v-else-if="hotel">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">🛠️ إدارة: {{ hotel.name }}</h4>
        <span class="badge bg-secondary fs-6">{{ hotel.status }}</span>
      </div>

      <div class="row g-4">
        <div class="col-lg-6">
          <div class="card shadow-sm mb-4">
            <div class="card-header bg-white fw-semibold">بيانات الفندق</div>
            <div class="card-body">
              <div v-if="infoMessage" class="alert alert-success py-2">{{ infoMessage }}</div>
              <div v-if="infoError" class="alert alert-danger py-2">{{ infoError }}</div>
              <div class="mb-2">
                <label class="form-label">الاسم</label>
                <input v-model="form.name" class="form-control" />
              </div>
              <div class="mb-2">
                <label class="form-label">الوصف</label>
                <textarea v-model="form.description" class="form-control" rows="2"></textarea>
              </div>
              <div class="row g-2 mb-2">
                <div class="col"><label class="form-label">العنوان</label><input v-model="form.address" class="form-control" /></div>
                <div class="col"><label class="form-label">الهاتف</label><input v-model="form.phone" class="form-control" /></div>
              </div>
              <div class="row g-2 mb-3">
                <div class="col"><label class="form-label">البريد</label><input v-model="form.email" class="form-control" /></div>
                <div class="col">
                  <label class="form-label">النجوم</label>
                  <select v-model.number="form.star_rating" class="form-select">
                    <option :value="1">1</option><option :value="2">2</option><option :value="3">3</option><option :value="4">4</option><option :value="5">5</option>
                  </select>
                </div>
              </div>
              <button class="btn btn-primary w-100" :disabled="savingInfo" @click="saveInfo">حفظ التعديلات</button>
            </div>
          </div>

          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">الصور</div>
            <div class="card-body">
              <div class="row g-2 mb-3">
                <div v-for="img in hotel.images" :key="img.id" class="col-4">
                  <img :src="storageBase + img.image_path" class="img-fluid rounded w-100" style="height: 90px; object-fit: cover" />
                  <div v-if="img.is_cover" class="small text-primary">الغلاف</div>
                </div>
              </div>
              <input type="file" class="form-control mb-2" multiple accept="image/*" @change="(e) => (newImages = Array.from(e.target.files))" />
              <button class="btn btn-outline-primary w-100" :disabled="uploadingImages || !newImages.length" @click="uploadImages">
                رفع الصور
              </button>
              <div v-if="imagesMessage" class="small text-muted mt-2">{{ imagesMessage }}</div>
            </div>
          </div>
        </div>

        <div class="col-lg-6">
          <div class="card shadow-sm">
            <div class="card-header bg-white fw-semibold">أنواع الإقامة</div>
            <div class="card-body">
              <div v-if="roomMessage" class="alert alert-success py-2">{{ roomMessage }}</div>
              <div v-if="roomError" class="alert alert-danger py-2">{{ roomError }}</div>

              <div v-for="t in hotel.accommodation_types" :key="t.id" class="border rounded p-2 mb-2">
                <div v-if="editingId !== t.id" class="d-flex justify-content-between align-items-center">
                  <div>
                    <div class="fw-semibold">
                      {{ t.name }}
                      <span v-if="!t.is_active" class="badge bg-secondary ms-1">موقوف</span>
                    </div>
                    <div class="small text-muted">{{ t.stay_type }} — {{ t.total_units }} وحدة — {{ t.base_price }} ريال</div>
                  </div>
                  <div class="d-flex gap-1">
                    <button class="btn btn-outline-primary btn-sm" @click="startEdit(t)">تعديل</button>
                    <button class="btn btn-outline-secondary btn-sm" @click="toggleType(t)">
                      {{ t.is_active ? 'إيقاف' : 'تفعيل' }}
                    </button>
                  </div>
                </div>

                <div v-else>
                  <div class="row g-2 mb-2">
                    <div class="col-6"><input v-model="editForm.name" class="form-control form-control-sm" /></div>
                    <div class="col-6">
                      <select v-model="editForm.stay_type" class="form-select form-select-sm">
                        <option value="room">غرفة</option><option value="apartment">شقة</option><option value="suite">جناح</option><option value="hall">قاعة</option>
                      </select>
                    </div>
                    <div class="col-4"><input v-model.number="editForm.max_adults" type="number" min="1" class="form-control form-control-sm" /></div>
                    <div class="col-4"><input v-model.number="editForm.max_children" type="number" min="0" class="form-control form-control-sm" /></div>
                    <div class="col-4"><input v-model.number="editForm.total_units" type="number" min="1" class="form-control form-control-sm" /></div>
                    <div class="col-8"><input v-model.number="editForm.base_price" type="number" min="0" class="form-control form-control-sm" /></div>
                    <div class="col-4 d-flex gap-1">
                      <button class="btn btn-success btn-sm flex-fill" @click="saveEdit(t)">حفظ</button>
                      <button class="btn btn-light btn-sm flex-fill" @click="editingId = null">إلغاء</button>
                    </div>
                  </div>
                </div>
              </div>

              <hr />
              <h6>إضافة نوع إقامة</h6>
              <div class="row g-2">
                <div class="col-6"><input v-model="roomForm.name" class="form-control" placeholder="الاسم" /></div>
                <div class="col-6">
                  <select v-model="roomForm.stay_type" class="form-select">
                    <option value="room">غرفة</option><option value="apartment">شقة</option><option value="suite">جناح</option><option value="hall">قاعة</option>
                  </select>
                </div>
                <div class="col-4"><input v-model.number="roomForm.max_adults" type="number" min="1" class="form-control" title="بالغون" /></div>
                <div class="col-4"><input v-model.number="roomForm.max_children" type="number" min="0" class="form-control" title="أطفال" /></div>
                <div class="col-4"><input v-model.number="roomForm.total_units" type="number" min="1" class="form-control" title="الوحدات" /></div>
                <div class="col-6"><input v-model.number="roomForm.base_price" type="number" min="0" class="form-control" placeholder="السعر" /></div>
                <div class="col-6">
                  <button class="btn btn-primary w-100" :disabled="savingRoom" @click="saveRoom">إضافة</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-else class="alert alert-danger">الفندق غير موجود.</div>
  </div>
</template>