<template>
  <button type="button" class="btn btn-success btn-sm px-4 py-1 fw-bold d-flex align-items-center gap-2 shadow run-btn rounded-pill border-0" data-bs-toggle="modal" data-bs-target="#previousSolutionsModal">
    <span class="d-none d-sm-inline">Previous Solutions</span>
    <span class="d-inline d-sm-none">Solutions</span>
  </button>

  <div class="modal fade" id="previousSolutionsModal" tabindex="-1" aria-labelledby="previousSolutionsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="previousSolutionsModalLabel">Saved Solutions</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <ul class="list-group">
            <li v-for="(solution, index) in savedSolutions" :key="index" class="list-group-item d-flex justify-content-between align-items-center">
              <div>
                <!-- each solution contains a language,and code -->
                <strong>Language:</strong> {{ solution.language }}<br>
                <strong>Code:</strong>
                <pre class="bg-light p-2 rounded mt-1">{{ solution.code }}</pre>
              </div>
              <div>
                <button class="btn btn-danger btn-sm me-2" @click="deleteSolution(index)">Delete</button>
                <button class="btn btn-info btn-sm" @click="importSolution(index)">Import</button>
              </div>
            </li>
            <li v-if="savedSolutions.length === 0" class="list-group-item">
              No solutions saved yet.
            </li>
          </ul>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
  savedSolutions: {
    type: Array,
    default: () => []
  }
});
//this component is responsible for showing the saved solutions in a modal and allowing the user to delete or import them to the editor
const emit = defineEmits(['deleteSolution', 'importSolution']);

const deleteSolution = (index) => {
  emit('deleteSolution', index);
};

//importing a solution means changing the current code and language in the editor to the code and language of the imported solution
const importSolution = (index) => {
  emit('importSolution', index);
};
</script>