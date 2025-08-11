<template>
  <div class="task-manager">
    <h1>Simple Task Management System</h1>

    <form @submit.prevent="saveTask" class="task-form" novalidate>
      <h2>{{ isEditing ? 'Edit Task' : 'Create New Task' }}</h2>

      <input
        type="text"
        v-model.trim="form.title"
        placeholder="Task Title"
        required
        :class="{ invalid: validationErrors.title }"
      />
      <div v-if="validationErrors.title" class="error-msg">{{ validationErrors.title }}</div>

      <textarea
        v-model.trim="form.description"
        placeholder="Task Description"
        rows="3"
      ></textarea>

      <input
        type="date"
        v-model="form.due_date"
        required
        :class="{ invalid: validationErrors.due_date }"
      />
      <div v-if="validationErrors.due_date" class="error-msg">{{ validationErrors.due_date }}</div>

      <div v-if="isEditing" class="checkbox-group">
        <label>
          <input type="checkbox" v-model="form.completed" />
          Completed
        </label>
      </div>

      <button type="submit" :disabled="loading">
        <span v-if="loading" class="spinner"></span>
        {{ isEditing ? 'Update Task' : 'Add Task' }}
      </button>
      <button type="button" @click="resetForm" v-if="isEditing" :disabled="loading">Cancel</button>
    </form>

    <table class="task-table" v-if="tasks.length">
      <thead>
        <tr>
          <th>Title</th>
          <th>Description</th>
          <th>Due Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="task in tasks"
          :key="task.id"
          :class="{ completed: task.completed }"
          tabindex="0"
        >
          <td>{{ task.title }}</td>
          <td>{{ task.description || '-' }}</td>
          <td>{{ formatDate(task.due_date) }}</td>
          <td>
            <span :class="['status', task.completed ? 'completed' : 'pending']">
              {{ task.completed ? 'Completed' : 'Pending' }}
            </span>
          </td>
          <td class="actions">
            <button
              @click="editTask(task)"
              class="edit-btn"
              :disabled="loading"
              aria-label="Edit Task"
            >Edit</button>
            <button
              @click="confirmDelete(task.id)"
              class="delete-btn"
              :disabled="loading"
              aria-label="Delete Task"
            >Delete</button>
          </td>
        </tr>
      </tbody>
    </table>
    <p v-else>No tasks found.</p>

    <!-- Delete Confirmation Modal -->
    <div v-if="showDeleteModal" class="modal-overlay" role="dialog" aria-modal="true">
      <div class="modal">
        <p>Are you sure you want to delete this task?</p>
        <div class="modal-actions">
          <button @click="deleteTask(deleteId)" :disabled="loading" class="delete-btn">Yes, Delete</button>
          <button @click="cancelDelete" :disabled="loading">Cancel</button>
        </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <transition name="toast-fade">
      <div v-if="toast.visible" :class="['toast', toast.type]">{{ toast.message }}</div>
    </transition>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      tasks: [],
      form: {
        id: null,
        title: '',
        description: '',
        due_date: '',
        completed: false
      },
      isEditing: false,
      loading: false,
      validationErrors: {},
      showDeleteModal: false,
      deleteId: null,
      toast: {
        visible: false,
        message: '',
        type: 'success' // 'success' or 'error'
      }
    };
  },
  created() {
    this.fetchTasks();
  },
  methods: {
    showToast(message, type = 'success') {
      this.toast = { visible: true, message, type };
      setTimeout(() => {
        this.toast.visible = false;
      }, 3000);
    },

    formatDate(dateStr) {
      if (!dateStr) return '-';
      const options = { year: 'numeric', month: 'short', day: 'numeric' };
      return new Date(dateStr).toLocaleDateString(undefined, options);
    },

    validateForm() {
      this.validationErrors = {};
      if (!this.form.title) this.validationErrors.title = 'Title is required.';
      if (!this.form.due_date) this.validationErrors.due_date = 'Due date is required.';
      return Object.keys(this.validationErrors).length === 0;
    },

    fetchTasks() {
      this.loading = true;
      axios
        .get('http://localhost:8000/api/tasks')
        .then(response => {
          this.tasks = response.data;
        })
        .catch(() => {
          this.showToast('Error fetching tasks.', 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    saveTask() {
      if (!this.validateForm()) return;

      this.loading = true;
      const apiCall = this.isEditing
        ? axios.put(`http://localhost:8000/api/tasks/${this.form.id}`, this.form)
        : axios.post('http://localhost:8000/api/tasks', this.form);

      apiCall
        .then(() => {
          this.showToast(this.isEditing ? 'Task updated!' : 'Task created!');
          this.fetchTasks();
          this.resetForm();
        })
        .catch(() => {
          this.showToast('Error saving task.', 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    editTask(task) {
      this.form = { ...task };
      this.isEditing = true;
      window.scrollTo({ top: 0, behavior: 'smooth' });
    },

    confirmDelete(id) {
      this.deleteId = id;
      this.showDeleteModal = true;
    },

    cancelDelete() {
      this.showDeleteModal = false;
      this.deleteId = null;
    },

    deleteTask(id) {
      this.loading = true;
      axios
        .delete(`http://localhost:8000/api/tasks/${id}`)
        .then(() => {
          this.showToast('Task deleted!');
          this.fetchTasks();
          this.cancelDelete();
        })
        .catch(() => {
          this.showToast('Error deleting task.', 'error');
        })
        .finally(() => {
          this.loading = false;
        });
    },

    resetForm() {
      this.form = {
        id: null,
        title: '',
        description: '',
        due_date: '',
        completed: false
      };
      this.isEditing = false;
      this.validationErrors = {};
    }
  }
};
</script>

<style scoped>
.task-manager {
  max-width: 800px;
  margin: 0 auto;
  font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
  padding: 20px;
  background: #f9f9f9;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgb(0 0 0 / 0.1);
}
h1 {
  text-align: center;
  color: #333;
}
.task-form {
  margin-bottom: 20px;
  background: white;
  padding: 20px;
  border-radius: 8px;
  border: 1px solid #ddd;
  box-shadow: inset 0 0 8px #eee;
}
.task-form input,
.task-form textarea {
  width: 100%;
  padding: 10px;
  margin-bottom: 12px;
  border: 1.5px solid #ccc;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
}
.task-form input:focus,
.task-form textarea:focus {
  outline: none;
  border-color: #007bff;
  box-shadow: 0 0 8px #007bff66;
}
.task-form input.invalid,
.task-form textarea.invalid {
  border-color: #dc3545;
}
.error-msg {
  color: #dc3545;
  font-size: 0.85rem;
  margin-top: -10px;
  margin-bottom: 10px;
}
.checkbox-group {
  margin-bottom: 12px;
  font-size: 1rem;
  user-select: none;
}
.task-form button {
  padding: 10px 18px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  border-radius: 5px;
  margin-right: 10px;
  background-color: #007bff;
  color: white;
  transition: background-color 0.25s;
}
.task-form button:disabled {
  background-color: #6c757d;
  cursor: not-allowed;
}
.task-form button:hover:not(:disabled) {
  background-color: #0056b3;
}
.task-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 0 6px rgb(0 0 0 / 0.1);
}
.task-table th,
.task-table td {
  border-bottom: 1px solid #ddd;
  padding: 10px 15px;
  text-align: left;
  vertical-align: middle;
}
.task-table th {
  background: #007bff;
  color: white;
  user-select: none;
}
.task-table tr:hover:not(.completed) {
  background-color: #f0f8ff;
  cursor: pointer;
}
.task-table tr.completed {
  text-decoration: line-through;
  color: #888;
  background-color: #f9f9f9;
}
.status {
  padding: 5px 12px;
  border-radius: 12px;
  font-weight: 600;
  font-size: 0.9rem;
  user-select: none;
}
.status.completed {
  background-color: #28a745;
  color: white;
}
.status.pending {
  background-color: #ffc107;
  color: #333;
}
.actions button {
  margin-right: 6px;
  padding: 6px 14px;
  font-weight: 600;
  border-radius: 4px;
  border: none;
  cursor: pointer;
  color: white;
  transition: background-color 0.3s;
  user-select: none;
}
.edit-btn {
  background-color: #17a2b8;
}
.edit-btn:hover:not(:disabled) {
  background-color: #117a8b;
}
.delete-btn {
  background-color: #dc3545;
}
.delete-btn:hover:not(:disabled) {
  background-color: #a71d2a;
}

/* Loading spinner */
.spinner {
  border: 3px solid #f3f3f3;
  border-top: 3px solid white;
  border-radius: 50%;
  width: 16px;
  height: 16px;
  display: inline-block;
  animation: spin 1s linear infinite;
  vertical-align: middle;
  margin-right: 8px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Modal styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0,0,0,0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 9999;
}
.modal {
  background: white;
  padding: 20px 30px;
  border-radius: 8px;
  max-width: 320px;
  text-align: center;
  box-shadow: 0 0 12px rgb(0 0 0 / 0.15);
  user-select: none;
}
.modal-actions {
  margin-top: 20px;
  display: flex;
  justify-content: center;
  gap: 12px;
}
.modal-actions button {
  padding: 8px 18px;
  border-radius: 5px;
  font-weight: 600;
  cursor: pointer;
  border: none;
  color: white;
  transition: background-color 0.25s;
}
.modal-actions button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}
.modal-actions .delete-btn {
  background-color: #dc3545;
}
.modal-actions .delete-btn:hover:not(:disabled) {
  background-color: #a71d2a;
}
.modal-actions button:not(.delete-btn) {
  background-color: #6c757d;
}
.modal-actions button:not(.delete-btn):hover:not(:disabled) {
  background-color: #565e64;
}

/* Toast notifications */
.toast {
  position: fixed;
  bottom: 30px;
  right: 30px;
  padding: 14px 22px;
  border-radius: 6px;
  color: white;
  font-weight: 600;
  user-select: none;
  box-shadow: 0 3px 10px rgb(0 0 0 / 0.15);
  z-index: 10000;
}
.toast.success {
  background-color: #28a745;
}
.toast.error {
  background-color: #dc3545;
}
.toast-fade-enter-active,
.toast-fade-leave-active {
  transition: opacity 0.4s;
}
.toast-fade-enter-from,
.toast-fade-leave-to {
  opacity: 0;
}
</style>
