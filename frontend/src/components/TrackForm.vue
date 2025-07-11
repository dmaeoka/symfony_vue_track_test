<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';
import { parseDuration, formatSecondsToMMSS } from '../utils';
import { useTrackStore } from '../stores/trackStore';
import Loading from './Loading.vue';

const trackStore = useTrackStore();

const emit = defineEmits<{
  (e: 'submitted'): void;
  (e: 'closemodal'): void;
}>();

interface Track {
  id?: number;
  title: string;
  artist: string;
  duration: number;
  isrc?: string;
}

interface ValidationErrorResponse {
  errors: { [key: string]: string };
}

const props = defineProps<{
  title: string;
  track?: Track | null;
}>();

const title = ref<string>('');
const artist = ref<string>('');
const duration = ref<number | null>(null);
const formattedDuration = ref<string>('');
const isrc = ref<string>('');
const isSubmitting = ref<boolean>(false);
const errorResponse = ref<ValidationErrorResponse | null>(null);

const resetForm = () => {
  title.value = '';
  artist.value = '';
  duration.value = null;
  formattedDuration.value = '';
  isrc.value = '';
};

const closeAlert = () => {
  errorResponse.value = null;
};

const closeModal = () => {
  emit('closemodal');
};

function setCustomPatternMessage(event: Event, message: string) {
  const input = event.target as HTMLInputElement;
  if (input.validity.patternMismatch) {
    input.setCustomValidity(message);
  }
}

function clearCustomValidity(event: Event) {
  const input = event.target as HTMLInputElement;
  input.setCustomValidity('');
}

const validateForm = (): boolean => {
  const e: { [key: string]: string } = {};

  if (!title.value?.trim()) {
    e.title = 'Please enter a value for the title field.';
  }

  if (!artist.value?.trim()) {
    e.artist = 'Please enter a value for the artist field.';
  }

  if (
    formattedDuration.value?.trim() &&
    !/^(\d{1,7}|[0-9]{1,2}:[0-5][0-9])$/.test(formattedDuration.value)
  ) {
    e.duration = 'Please enter a valid duration greater than 0.';
  }

  if (isrc.value && !/^[A-Z]{2}-[A-Z0-9]{3}-\d{2}-\d{5}$/.test(isrc.value)) {
    e.isrc = 'ISRC must follow the format XX-XXX-00-00000.';
  }

  if (Object.keys(e).length > 0) {
    errorResponse.value = { errors: e };
    return false;
  }

  errorResponse.value = null;
  return true;
};

const handleSubmit = async () => {
  isSubmitting.value = true;

  if (!validateForm()) {
    isSubmitting.value = false;
    return;
  }

  const track: Track = {
    title: title.value?.trim() || '',
    artist: artist.value?.trim() || '',
    duration: parseDuration(formattedDuration.value) ?? 0,
    isrc: isrc.value?.trim() || '',
  };

  try {
    if (props.track?.id) {
      await trackStore.updateTrack(props.track.id, track);
    } else {
      await trackStore.addTrack(track);
    }

    resetForm();
    emit('submitted');
  } catch (err: any) {
    if (err.response?.data?.errors) {
      errorResponse.value = { errors: err.response.data.errors };
    } else {
      console.error('Unexpected error:', err);
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  if (props.track) {
    title.value = props.track.title;
    artist.value = props.track.artist;
    duration.value = props.track.duration;
    formattedDuration.value = formatSecondsToMMSS(duration.value);
    isrc.value = props.track.isrc ?? '';
  } else {
    resetForm();
  }
});

watch(
  () => props.track,
  (newTrack) => {
    if (newTrack) {
      title.value = newTrack.title;
      artist.value = newTrack.artist;
      duration.value = newTrack.duration;
      formattedDuration.value = formatSecondsToMMSS(duration.value);
      isrc.value = newTrack.isrc ?? '';
    } else {
      resetForm();
    }
  },
  { immediate: true }
);
</script>

<template>
  <div class="relative w-full max-w-2xl max-h-full">
    <form class="relative bg-white rounded-lg shadow-sm" @submit.prevent="handleSubmit">
      <div class="flex items-start justify-between p-5 border-b border-gray-200 rounded-t">
        <h3 class="text-xl font-semibold text-gray-900 lg:text-2xl">{{ props.title }}</h3>
        <button type="button" @click="closeModal" class="cursor-pointer text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm p-1.5 ml-auto inline-flex items-center">
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
          </svg>
        </button>
      </div>
      <div v-if="errorResponse" class="p-6 pb-0">
        <div class="flex items-center p-4 text-red-800 rounded-lg bg-red-50" role="alert">
          <span class="sr-only">Error</span>
          <div>
            <span class="font-medium">Ensure that these requirements are met:</span>
            <ul class="mt-1.5 list-disc list-inside">
              <li v-for="(message, field) in errorResponse?.errors" :key="field">{{ message }}</li>
            </ul>
          </div>
          <button type="button" @click="closeAlert" class="ms-auto -mx-1.5 -my-1.5 bg-red-50 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8" aria-label="Close">
            <span class="sr-only">Close</span>
            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
              <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/>
            </svg>
          </button>
        </div>
      </div>
      <div v-if="!isSubmitting">
        <div class="p-6 space-y-6">
          <div>
            <label for="title" class="block mb-2 text-sm font-medium text-gray-900">Title</label>
            <input type="text" id="title" v-model="title" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Track title..." />
          </div>
          <div>
            <label for="artist" class="block mb-2 text-sm font-medium text-gray-900">Artist</label>
            <input type="text" id="artist" v-model="artist" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="Artist name..." required />
          </div>
          <div>
            <label for="duration" class="block mb-2 text-sm font-medium text-gray-900">Duration</label>
            <input type="text" id="duration" v-model="formattedDuration" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required pattern="^(\d{1,7}|[0-9]{1,2}:[0-5][0-9])$" @input="clearCustomValidity($event)" @invalid="setCustomPatternMessage($event, 'Duration must be in the format mm:ss or 120, e.g., 03:45')" />
          </div>
          <div>
            <label for="isrc" class="block mb-2 text-sm font-medium text-gray-900">ISRC</label>
            <input type="text" id="isrc" v-model="isrc" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" placeholder="XX-XXX-00-00000" pattern="^[A-Z]{2}-[A-Z0-9]{3}-\d{2}-\d{5}$" @input="clearCustomValidity($event)" @invalid="setCustomPatternMessage($event, 'ISRC must be in the format XX-XXX-00-00000')" />
          </div>
        </div>
        <div class="flex items-center p-6 space-x-2 border-t border-gray-200 rounded-b">
          <button type="submit" class="text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Save track</button>
          <button type="button" class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-blue-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900 focus:z-10" @click="closeModal">Cancel</button>
        </div>
      </div>
      <Loading v-else />
    </form>
  </div>
</template>
