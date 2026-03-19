import { createRouter, createWebHistory } from 'vue-router'
import Home from '../components/Home.vue'
import About from '../components/About.vue'
import Editor from "../views/Editor.vue"

const routes = [
  { path: '/', name: 'Home', component: Home },
  { path: '/about', name: 'About', component: About },
  { path: "/editor", name: "Editor", component: Editor }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router