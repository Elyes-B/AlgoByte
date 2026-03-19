<template>
  <header class="header">
    <div class="header-title">
      <h1>AlgoByte</h1>
      <span class="edition">demo edition</span>
    </div>

    <nav class="nav-bar">
      <ul>
        <li><router-link to="/">Home</router-link></li>
        <li><router-link to="/editor">Editor</router-link></li>
        <li><router-link to="/about">About</router-link></li>
      </ul>
      <button class="btn btn-primary">Login</button>
    </nav>
  </header>
</template>

<style scoped>
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 40px;
  background: linear-gradient(135deg, #6a11cb, #2575fc);
  color: white;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  border-radius: 0 0 15px 15px;
}

.header-title h1 {
  margin: 0;
  font-size: 32px;
  font-weight: 800;
  background: linear-gradient(90deg, #ffd700, white);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.edition {
  display: block;
  font-size: 14px;
  font-weight: 600;
  opacity: 0.8;
  color: #f0f0f0;
  margin-top: 2px;
}

.nav-bar {
  display: flex;
  align-items: center;
  gap: 30px;
}

.nav-bar ul {
  display: flex;
  list-style: none;
  gap: 25px;
  margin: 0;
  padding: 0;
}

.nav-bar a {
  color: #f0f0f0;
  text-decoration: none;
  font-size: 15px;
  font-weight: 500;
  position: relative;
  transition: all 0.3s ease;
}

.nav-bar a::after {
  content: '';
  position: absolute;
  width: 0%;
  height: 2px;
  left: 0;
  bottom: -3px;
  background: #ffd700;
  transition: width 0.3s ease;
}

.nav-bar a:hover::after {
  width: 100%;
}

.btn-primary {
  padding: 8px 18px;
  background: #ebebeb66;
  border: none;
  border-radius: 8px;
  font-weight: 600;
  color: #fff;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}

.btn-primary:hover {
  background: #ffd700;
  color: #6a11cb;
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0,0,0,0.25);
}
</style>