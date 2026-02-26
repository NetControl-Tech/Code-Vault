  import { defineStore } from 'pinia'
import axios from '../services/api'

export const useDashboardStore = defineStore('dashboard', {
  state: () => ({
    statistics: null,
    loading: false,
    error: null,
    lateLetters: [],
    lateLettersLoading: false,
    lateLettersError: null,
    lateThresholdMeta: null,
    // Subjects count by category
    subjectsCountByCategory: [],
    subjectsCountLoading: false,
    subjectsCountError: null,

    // Non-Cooperative Letters Stats
    nonCooperativeCount: 0,
    nonCooperativeByCategory: [],
    nonCooperativeLoading: false,
    nonCooperativeError: null
  }),

  actions: {
    async fetchStatistics() {
      this.loading = true
      this.error = null
      try {
        const response = await axios.get('/dashboard/statistics')
        if (response.data.success) {
          this.statistics = response.data.data
          return { success: true, data: response.data.data }
        }
        return { success: false, message: 'فشل في تحميل الإحصائيات' }
      } catch (error) {
        console.error('Error fetching dashboard statistics:', error)
        this.error = error.response?.data?.message || 'حدث خطأ أثناء تحميل الإحصائيات'
        return { success: false, message: this.error }
      } finally {
        this.loading = false
      }
    },

    async fetchCalendar(start, end) {
      try {
        const response = await axios.get('/dashboard/calendar', { params: { start, end } })
        if (response.data.success) {
          return { success: true, data: response.data.data }
        }
        return { success: false }
      } catch (error) {
        console.error('Error fetching calendar:', error)
        return { success: false, message: error.message }
      }
    },

    async fetchLateLetters() {
      this.lateLettersLoading = true;
      this.lateLettersError = null;
      try {
        const response = await axios.get('/letters/late');
        if (response.data.success) {
          this.lateLetters = response.data.data;
          this.lateThresholdMeta = response.data.meta;
          return { success: true, data: response.data.data };
        }
      } catch (error) {
        console.error('Error fetching late letters:', error);
        this.lateLettersError = error.response?.data?.message || 'Failed to fetch late letters';
      } finally {
        this.lateLettersLoading = false;
      }
    },

    /**
     * Fetch subjects count by category using promise chaining only (no async/await).
     * @returns {Promise}
     */
    fetchSubjectsCountByCategory() {
      this.subjectsCountLoading = true
      this.subjectsCountError = null

      return axios.get('/dashboard/subjects-per-category')
        .then((response) => {
          if (response.data.success) {
            this.subjectsCountByCategory = response.data.data
            return { success: true, data: response.data.data }
          }
          return { success: false, message: 'فشل في تحميل عدد المواضيع' }
        })
        .catch((error) => {
          console.error('Error fetching subjects count by category:', error)
          this.subjectsCountError = error.response?.data?.message || 'حدث خطأ أثناء تحميل عدد المواضيع لكل تصنيف'
          return { success: false, message: this.subjectsCountError }
        })
        .finally(() => {
          this.subjectsCountLoading = false
        })
    },

    async fetchNonCooperativeStats() {
      this.nonCooperativeLoading = true
      this.nonCooperativeError = null
      
      try {
        const [countResponse, categoryResponse] = await Promise.all([
          axios.get('/dashboard/noncooperative/count'),
          axios.get('/dashboard/noncooperative/by-category')
        ])

        if (countResponse.data.success) {
          this.nonCooperativeCount = countResponse.data.data.count
        }

        if (categoryResponse.data.success) {
          this.nonCooperativeByCategory = categoryResponse.data.data
        }

        return { success: true }
      } catch (error) {
        console.error('Error fetching non-cooperative stats:', error)
        this.nonCooperativeError = error.response?.data?.message || 'Failed to load non-cooperative stats'
        return { success: false, message: this.nonCooperativeError }
      } finally {
        this.nonCooperativeLoading = false
      }
    }
  }
})

