<script setup>
import { computed, ref, watch } from 'vue'
import { VueMonacoEditor } from '@guolao/vue-monaco-editor'
import testCaseForm from './testCaseForm.vue'
import testCase from './testCase.vue'
import previousSolutions from './previousSolutions.vue'

/* Eclipse theme setup for Monaco Editor */
const eclipseTheme = {
  base: 'vs',
  inherit: true,
  rules: [
    { token: 'keyword', foreground: '7F0055', fontStyle: 'bold' },
    { token: 'comment', foreground: '3F7F5F' },
    { token: 'string', foreground: '2A00FF' },
    { token: 'number', foreground: '000000' }
  ],
  colors: {
    'editor.background': '#ffffff',
    'editor.foreground': '#000000'
  }
}

const handleBeforeMount = (monaco) => {
  monaco.editor.defineTheme('eclipse', eclipseTheme)
}

/* code execution section */

const functionName= 'solution'
const language = ref('typescript'); //the selected language
const numberOfInputs = ref(2)
const outputDataType = 'int' //for now pre selected in sprint 2 we will add an option to change it
const acceptedDataTypes = ['int', 'double', 'boolean', 'string'] //the supported data types in the site
const submissionList = ref([]) //a list that holds all the code submissions results (useful for sprint 2 to display the history of submissions)
const savedSolutions = ref([])

const inputsDataType = computed(() => { // since there are multiple inputs we turn it into an array of data types, the same thing about sprint 2 also applies here
    const dataTypes = []
    for (let i = 0; i < numberOfInputs.value; i++) {
        dataTypes.push('int')
    }
    return dataTypes
})

const inputsNames = computed(() => { //just to display input 1, input 2... in the test case form
    const names = []
    for (let i = 0; i < numberOfInputs.value; i++) {
        names.push(`input${i + 1}`)
    }
    return names
})

const fullFuntion = computed(() => {
  switch (language.value) {
    case 'typescript':
      return `function ${functionName}(${inputsNames.value.join(', ')}) {\n  // your code here\n}`

    case 'python':
      return `def ${functionName}(${inputsNames.value.join(', ')}):\n    # your code here\n`

    case 'java':
      let javaInputsDataType = []
      for (let i = 0; i < numberOfInputs.value; i++) {
          inputsDataType.value[i] == "string" ? javaInputsDataType.push("String") : javaInputsDataType.push(inputsDataType.value[i])
      }
      let javaOutputDataType = outputDataType === "string" ? "String" : outputDataType
      return `public ${javaOutputDataType} ${functionName}(${javaInputsDataType.map((type, index) => `${type} ${inputsNames.value[index]}`).join(', ')}) {\n  // your code here\n}`

    case 'c':
      let cInputsDataType = []
      for (let i = 0; i < numberOfInputs.value; i++) {
          inputsDataType.value[i] == "string" ? cInputsDataType.push("char*") : cInputsDataType.push(inputsDataType.value[i])
          cInputsDataType[i] == "boolean" ? cInputsDataType[i] = "int" : cInputsDataType[i] = cInputsDataType[i]
      }
      let cOutputDataType = outputDataType === "string" ? "char*" : outputDataType
      cOutputDataType == "boolean" ? cOutputDataType = "int" : cOutputDataType = cOutputDataType
  return `${cOutputDataType} solution(${cInputsDataType.map((type, index) => `${type} ${inputsNames.value[index]}`).join(', ')}) {\n  // your code here\n}`

    default:
      return `function ${functionName}(${inputsNames.value.join(', ')}): ${outputDataType} {\n  // your code here\n}`
  }
})



const languages = [ // the supported languages in the site
  { value: 'typescript', label: 'TypeScript' },
  { value: 'python', label: 'Python' },
  { value: 'java', label: 'Java' },
  { value: 'c', label: 'C' },
]

//variables declaration for editor and execution
const failureStatuses = new Set(['compile_error', 'runtime_error', 'runner_unavailable']) //determines how the code execution failed

const code = ref(fullFuntion.value) //the actual code that will be executed
const fontSize = ref(14); //the editor font sinze
const isSubmitting = ref(false); //a boolean to determine if the code is being executed
const statusMessage = ref(''); //a message to show the status of the last code execution (like completion)
const errorMessage = ref(''); //an error message that gets displayed to the console after failure
const lastSubmission = ref(null); //an object that holds the result of the last code execution, it has the following structure
const MatchedOutput = ref(true) //a boolean to determine if the output from piston matched the expected output from the test case
const failedTestCase = ref(null) //an object that holds the test case that failed in the last execution (useful for sprint 2 to display the failed test case details)

watch(language, () => {
  code.value = fullFuntion.value
})

const hasExecutionFailure = computed(() => {
  return lastSubmission.value ? failureStatuses.has(lastSubmission.value.status) : false //if the last submission  failed
})

const resultPanelClass = computed(() => {
  return (errorMessage.value || hasExecutionFailure.value || !MatchedOutput.value)  //displays either the success execution panel or the error panel
    ? 'submission-panel-error' //red background for error
    : 'submission-panel-success' //green background for success
})

const formatExecutionStatus = (status) => {
  return status ? status.replaceAll('_', ' ') : 'unknown' //just converts complie_error to compile error
}

const formatRuntimeLabel = (submission) => { //displays the runtime and the language used in the last submission
  if (!submission?.runtime) {
    return ''
  }

  return submission.runtime_version
    ? ` using ${submission.runtime} ${submission.runtime_version}`
    : ` using ${submission.runtime}`
}

const runCode = async () => {
  //variables declaration for the code before exeuction
  let initialCode = code.value //we save the initial code before execution to prevent the formatting for execution from changing the code in the editor
  errorMessage.value = ''
  lastSubmission.value = null
  failedTestCase.value = null
  MatchedOutput.value = true
  submissionList.value = [] //we reset the submission list to only keep the last execution result (useful for sprint 2 when we will display the history of submissions)

  if (!code.value.trim()) {
    errorMessage.value = 'Write some code before sending it to the API.' //if the editor is empty then we tell the user to wrote code
    return
  }

  isSubmitting.value = true //otherwise we send the code to the API and wait for the response
  try {
    for (let i =0;i<listOfTestCases.value.length;i++){
    listOfTestCases.value[i].MatchedOutput = null //we reset the matched output of the test case before execution to determine later if it passed or failed
    const wrappedCode = formatCodeForExecution(listOfTestCases.value[i], initialCode) //we format the code for execution by adding the function call with the test case inputs at the end of the code in the editor;
    const response = await fetch('/piston/api/v2/execute', { //api call to piston to execute the code
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        language: language.value,
        version: '*', // the star means use whatever version of the compiler you have installed (not specific)
        files: [{ content: wrappedCode }] //the content is the code in the editor (which is by default the template)
      })
    })

    const payload = await response.json().catch(() => null)

    if (!response.ok) {
      //in case piston did not accept the request as valid code
      throw new Error(payload?.message ?? 'Piston rejected the request.')
    }

    if (payload?.compile && payload.compile.code !== 0) {
      //in case an error occures within the code itself
      lastSubmission.value = {
        status: 'compile_error',
        stdout: null,
        stderr: payload.compile.stderr || null,
        compile_output: payload.compile.output || payload.compile.stderr || null,
        exit_code: payload.compile.code,
        runtime: payload.language,
        runtime_version: payload.version,
        signal: payload.compile.signal || null,
      }
      lastSubmission.value.status = 'Compilation failed.'
      return
    }

    // execution result here
    const run = payload?.run //the ? after the variables name means (if the variable is NULL dont try to execute the method, useful for preventing crashes)
    const didFail = run.code !== 0 || !!run.signal

    lastSubmission.value = {
      status: didFail ? 'runtime_error' : 'completed', //ternary operator: if the didfail is true then execute what if after the ? points otherwise execute what is after :
      stdout: run.stdout || null,
      stderr: run.stderr || null,
      compile_output: payload?.compile?.output || null,
      exit_code: run.code,
      runtime: payload.language,
      runtime_version: payload.version,
      signal: run.signal || null,
      wall_time: run.wall_time || null,
    }
    if(compareOutput(run.stdout, listOfTestCases.value[i].output, lastSubmission)){
      lastSubmission.value.status = didFail ? 'Code executed with errors.' : 'Code executed successfully.'
      listOfTestCases.value[i].status = didFail? 'failed' : 'passed'
      lastSubmission.value.MatchedOutput = true
      MatchedOutput.value = true
      submissionList.value.push(lastSubmission.value) //we add the submission result to the submission list
    } else {
      listOfTestCases.value[i].status = 'failed'
      lastSubmission.value.MatchedOutput = false
      MatchedOutput.value = false
      failedTestCase.value = listOfTestCases.value[i]
      break;
    }
  }
  } catch (error) {
    errorMessage.value = error instanceof Error ? error.message : 'Unable to reach Piston.' //in case we catch an error when trying to fetch the executoin we add an error message
  } finally {
    isSubmitting.value = false //always after fetch we set this to false
    code.value = initialCode //we reset the code in the editor to the initial code (before execution)
  }
  if (allTestCasesPassed.value){
    saveSolution() //if all test cases passed we save the solution automatically
  }
}

const totalExecutionTime = computed(() => {
  for (let i = 0; i < submissionList.value.length; i++) {
    if (submissionList.value[i].wall_time) {
      return submissionList.value[i].wall_time
    }
  }
  return null
})

const formatCodeForExecution = (testCase) => {
  let inputs = formatInputsForLanguage(testCase.inputs) //we format the inputs according to the language (for example we add "" around string inputs)
  switch(language.value) {
    case 'python':
      return `${code.value}\n\nprint(${functionName}(${inputs}))` 
    case 'java':
  return `public class Main {\n  ${code.value}\n\n  public static void main(String[] args) {\n    Main obj = new Main();\n    System.out.println(obj.${functionName}(${inputs}));\n  }\n}`
    case 'c':
      return `#include <stdio.h>\n${code.value} int main() { printf("%d", ${functionName}(${inputs})); return 0; }`
    default:
      return `${code.value}\nconsole.log(${functionName}(${inputs}))`
  }

}

const formatInputsForLanguage = (inputs) => {
  return inputs.map((input, i) => {
    const type = inputsDataType.value[i]
    if (type === 'string') return `"${input}"` 
    if (type === 'boolean' && language.value === 'python') {
      return input.trim().toLowerCase() === 'true' ? 'True' : 'False'
    }
    return input
  }).join(', ')
}

//simple font size management
const increaseFontSize = () => {
  if (fontSize.value < 36) fontSize.value += 2;
}

const decreaseFontSize = () => {
  if (fontSize.value > 10) fontSize.value -= 2;
}

const editorOptions = computed(() => ({
  fontSize: fontSize.value,
  automaticLayout: true
}))


//using prebuilt themes for the editor


const theme = ref('vs-dark')
const themes = [
  { value: 'vs-dark', label: 'Dark' },
  { value: 'vs', label: 'Light' },
  { value: 'hc-black', label: 'High Contrast' }
]

const isDarkTheme = computed(() => {
  return !['vs', 'eclipse'].includes(theme.value)
})

/* testcases section */

//
const numberOfTestCases = ref(0)
const listOfTestCases = ref([]) 


const addTestCase = (testCase) => { //when we add a test case from the testCaseForm component using emit we call this method
  // if the user left the form empty we change the emptiness to Null
  for (let i =0;i<testCase.inputs.length;i++){
        if(testCase.inputs[i] === ''){
            testCase.inputs[i] = "Null"
        }
  }
  if(testCase.output === ''){
        testCase.output = "Null" //this also applies here
  }
    testCase.id = Date.now() //we add an id to identify them (useful when using for loops with :key)
    testCase.status = 'not executed' //the default status of the test case before execution
    let testCaseCopy = JSON.parse(JSON.stringify(testCase)) //we send a copy not the real object
    //because if the user changes (for example an input value) it will change AUTOMATICALLY the test case before we even hit submit
    listOfTestCases.value.push(testCaseCopy)  
    numberOfTestCases.value++
}

const removeTestCase = (testCase) => {
  const index = listOfTestCases.value.indexOf(testCase)
  if (index > -1) {
    if (failedTestCase.value?.id === listOfTestCases.value[index].id) {
      failedTestCase.value = null
      MatchedOutput.value = true 
    }
    listOfTestCases.value.splice(index, 1)
    numberOfTestCases.value--
  }
}

const changeTestCase = (updatedTestCase,id) => {
  //an emit function from the testCase component to change a test case from the editor
    for (let i =0;i<listOfTestCases.value.length;i++){
        if(listOfTestCases.value[i].id === id){ //find the exact test case by id and change it with the updated one
            listOfTestCases.value[i] = updatedTestCase
            break
        }
    }
}

const allTestCasesPassed = computed(() => {
  for (let i = 0; i < listOfTestCases.value.length; i++) {
    if (listOfTestCases.value[i].status !== 'passed') {
      return false
    }
  }
  return listOfTestCases.value.length > 0 //if there are no test cases we
})

/* test case execution */


// before we compare values and decide whether the output matches piston we need to transform them from strings to their actual datatype
const transformValue = (value, dataType) => {
  switch (dataType.toLowerCase().trim()) {
    case 'int':
      return parseInt(value, 10)

    case 'float':
      return parseFloat(value)

    case 'boolean':
      return value.trim().toLowerCase() === 'true'

    case 'string':
      return String(value).trim()

    default:
      throw new Error(`Unknown datatype: ${dataType}`)
  }
}

//we compare the outputs and determine whether it matches the test case or not
const compareOutput = (pistonOutput, expectedOutput, lastSubmission) => {
  //first we need to check if the datatypes are correct or not
  try {
    switch (outputDataType.toLowerCase().trim()) {
      case 'int':
        if (pistonOutput.includes('.') || expectedOutput.includes('.') || isNaN(pistonOutput) || isNaN(expectedOutput)) {
          //if not we change the status of the last submission and return an error
          lastSubmission.value.status = 'Invalid integer format in output.'
          throw new Error('Invalid integer format in output.')
        }
        break;
      case 'double':
      case 'float':
        if (isNaN(pistonOutput) || isNaN(expectedOutput)) {
          lastSubmission.value.status = 'Invalid number format in output.'
          throw new Error('Invalid number format in output.')
        }
        break

      case 'boolean':
        lastSubmission.value.status = 'Invalid boolean format in output.'
        if (!['true', 'false'].includes(pistonOutput.trim().toLowerCase()) || !['true', 'false'].includes(expectedOutput.trim().toLowerCase())) {
          lastSubmission.value.status = 'Invalid boolean format in output.'
          throw new Error('Invalid boolean format in output.')
        }
        break

      case 'string':
        // no specific validation needed for strings
        break

      default:
        throw new Error(`Unsupported output data type: ${outputDataType}`)
    }

    //afterwards we transform the values to their actual datatype and compare them
  const actual = transformValue(pistonOutput, outputDataType)
  const expected = transformValue(expectedOutput, outputDataType)

  if (actual !== expected) {
    //if they dont match we change the status of the last submission and return an error
    lastSubmission.value.status = 'Output type mismatch.'
    throw new Error('Output type mismatch.')
  }
    
    //returns a boolean if they match or not
    return actual === expected

  } catch (error) {
    console.error('compareOutput error:', error.message) //oterwise if  there an another error we log it to the console and return false
    return false
  }
}

console.log(compareOutput("true", "true", "boolean")) //test for the compare output function
console.log(compareOutput("true", "false", "boolean")) //test for the compare output function

// Solution section

const saveSolution = () => {
  //if the same solution already exists we tell the user
  for (let i = 0; i < savedSolutions.value.length; i++) {
    if (savedSolutions.value[i].code === code.value && savedSolutions.value[i].language === language.value) {
      window.alert('This solution is already saved.') 
      return
    }
  }
  //otherwise we save the solution in the saved solutions list
  savedSolutions.value.push({
    code: code.value,
    language: language.value,
    timestamp: new Date().toLocaleString() //serves as an id
  });
  alert('Solution saved successfully!')
};


//emit method from the previousSolutions component to delete a solution from the saved solutions list or import it to the editor
const deleteSolution = (index) => {
  savedSolutions.value.splice(index, 1);
};

const importSolution = (index) => {
  //to import a solution we change the current code and language in the editor to the code and language of the imported solution
  const solution = savedSolutions.value[index];
  code.value = solution.code;
  language.value = solution.language;
};

</script>

<template>
  <div :class="['editor-container border rounded shadow-sm overflow-hidden my-3 mx-2', isDarkTheme ? 'border-secondary dark-theme' : 'border-secondary light-theme']">
    <!-- Toolbar -->
    <div :class="['toolbar d-flex align-items-center justify-content-between p-2', isDarkTheme ? 'bg-dark text-white' : 'bg-light text-dark']"
         :style="{ borderBottom: isDarkTheme ? '2px solid #2d2d2d' : '2px solid #e9ecef' }">
      <div class="d-flex align-items-center gap-3 flex-wrap">
        <previousSolutions :savedSolutions="savedSolutions" @deleteSolution="deleteSolution" @importSolution="importSolution"/>
        
        <!-- Theme Selector -->
        <div class="d-flex align-items-center gap-2">
          <label for="theme-select" :class="['mb-0 fw-bold small d-none d-sm-block', isDarkTheme ? 'text-light' : 'text-dark']">Theme:</label>
          <select id="theme-select" v-model="theme" :class="['form-select form-select-sm', isDarkTheme ? 'bg-secondary text-white border-secondary' : 'bg-white text-dark border-secondary']" style="width: auto; cursor: pointer;">
            <option v-for="t in themes" :key="t.value" :value="t.value">
               {{ t.label }}
            </option>
          </select>
        </div>

        <div :class="['vr mx-1 d-none d-sm-block', isDarkTheme ? 'bg-secondary' : 'bg-dark']"></div>

        <!-- Language Selector -->
        <div class="d-flex align-items-center gap-2">
          <label for="language-select" :class="['mb-0 fw-bold small d-none d-sm-block', isDarkTheme ? 'text-light' : 'text-dark']">Language:</label>
          <select id="language-select" v-model="language" :class="['form-select form-select-sm', isDarkTheme ? 'bg-secondary text-white border-secondary' : 'bg-white text-dark border-secondary']" style="width: auto; cursor: pointer;">
            <option v-for="lang in languages" :key="lang.value" :value="lang.value">
               {{ lang.label }}
            </option>
          </select>
        </div>

        <div :class="['vr mx-1 d-none d-sm-block', isDarkTheme ? 'bg-secondary' : 'bg-dark']"></div>

        <!-- Font Size Controls -->
        <div class="d-flex align-items-center gap-2">
          <span :class="['small fw-bold d-none d-sm-block', isDarkTheme ? 'text-light' : 'text-dark']">Font Size:</span>
          <div class="btn-group shadow-sm">
            <button @click="decreaseFontSize" :class="['btn btn-sm d-flex align-items-center justify-content-center', isDarkTheme ? 'btn-outline-light' : 'btn-outline-dark']" title="Decrease Font Size">
              <span style="font-size: 1.2rem; line-height: 0.5;">-</span>
            </button>
            <div :class="['px-2 d-flex align-items-center justify-content-center border-top border-bottom', isDarkTheme ? 'bg-secondary text-white border-light' : 'bg-white text-dark border-dark']" style="min-width: 45px; font-size: 0.875rem;">
              {{ fontSize }}px
            </div>
            <button @click="increaseFontSize" :class="['btn btn-sm d-flex align-items-center justify-content-center', isDarkTheme ? 'btn-outline-light' : 'btn-outline-dark']" title="Increase Font Size">
               <span style="font-size: 1.2rem; line-height: 0.5;">+</span>
            </button>
          </div>
        </div>
      </div>
      
      <!-- Run Button -->
       <div class="d-flex align-items-center gap-2">
      <button @click="runCode" class="btn btn-success btn-sm px-4 py-1 fw-bold d-flex align-items-center gap-2 shadow run-btn rounded-pill border-0">
        <span class="d-none d-sm-inline">Run Code</span>
        <span class="d-inline d-sm-none">Run</span>
      </button>
      </div>
    </div>

    <!-- Editor -->
    <div class="editor-wrapper">
      <VueMonacoEditor
        v-model:value="code"
        :language="language"
        :theme="theme"
        :options="editorOptions"
        @before-mount="handleBeforeMount"
      />
    </div>

    <testCaseForm 
    @addTestCase="addTestCase"
    :listOfTestCases="listOfTestCases"
    :numberOfTestCases="numberOfTestCases"
    :numberOfInputs="numberOfInputs"
    @removeTestCase="removeTestCase"
    @changeTestCase="changeTestCase"
    :inputsNames="inputsNames"
    :InputsDataType="inputsDataType"
    :outputDataType="outputDataType"
    ></testCaseForm>


    <div v-if="isSubmitting || errorMessage || lastSubmission"
  :class="['submission-panel px-3 py-2 small border-top', resultPanelClass]">

  <div v-if="isSubmitting">Running code on Piston...</div> <!-- if still submiting  the  code -->
  <div v-else-if="errorMessage">{{ errorMessage }}</div> <!-- if there is an error -->
  <div v-else-if="allTestCasesPassed">All test cases passed! <!-- if all test cases passed -->
      <span v-if="totalExecutionTime">Total Time: {{ totalExecutionTime }}ms </span>
      <span v-if="totalExecutionTime && submissionList"> Average Time: {{ totalExecutionTime / submissionList.length }}ms</span>
    <div class="result-meta">
      <span>Engine: Piston</span>
      <span>Language: {{ lastSubmission.runtime ?? 'n/a' }}</span>
      <span>Version: {{ lastSubmission.runtime_version ?? 'n/a' }}</span>
      <span>Exit code: {{ lastSubmission.exit_code ?? 'n/a' }}</span>
      <span>Signal: {{ lastSubmission.signal ?? 'none' }}</span>
    </div>
  </div>
  <div v-else-if="lastSubmission" class="result-stack"> <!-- if there is a last submission(not all test cases passed ) -->

    <div class="result-summary">
      Status: "{{ formatExecutionStatus(lastSubmission.status) }}"{{ formatRuntimeLabel(lastSubmission) }}.
      <span v-if="lastSubmission.wall_time">Time: {{ lastSubmission.wall_time }}ms</span>
      <p>Number of test cases passed {{submissionList.length}}</p>
    </div>

    <div v-if="!MatchedOutput && failedTestCase!=null" class="result-summary"><!-- we showcase the testcase that returned false -->
      <testCase @emitToDeleteToTestCaseForm="removeTestCase" :testCase="failedTestCase" :inputsNames="inputsNames" :InputsDataType="inputsDataType" :outputDataType="outputDataType"/>
    </div>

    <!-- info about the last submission -->
    <div class="result-meta">
      <span>Engine: Piston</span>
      <span>Language: {{ lastSubmission.runtime ?? 'n/a' }}</span>
      <span>Version: {{ lastSubmission.runtime_version ?? 'n/a' }}</span>
      <span>Exit code: {{ lastSubmission.exit_code ?? 'n/a' }}</span>
      <span>Signal: {{ lastSubmission.signal ?? 'none' }}</span>
    </div>

    <!-- compiler vs actual output -->
    <div v-if="lastSubmission.compile_output" class="result-block">
      <div class="result-label">Compiler Output</div>
      <pre>{{ lastSubmission.compile_output }}</pre>
    </div>

    <div v-if="lastSubmission.stdout" class="result-block">
      <div class="result-label">Standard Output</div>
      <pre>{{ lastSubmission.stdout }}</pre>
    </div>

    <div v-if="lastSubmission.stderr" class="result-block">
      <div class="result-label">Standard Error</div>
      <pre>{{ lastSubmission.stderr }}</pre>
    </div>

  </div>
</div>
  </div>
</template>

<style>
.editor-container {
  width: calc(100% - 1rem); /* Slight margin for better visual boundaries */
  transition: background-color 0.3s ease;
}

.editor-container.dark-theme {
  background-color: #1e1e1e; /* Monaco dark theme background */
}

.editor-container.light-theme {
  background-color: #ffffff;
}

.toolbar {
  transition: all 0.3s ease;
}

.editor-wrapper {
  width: 100%;
  height: 75vh;
  min-height: 400px;
}

.submission-panel {
  transition: background-color 0.3s ease;
}

.result-stack {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.result-summary {
  font-weight: 600;
}

.result-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
  opacity: 0.9;
}

.result-block {
  display: flex;
  flex-direction: column;
  gap: 0.35rem;
}

.result-label {
  font-weight: 700;
}

.result-block pre {
  margin: 0;
  padding: 0.75rem;
  border-radius: 0.5rem;
  background: rgba(0, 0, 0, 0.08);
  color: inherit;
  font-family: "Consolas", "Courier New", monospace;
  white-space: pre-wrap;
  word-break: break-word;
}

.submission-panel-success {
  background-color: #e8f7ef;
  color: #0f5132;
}

.submission-panel-error {
  background-color: #f8d7da;
  color: #842029;
}

.btn-outline-light {
  border-color: #6c757d;
}
.btn-outline-light:hover {
  background-color: #6c757d;
  color: white;
  border-color: #6c757d;
}

.run-btn {
  background: linear-gradient(135deg, #198754, #146c43); /* Slightly richer green */
  transition: all 0.2s ease-in-out;
}

.run-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 8px rgba(25, 135, 84, 0.4) !important;
  background: linear-gradient(135deg, #1b965d, #157347); /* Brighter on hover */
}

.run-btn:active {
  transform: translateY(1px);
  box-shadow: 0 2px 4px rgba(25, 135, 84, 0.3) !important;
}

select.form-select-sm:focus {
  box-shadow: 0 0 0 0.2rem rgba(255, 255, 255, 0.25);
}
</style>
