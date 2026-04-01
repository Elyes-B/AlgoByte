<template>
  <!-- in case there are more than 5 test cases in form we will use this component -->
    <div style="display: inline-block;" data-bs-toggle="modal" href="#testCasesShowcase">
    <button type="button" class="btn btn-secondary d-block mt-3" >Show more</button> <!--button to open a modal that contains all  the test cases -->
    </div>
<!-- all test cases modal -->
<div class="modal fade" id="testCasesShowcase" tabindex="-1" aria-labelledby="testCasesShowcaseLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between">
        <h5 class="modal-title" id="testCasesShowcaseLabel">All testcases</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div v-for="tc in props.listOfTestCases" class="d-flex flex-row justify-content-center" :key="tc.id" style="display: inline-block; margin-right:20px;">
  <testCase :testCase="tc" @emitToDeleteToTestCaseForm="emitDeleteToForm"/>
  </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</template>

<script setup>
import testCase from './testCase.vue';

const props = defineProps({ // props needed for the display
  listOfTestCases: {
    type: Array,
    default: () => 0
  },
  numberOfTestCases: {
    type: Number,
    default: () => 0
  }
})

const emit = defineEmits(['emitDeleteToForm'])

const emitDeleteToForm = (testCase) =>{ //takes the emit signal  from  the test case component and sends it to the form component
    const showcase = document.getElementById('testCasesShowcase') //we get the modal element to hide it after deleting the test case
    const instance = window.bootstrap.Modal.getInstance(showcase) //we do this to prevent a crash that happens when we delete an element inside a modal without hiding it first
    instance.hide()
    emit('emitDeleteToForm', testCase)

}

</script>