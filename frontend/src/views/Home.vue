<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Modal } from 'flowbite';
import TrackForm from '../components/TrackForm.vue';
import Row from '../components/Row.vue';
import AddTrack from '../components/AddTrack.vue';
import { useTrackStore } from '../stores/trackStore';

interface Track {
  id: number
  title: string
  artist: string
  duration: number
  isrc?: string
}

const route = useRoute();
const router = useRouter();
const trackStore = useTrackStore();

const trackToEdit = ref<Track | null>(null);
const modalElement = ref<HTMLElement | null>(null);
let modalInstance: Modal | null = null;

const openEditModal = (track: Track) => {
  trackToEdit.value = track;
  modalInstance?.show();
};

const loadTracks = async () => {
  const page = parseInt(route.query.page as string) || 1;
  await trackStore.fetchTracks(page);

  if (trackStore.tracks.length === 0 && page > 1) {
    router.push({ path: '/track/', query: { page: page - 1 } });
  }
};

onMounted(() => {
  loadTracks();

  if (modalElement.value) {
    modalInstance = new Modal(modalElement.value, {
      backdrop: 'dynamic',
      closable: true,
    });
  }
});

watch(
  () => route.query.page,
  () => {
    loadTracks();
  }
);
</script>

<template>
  <section class="p-3 sm:p-5">
    <div class="max-w-screen-xl px-4 mx-auto lg:px-12">
      <div v-if="trackStore.isLoading">Loading...</div>
      <div v-else>
        <div class="relative overflow-hidden bg-white shadow-md sm:rounded-lg">
          <div class="flex items-center justify-between p-4">
            <AddTrack @submitted="loadTracks" />
          </div>

          <!-- Table -->
          <div class="overflow-x-auto">
            <table class="w-full text-sm text-left text-gray-500">
              <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                <tr>
                  <th class="px-4 py-3">Title</th>
                  <th class="px-4 py-3" width="25%">Artist</th>
                  <th class="px-4 py-3" width="10%">Duration</th>
                  <th class="px-4 py-3" width="8%">ISRC</th>
                  <th class="px-4 py-3" width="50">
                    <span class="sr-only">Actions</span>
                  </th>
                </tr>
              </thead>
              <tbody>
                <Row
                  v-for="track in trackStore.tracks"
                  :key="track.id"
                  :track="track"
                  @submitted="loadTracks"
                  @edit="openEditModal"
                />
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <nav
            v-if="trackStore.pagination"
            class="flex items-center justify-between p-4"
            aria-label="Table navigation"
          >
            <span class="text-sm text-gray-500">
              Showing <span class="font-semibold text-gray-900">{{ trackStore.rangeStart }}</span>
              to <span class="font-semibold text-gray-900">{{ trackStore.rangeEnd }}</span> of
              <span class="font-semibold text-gray-900">{{ trackStore.pagination.total_items }}</span>
            </span>

            <ul class="inline-flex -space-x-px">
              <li>
                <router-link
                  :to="`/track/?page=${trackStore.pagination.current_page - 1}`"
                  :class="[
                    'flex items-center justify-center h-full py-1.5 px-3 ml-0 text-gray-500 bg-white rounded-l-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700',
                    trackStore.pagination.current_page <= 1
                      ? 'opacity-50 cursor-not-allowed pointer-events-none'
                      : ''
                  ]"
                >
                  <span class="sr-only">Previous</span>
                    <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </router-link>
              </li>
              <li>
                <router-link
                  :to="`/track/?page=${trackStore.pagination.current_page + 1}`"
                  :class="[
                    'flex items-center justify-center h-full py-1.5 px-3 leading-tight text-gray-500 bg-white rounded-r-lg border border-gray-300 hover:bg-gray-100 hover:text-gray-700',
                    (trackStore.pagination.current_page >= trackStore.pagination.total_pages)
                      ? 'opacity-50 cursor-not-allowed pointer-events-none'
                      : ''
                  ]"
                >
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
    </div>

    <!-- Modal for Edit -->
    <div
      ref="modalElement"
      tabindex="-1"
      aria-hidden="true"
      class="fixed inset-0 z-50 hidden w-full h-full max-h-full p-4 overflow-x-hidden overflow-y-auto"
    >
      <TrackForm
        v-if="trackToEdit"
        title="Edit Track"
        :track="trackToEdit"
        @closemodal="() => modalInstance?.hide()"
        @submitted="() => { modalInstance?.hide(); loadTracks(); }"
      />
    </div>
  </section>
</template>
