<template>
  <div>
    <h1>Todo List</h1>
    <input 
      v-model="newTodo" 
      @keyup.enter="addTodo" 
      placeholder="Add todo"
    >
    <ul>
      <li v-for="todo in todos" :key="todo.id">
        {{ todo.title }}
        <input 
          type="checkbox" 
          :checked="todo.completed" 
          @change="toggleTodo(todo)"
        >
        <button @click="deleteTodo(todo)">Delete</button>
      </li>
    </ul>
  </div>
</template>

<script>
import axios from 'axios'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

export default {
  data() {
    return {
      todos: [],
      newTodo: '',
      echo: null
    }
  },
  mounted() {
    this.initEcho()
    this.fetchTodos()
  },
  methods: {
    initEcho() {
      window.Pusher = Pusher
      this.echo = new Echo({
        broadcaster: 'reverb',
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: import.meta.env.VITE_REVERB_PORT,
        forceTLS: false,
        disableStats: true,
      })

      this.echo.channel('todos')
        .listen('TodoCreated', (event) => {
          console.log('Todo created:', event.todo)
          this.todos.push(event.todo)
        })
        .listen('TodoUpdated', (event) => {
          const index = this.todos.findIndex(t => t.id === event.todo.id)
          if (index !== -1) {
            this.todos[index] = event.todo
          }
        })
        .listen('TodoDeleted', (event) => {
          this.todos = this.todos.filter(t => t.id !== event.todo.id)
        })
    },
    async fetchTodos() {
      try {
        const response = await axios.get('http://localhost:8000/api/todos')
        this.todos = response.data
      } catch (error) {
        console.error('Error fetching todos:', error)
      }
    },
    async addTodo() {
      if (this.newTodo.trim()) {
        try {
          await axios.post('http://localhost:8000/api/todos', { 
            title: this.newTodo 
          })
          this.newTodo = ''
        } catch (error) {
          console.error('Error adding todo:', error)
        }
      }
    },
    async toggleTodo(todo) {
      try {
        await axios.put(`http://localhost:8000/api/todos/${todo.id}`, { 
          completed: !todo.completed 
        })
      } catch (error) {
        console.error('Error updating todo:', error)
      }
    },
    async deleteTodo(todo) {
      try {
        await axios.delete(`http://localhost:8000/api/todos/${todo.id}`)
      } catch (error) {
        console.error('Error deleting todo:', error)
      }
    }
  }
}
</script>