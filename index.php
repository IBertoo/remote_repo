<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Exploración Espacial</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      background: linear-gradient(to bottom, #0a0a23, #1a1a3d);
      overflow-x: hidden;
    }
    .stars {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      pointer-events: none;
      z-index: -1;
    }
    .star {
      position: absolute;
      background: white;
      border-radius: 50%;
      animation: twinkle 5s infinite;
    }
    @keyframes twinkle {
      0%, 100% { opacity: 0.2; }
      50% { opacity: 1; }
    }
    .glow {
      box-shadow: 0 0 20px rgba(0, 255, 255, 0.5);
    }
    .hover-scale:hover {
      transform: scale(1.05);
      transition: transform 0.3s ease;
    }
  </style>
</head>
<body class="text-white font-sans">
  <!-- Fondo de estrellas -->
  <canvas class="stars" id="stars"></canvas>

  <!-- Encabezado -->
  <header class="fixed top-0 w-full bg-black bg-opacity-50 backdrop-blur-md p-4 z-10">
    <nav class="container mx-auto flex justify-between items-center">
      <h1 class="text-3xl font-bold text-cyan-400">CosmoNet</h1>
      <ul class="flex space-x-6">
        <li><a href="#home" class="text-cyan-300 hover:text-cyan-100 transition">Inicio</a></li>
        <li><a href="#about" class="text-cyan-300 hover:text-cyan-100 transition">Explorar</a></li>
        <li><a href="#contact" class="text-cyan-300 hover:text-cyan-100 transition">Contacto</a></li>
      </ul>
    </nav>
  </header>

  <!-- Sección Formulario -->
  <section id="forForm" class="py-20">
    <div class="container mx-auto text-center">
      <h3 class="text-4xl font-bold text-cyan-400 mb-8 glow">Formulario de Contacto</h3>
      <form id="checkout-form" method="POST" action="/cart.php" class="max-w-lg mx-auto space-y-6">
        <input type="hidden" name="cart_data" id="cart-data">

        <div>
          <label for="customer_name" class="block text-lg text-cyan-300 mb-2">Nombre completo <span class="text-red-400">*</span></label>
          <input type="text" id="customer_name" name="customer_name" placeholder="Ej. Juan Pérez" class="w-full p-3 bg-black bg-opacity-50 text-white border border-cyan-500 rounded-lg focus:outline-none focus:border-cyan-300 glow">
        </div>

        <div>
          <label for="customer_phone" class="block text-lg text-cyan-300 mb-2">Número de celular <span class="text-red-400">*</span></label>
          <input type="tel" id="customer_phone" name="customer_phone" placeholder="Ej. 71234567" pattern="[0-9]{8,12}" title="Introduce un número válido" class="w-full p-3 bg-black bg-opacity-50 text-white border border-cyan-500 rounded-lg focus:outline-none focus:border-cyan-300 glow">
        </div>

        <div>
          <label for="customer_email" class="block text-lg text-cyan-300 mb-2">Correo electrónico (opcional)</label>
          <input type="email" id="customer_email" name="customer_email" placeholder="Ej. correo@ejemplo.com" class="w-full p-3 bg-black bg-opacity-50 text-white border border-cyan-500 rounded-lg focus:outline-none focus:border-cyan-300 glow">
        </div>

        <div id="form-message" class="text-red-400 hidden"></div>

        <button type="submit" id="checkout-button" class="bg-cyan-500 text-black px-6 py-3 rounded-full hover:bg-cyan-300 transition hover-scale">Finalizar Compra</button>
      </form>
    </div>
  </section>

  <!-- Resto de las secciones (sin cambios) -->
  <section id="home" class="min-h-screen flex items-center justify-center">
    <div class="text-center">
      <h2 class="text-5xl font-extrabold text-cyan-400 mb-4 glow">Bienvenido al Cosmos</h2>
      <p class="text-lg text-gray-300 mb-6">Explora el universo con nuestra interfaz futurista.</p>
      <a href="#about" class="inline-block bg-cyan-500 text-black px-6 py-3 rounded-full hover:bg-cyan-300 transition hover-scale">Descubre Más</a>
    </div>
  </section>

  <section id="about" class="py-20 bg-gradient-to-b from-transparent to-black">
    <div class="container mx-auto text-center">
      <h3 class="text-4xl font-bold text-cyan-400 mb-8 glow">Acerca del Espacio</h3>
      <p class="text-gray-300 max-w-2xl mx-auto">Embárcate en un viaje a través de las estrellas. Descubre galaxias, nebulosas y los misterios del universo con nuestra tecnología de punta.</p>
    </div>
  </section>

  <section id="contact" class="py-20">
    <div class="container mx-auto text-center">
      <h3 class="text-4xl font-bold text-cyan-400 mb-8 glow">Contacto Cósmico</h3>
      <p class="text-gray-300 mb-6">¿Listo para conectarte con el universo? Envíanos un mensaje.</p>
      <button class="bg-cyan-500 text-black px-6 py-3 rounded-full hover:bg-cyan-300 transition hover-scale">Enviar Mensaje</button>
    </div>
  </section>

  <footer class="py-6 bg-black bg-opacity-50 text-center">
    <p class="text-gray-400">&copy; 2025 CosmoNet. Todos los derechos reservados.</p>
  </footer>

  <!-- JavaScript para animación de estrellas y manejo del formulario -->
  <script>
    // Animación de estrellas (sin cambios)
    const canvas = document.getElementById('stars');
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;

    const stars = [];
    for (let i = 0; i < 100; i++) {
      stars.push({
        x: Math.random() * canvas.width,
        y: Math.random() * canvas.height,
        radius: Math.random() * 2,
        opacity: Math.random()
      });
    }

    function animateStars() {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      stars.forEach(star => {
        ctx.beginPath();
        ctx.arc(star.x, star.y, star.radius, 0, Math.PI * 2);
        ctx.fillStyle = `rgba(255, 255, 255, ${star.opacity})`;
        ctx.fill();
        star.opacity += Math.random() * 0.02 - 0.01;
        if (star.opacity > 1) star.opacity = 1;
        if (star.opacity < 0.2) star.opacity = 0.2;
      });
      requestAnimationFrame(animateStars);
    }
    animateStars();

    window.addEventListener('resize', () => {
      canvas.width = window.innerWidth;
      canvas.height = window.innerHeight;
    });


  </script>
</body>
</html>
  