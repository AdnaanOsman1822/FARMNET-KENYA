<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Farmnet Kenya</title>
  <style>
    @font-face {
      font-family: 'Jura';
      src: url('ui/fonts/jura.ttf') format('truetype');
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Jura', sans-serif;
      background-color: #ffffff;
    }

    #topbar {
      background-color: #102D03;
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 10px 30px;
      height: 100px;
    }

    #logo img {
      height: 80px;
      cursor: pointer;
    }

    nav#main_nav {
      display: flex;
      gap: 30px;
      align-items: center;
    }

    nav#main_nav a {
      color: #7CD375;
      text-decoration: none;
      font-weight: bold;
      text-transform: uppercase;
      font-size: 20px;
      padding-bottom: 6px;
      border-bottom: 4px solid transparent;
      transition: all 0.2s ease-in-out;
    }

    nav#main_nav a.active {
      border-bottom: 3px solid #7CD375;
    }

    nav#main_nav a:hover {
      color: #a2f6a6;
    }

    #menu_icon {
      font-size: 36px;
      color: #7CD375;
      cursor: pointer;
      display: block;
    }

    #menu {
      position: fixed;
      top: 0;
      right: -260px;
      width: 260px;
      height: 100%;
      background-color: #ffffff;
      box-shadow: -2px 0 8px rgba(0, 0, 0, 0.2);
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: right 0.3s ease;
      z-index: 1000;
      padding: 20px;
    }

    #menu.visible {
      right: 0;
    }

    #menu h2 {
      color: #7CD375;
      font-size: 24px;
      font-weight: bold;
      margin-bottom: 20px;
      border-bottom: 2px solid #7CD375;
      padding-bottom: 10px;
    }

    #menu ul {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    #menu ul li a {
      color: #7CD375;
      font-size: 18px;
      font-weight: bold;
      text-decoration: none;
    }

    #menu ul li a:hover {
      text-decoration: underline;
    }

    #menu .logout {
      border-top: 1px solid #7CD375;
      padding-top: 20px;
    }

    #menu .logout a {
      color: red;
      font-size: 18px;
      font-weight: bold;
      text-decoration: none;
    }

    #menu .logout a:hover {
      text-decoration: underline;
    }

    #content_area {
      padding: 20px;
    }

    @media(max-width: 768px) {
      nav#main_nav {
        display: none;
      }

      #menu_icon {
        display: block;
      }
    }
  </style>
</head>
<body>

<!-- Header -->
<header id="topbar">
  <div id="logo">
    <a href="index.php"><img src="ui/images/logo.png" alt="Farmnet Kenya Logo"></a>
  </div>

  <nav id="main_nav">
    <a href="index.php" class="nav-link">Home</a>
    <a href="chats.php" class="nav-link">Chats</a>
    <a href="contacts.php" class="nav-link">Contacts</a>
    <a href="profile.php" class="nav-link">Profile</a>
  </nav>

  <div id="menu_icon">&#9776;</div>
</header>

<!-- Slide-in Right Menu -->
<div id="menu">
  <div>
    <h2>Menu</h2>
    <ul>
      <li><a href="history.php">SERVICE HISTORY</a></li>
    </ul>
  </div>

  <div class="logout">
    <a href="logout.php">LOGOUT</a>
  </div>
</div>

<script>
  const menuIcon = document.getElementById('menu_icon');
  const slideMenu = document.getElementById('menu');

  menuIcon.addEventListener('click', () => {
    slideMenu.classList.toggle('visible');
  });

  document.addEventListener('click', e => {
    if (!slideMenu.contains(e.target) && e.target !== menuIcon) {
      slideMenu.classList.remove('visible');
    }
  });

  // Highlight active link
  const currentPath = window.location.pathname;
  const navLinks = document.querySelectorAll('#main_nav a');
  navLinks.forEach(link => {
    if (link.getAttribute('href') === currentPath.split('/').pop()) {
      link.classList.add('active');
    }
  });
</script>
