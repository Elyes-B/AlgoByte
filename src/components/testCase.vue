<template>
  <!-- Test Case Card -->
    <div :class="`btn btn-dark text-start shadow-sm d-flex mb-3 justify-content-center ${props.testCase.status === 'passed' ? 'border border-success' : props.testCase.status === 'failed' ? 'border border-danger' : ''}`">
  <p class="mb-0 small opacity-75">
    <span data-bs-toggle="modal" :data-bs-target="`#testCaseEditForm${props.testCase.id}`">testcase {{i}}: {{ props.testCase.inputs.join(', ') }} => output: {{ props.testCase.output }}</span>
    <span class="btn bi bi-x-lg text-danger m-1" @click.stop.prevent="removeTestCase"></span>
  </p>
</div>

<!-- test case edit modal -->
<Teleport to="body"> <!-- we added this to solve a problem with multiple modals in the same page -->
<div class="modal fade" :id="`testCaseEditForm${props.testCase.id}`" tabindex="-1" aria-labelledby="testCaseEditFormLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between">
        <h5 class="modal-title" id="testCaseEditFormLabel">Edit Test Case</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="forEachInput">
                <div v-for="i in numberOfInputs" :key="i" class="mb-3"> <!-- asks for input values -->
                    <label for="input">{{ props.inputsNames[i - 1] }} values (datatype: {{ props.InputsDataType[i - 1] }})</label>
                <input type="text" class="form-control" :id="`input${i}`" :placeholder="`Enter expected input ${i} for this test case`" @change="emitChange" v-model="props.testCase.inputs[i - 1]">
                </div>
            </div>
            <div class="mb-3"> <!-- asks for output value -->
                <label for="output">output values (datatype: {{props.outputDataType}})</label>
                <input type="text" class="form-control" id="output" :placeholder="`Enter expected output for this test case`" @change="emitChange" v-model="props.testCase.output">
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>
</Teleport>
    </template>

<script setup>
import { onMounted } from 'vue';


const emit = defineEmits(['emitToDeleteToTestCaseForm', 'emitToChangeToTestCaseForm'])
const props = defineProps({ /* props needed for the display */
  testCase: {
    type: Object,
    default: () => 0,
    required: true
  },
  inputsNames: {
    type: Array,
    default: () => [],
    required: true
  },
  InputsDataType: {
    type: Array,
    default: () => [],
    required: true
  },
  outputDataType: {
    type: String,
    default: '',
    required: true
  }
})

onMounted(() => { /* transforms empty strings to "Null" */
  for (let i =0;i<props.testCase.inputs.length;i++){
        if(props.testCase.inputs[i] === ''){
            props.testCase.inputs[i] = "Null"
        }
  }
  if(props.testCase.output === ''){
        props.testCase.output = "Null"
  }
});


const emitChange = () =>{
    let validChange = true /* we use this boolean to prevent the user from adding empty test cases */
    for (let i =0;i<props.testCase.inputs.length;i++){
        if(props.testCase.inputs[i] === ''){
            props.testCase.inputs[i] = "Null"
            validChange = false
            break
        }
    }
    if(props.testCase.output === ''){
        props.testCase.output = "Null"
        validChange = false
    }
    if(validChange){ /* otherwise we send an emit to the form then to editor page */
        emit('emitToChangeToTestCaseForm',props.testCase,props.testCase.id)
    }
}

const numberOfInputs = props.testCase.inputs.length

const removeTestCase = () =>{
    emit('emitToDeleteToTestCaseForm', props.testCase) /* we send an emit after the user clicks on the red x icon */
}
</script>