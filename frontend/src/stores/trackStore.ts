import { defineStore } from 'pinia'
import axios from 'axios'

interface Track {
  id?: number
  title: string
  artist: string
  duration: number
  isrc?: string
}

interface Pagination {
  current_page: number
  total_items: number
  items_per_page: number
  total_pages: number
}

export const useTrackStore = defineStore('trackStore', {
  state: () => ({
    tracks: [] as Track[],
    isLoading: false,
    pagination: null as Pagination | null,
  }),
  getters: {
    rangeStart(state) {
      if (!state.pagination) return 0
      return (state.pagination.current_page - 1) * state.pagination.items_per_page + 1
    },
    rangeEnd(state) {
      if (!state.pagination) return 0
      const end = state.pagination.current_page * state.pagination.items_per_page
      return end > state.pagination.total_items ? state.pagination.total_items : end
    },
  },
  actions: {
    async fetchTracks(page: number = 1) {
      this.isLoading = true
      try {
        const response = await axios.get(`${import.meta.env.VITE_API_BASE_URL}/tracks?page=${page}`)
        this.tracks = response.data?.items ?? []
        this.pagination = response.data?.pagination ?? null
      } catch (error) {
        console.error('Failed to fetch tracks:', error)
      } finally {
        this.isLoading = false
      }
    },
    async deleteTrack(id: number) {
      try {
        await axios.delete(`${import.meta.env.VITE_API_BASE_URL}/tracks/${id}`);
        this.tracks = this.tracks.filter(t => t.id !== id);
        const currentPage = this.pagination?.current_page ?? 1;
        const remainingItems = this.tracks.length;

        if (remainingItems === 0 && currentPage > 1) {
          await this.fetchTracks(currentPage - 1);
        } else {
          await this.fetchTracks(currentPage);
        }

      } catch (error) {
        console.error('Failed to delete track:', error);
      }
    },
    async addTrack(track: Track) {
      await axios.post(`${import.meta.env.VITE_API_BASE_URL}/tracks`, track);
    },
    async updateTrack(id: number, track: Track) {
      const res = await axios.put(`${import.meta.env.VITE_API_BASE_URL}/tracks/${id}`, track);
      const index = this.tracks.findIndex(t => t.id === id);
      if (index !== -1) this.tracks[index] = JSON.parse(res.data);
    }
  },
})
