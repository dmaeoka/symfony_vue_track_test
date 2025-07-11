<script setup lang="ts">
import TrackForm from '../components/TrackForm.vue';
import { ref, onMounted } from 'vue';
import { Modal } from 'flowbite';
const emit = defineEmits<{
  (e: 'submitted'): void
}>();

const modalElement = ref<HTMLElement | null>(null);
let modalInstance: Modal | null = null

onMounted(() => {
  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value, {
      backdrop: 'dynamic',
      closable: true,
    });
  }
})

const openModal = () => {
  modalInstance?.show()
}

const closeModal = () => {
  modalInstance?.hide()
}

const submitModal = () => {
  emit('submitted');
  closeModal();
}
</script>

<template>
  <div class="w-full md:w-auto">
    <button id="button" type="button" @click="openModal" class="w-full cursor-pointer md:w-auto flex items-center justify-center py-2 px-4 text-sm font-medium text-gray-900 focus:outline-none bg-white rounded-lg border border-gray-200 hover:bg-gray-100 hover:text-primary-700 focus:z-10 focus:ring-4 focus:ring-gray-200">
      <svg class="w-5 h-5 mr-2" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path clip-rule="evenodd" fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" />
      </svg>
      Add track
    </button>
    <div ref="modalElement" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-full max-h-full">
      <TrackForm title="Add Track" @closemodal="closeModal" @submitted="submitModal" />
    </div>
  </div>
</template>
