<script setup lang="ts">
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;
import axios from 'axios';
import { ref, watch, onMounted } from 'vue';
import TrackForm from '../components/TrackForm.vue';
import Row from '../components/Row.vue';
import AddTrack from '../components/AddTrack.vue';
import { useRoute } from 'vue-router'
import { Modal } from 'flowbite';

interface Track {
  id: number;
  title: string;
  artist: string;
  duration: number;
  isrc?: string;
}

interface Pagination {
  current_page: number;
  total_items: number;
  items_per_page: number;
  total_pages: number;
}
const route = useRoute()
const tracks = ref<Track[]>([])
const trackToEdit = ref<Track | null>(null)
const modalElement = ref<HTMLElement | null>(null);
let modalInstance: Modal | null = null
const pagination = ref<Pagination | null>({
  current_page: 1,
  total_items: 0,
  items_per_page: 0,
  total_pages: 0
})
const currentPage = ref<number>(1);

const fetchTracks = async () => {
  const page = parseInt(route.query.page as string) || 1
  const response = await axios.get(`${API_BASE_URL}/tracks?page=${page}`)
  tracks.value = response.data.items || [];
  pagination.value = response.data.pagination;
  currentPage.value = pagination.value?.current_page ?? 1;
}

const openEditModal = (track: Track) => {
  trackToEdit.value = track;
  modalInstance?.show()
}

onMounted(() => {
  fetchTracks();

  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value, {
      backdrop: 'dynamic',
      closable: true,
    });
  }
});
watch(() => route.query.page, fetchTracks);
</script>

<template>
  <section class="p-3 sm:p-5">
    <div class="max-w-screen-xl px-4 mx-auto lg:px-12">
      <div class="relative overflow-hidden bg-white shadow-md sm:rounded-lg">
        <div class="flex flex-col items-center justify-between p-4 space-y-3 md:flex-row md:space-y-0 md:space-x-4">
          <div class="flex flex-col items-stretch justify-end flex-shrink-0 w-full space-y-2 md:w-auto md:flex-row md:space-y-0 md:items-center md:space-x-3">
            <div class="flex items-center w-full space-x-3 md:w-auto">
              <AddTrack @submitted="fetchTracks" />
            </div>
          </div>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-sm text-left text-gray-500">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
              <tr>
                <th scope="col" class="px-4 py-3">Title</th>
                <th scope="col" class="px-4 py-3" width="25%">Artist</th>
                <th scope="col" class="px-4 py-3" width="10%">Duration</th>
                <th scope="col" class="px-4 py-3" width="8%">ISRC</th>
                <th scope="col" class="px-4 py-3" width="50">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody>
              <Row
                v-for="track in tracks"
                :key="track.id"
                :track="track"
                @submitted="fetchTracks"
                @edit="openEditModal"/>
            </tbody>
          </table>
        </div>
        <nav v-if="pagination" class="flex flex-col items-start justify-between p-4 space-y-3 md:flex-row md:items-center md:space-y-0" aria-label="Table navigation">
          <span class="text-sm font-normal text-gray-500">
            Showing
            <span class="font-semibold text-gray-900">{{ (pagination.current_page * pagination.items_per_page) / pagination.items_per_page }}-{{ (pagination.items_per_page > pagination.total_items) ? pagination.total_items : pagination.items_per_page }}</span>
            of
            <span class="font-semibold text-gray-900">{{ pagination.total_items }}</span>
          </span>
          <ul class="inline-flex items-stretch -space-x-px">
              <li>
                  <router-link
                    :to="`/track/?page=${pagination.current_page - 1}`"
                    :class="[
                      'flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700',
                      pagination.current_page <= 1
                        ? 'opacity-50 cursor-not-allowed pointer-events-none'
                        : ''
                    ]"
                    :aria-disabled="pagination.current_page <= 1">
                    <span class="sr-only">Previous</span>
                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                  </router-link>
              </li>
              <li>
                  <router-link
                    :to="`/track/?page=${pagination.current_page + 1}`"
                    :class="[
                      'flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700',
                      (pagination.current_page >= pagination.total_pages)
                        ? 'opacity-50 cursor-not-allowed pointer-events-none'
                        : ''
                    ]"
                    :aria-disabled="pagination.current_page >= pagination.total_pages">
                    <span class="sr-only">Next</span>
                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                  </router-link>
              </li>
          </ul>
        </nav>
      </div>
    </div>

    <div ref="modalElement" tabindex="-1" aria-hidden="true" class="fixed top-0 left-0 right-0 z-50 hidden w-full h-full max-h-full p-4 overflow-x-hidden overflow-y-auto md:inset-0">
      <TrackForm
        v-if="trackToEdit"
        title="Edit Track"
        :track="trackToEdit"
        @closemodal="() => { modalInstance?.hide(); }"
        @submitted="() => { modalInstance?.hide(); fetchTracks(); }" />
    </div>
  </section>
</template>
