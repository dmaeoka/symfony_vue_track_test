<script setup lang="ts">
import axios from 'axios'
import { ref, onMounted } from 'vue';
import { Dropdown } from 'flowbite';
import { formatSecondsToMMSS } from '../utils';
const emit = defineEmits<{
  (e: 'submitted'): void
  (e: 'edit', track: Track): void
}>()

interface Message {
  message: number;
}

interface Track {
  id: number;
  title: string;
  artist: string;
  duration: number;
  isrc?: string;
}

const response = ref<Message | null>(null);
const dropdownElement = ref<HTMLElement | null>(null);
const dropdownTriggerElement = ref<HTMLElement | null>(null);
let dropdownInstance: Dropdown | null = null

const props = defineProps<{
  track: Track
}>()

const deleteTrack = async(id: number) => {
  const confirmDelete: Boolean = window.confirm("Do you want to delete this track?");
  if (confirmDelete) {
    dropdownInstance?.hide();
    response.value = await axios.delete(`http://127.0.0.1:8000/api/tracks/${id}`);
    emit('submitted');
  } else {
    dropdownInstance?.toggle();
  }
}

const editTrack = () => {
  emit('edit', props.track)
  dropdownInstance?.hide()
}

onMounted(() => {
  try {
    if (dropdownElement.value) {
      dropdownInstance = new Dropdown(dropdownElement.value, dropdownTriggerElement.value);
    }
  } catch (err) {
    console.error('Error in Row onMounted:', err)
  }
});
</script>

<template>
  <tr class="border-b border-gray-100">
    <th scope="row" class="px-4 py-3 font-medium text-gray-900">{{ props.track.title }}</th>
    <td class="px-4 py-3">{{ props.track.artist }}</td>
    <td class="px-4 py-3 whitespace-nowrap">{{ formatSecondsToMMSS(props.track.duration) }}</td>
    <td class="px-4 py-3 whitespace-nowrap">{{ props.track.isrc || '—' }}</td>
    <td class="px-4 py-3 flex items-center justify-end">
      <button ref="dropdownTriggerElement" class="inline-flex items-center p-0.5 text-sm font-medium text-center text-gray-500 hover:text-gray-800 rounded-lg focus:outline-none cursor-pointer" type="button">
        <svg class="w-5 h-5" aria-hidden="true" fill="currentColor" viewbox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
          <path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM16 12a2 2 0 100-4 2 2 0 000 4z" />
        </svg>
      </button>
      <div ref="dropdownElement" class="hidden z-10 w-44 bg-white rounded divide-y divide-gray-100 shadow">
        <ul class="py-1 text-sm text-gray-700">
          <li>
            <a href="#" @click.prevent="editTrack()" class="block py-2 px-4 hover:bg-gray-100">Edit</a>
          </li>
          <li>
            <a href="#" @click.prevent="deleteTrack(props.track.id)" class="block py-2 px-4 hover:bg-gray-100">Delete</a>
          </li>
        </ul>
      </div>
    </td>
  </tr>
</template>
