<template>
  <!-- this display is placed above the terminal -->
  <div class="d-flex">
  <text class="col-1">Number of test cases: {{ numberOfTestCases }}</text> <!-- we showcase the number of test cases -->
  <div class="col-10">
  <div v-if="numberOfTestCases>5"> <!-- if there are more than 5 test cases  the 6th transforms into a button  that shows all the testcases-->
    <div v-for="i in 5" :key="i" style="display: inline-block;margin-right:20px;">
      <testCase :inputsNames="props.inputsNames" :InputsDataType="props.InputsDataType" :outputDataType="props.outputDataType" :testCase="listOfTestCases[i-1]" @emitToDeleteToTestCaseForm="emitToDeleteToEditor" @emitToChangeToTestCaseForm="emitToChangeToEditor"/>
    </div>
    <testCasesShowcase :listOfTestCases="props.listOfTestCases" :numberOfTestCases="props.numberOfTestCases"  @emitDeleteToForm="emitToDeleteToEditor"/> <!-- the button to  show all test cases in a modal -->
  </div>

  <div v-else>
    <div v-for="i in numberOfTestCases" :key="i" style="display: inline-block; margin-right:20px;"> <!-- otherwise if there are less we show them all without the need of the button -->
      <testCase :inputsNames="props.inputsNames" :InputsDataType="props.InputsDataType" :outputDataType="props.outputDataType"  @emitToDeleteToTestCaseForm="emitToDeleteToEditor" :testCase="listOfTestCases[i-1]" @emitToChangeToTestCaseForm="emitToChangeToEditor"/>
    </div>
  </div>
  </div>
<button type="button" class="btn btn-primary col-1" data-bs-toggle="modal" data-bs-target="#exampleModalCenter"> <!-- button to open the create new test case form modal -->
  Add test case
</button>
</div>

<!-- create new test cases modal -->
 <div class="container">
<div class="modal fade" id="exampleModalCenter" tabindex="-1" aria-labelledby="exampleModalToggleLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between">
        <h5 class="modal-title" id="exampleModalToggleLabel">Creating new test case</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
            <div id="forEachInput">
                <div v-for="i in numberOfInputs" :key="i" class="mb-3"> <!-- for each input  ask the user to provide a value -->
                    <label for="input">input {{ i }} values (datatype: {{ InputsDataType[i - 1] }})</label>
                <input type="text" class="form-control" :id="`input${i}`" :placeholder="`Enter expected input ${i} for this test case`" v-model="listOfInputs[i - 1]">
                </div>
            </div>
            <div class="mb-3">
                <label for="output">output values (datatype: {{ outputDataType }})</label>
                <input type="text" class="form-control" id="output" :placeholder="`Enter expected output for this test case`" v-model="outputValue">
            </div>

            
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button @click="submitForm" class="btn btn-primary" data-bs-dismiss="modal">Submit</button>
      </div>
    </div>
  </div>
</div>
</div>
</template>

<script setup>
import {ref} from 'vue'
import testCasesShowcase from './testCasesShowcase.vue'
import testCase from './testCase.vue'

const emit = defineEmits(['addTestCase', 'removeTestCase', 'changeTestCase'])

/* 3 emits one for submit the other for delete and the last for edit */

const submitForm = () =>{
    emit('addTestCase', {inputs: listOfInputs.value, output: outputValue.value})
}

const emitToDeleteToEditor = (testCase) =>{ //takes the emit signal  from  the test case component and sends it to the editor page
  emit('removeTestCase', testCase)
}

const emitToChangeToEditor = (testCase,id) =>{ //takes the emit signal  from  the test case component and sends it to the editor page
  emit('changeTestCase',testCase,id)
}


const props = defineProps({ /* props needed for the form */
  listOfTestCases: {
    type: Array,
    default: () => 0
  },
  numberOfTestCases: {  
    type: Number,
    default: () => 0
  },
  numberOfInputs:{
    type: Number,
    default: () => 0
  },
  inputsNames:{
    type: Array,
    default: () => []
  },
  InputsDataType:{
    type: Array,
    default: () => []
  },
  outputDataType:{
    type: String,
    default: () => 'int'
  }
})

const listOfInputs = ref(Array(props.numberOfInputs).fill(""));/* we create a separate instance of inputs list so that it can be modified without affecting the original list */
const outputValue = ref("");



</script>