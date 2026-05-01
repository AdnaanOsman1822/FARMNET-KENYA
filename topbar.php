<!-- topbar.php -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['userid'])) {
    header("Location: login.php");
    die;
}

$userRole = $_SESSION['role'] ?? '';
?>


<style>
  @font-face {
    font-family: 'Jura';
    src: url('ui/fonts/jura.ttf') format('truetype'); /* Absolute path */
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Jura', sans-serif; /* Apply Jura globally */
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

  #right_menu {
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

  #right_menu.visible {
    right: 0;
  }

  #right_menu h2 {
    color: #7CD375;
    font-size: 24px;
    font-weight: bold;
    margin-bottom: 20px;
    border-bottom: 2px solid #7CD375;
    padding-bottom: 10px;
  }

  #right_menu ul {
    list-style: none;
    padding: 0;
    margin: 0;
  }

  #right_menu ul li a {
    color: #7CD375;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
  }

  #right_menu ul li a:hover {
    text-decoration: underline;
  }

  #right_menu .logout {
    border-top: 1px solid #7CD375;
    padding-top: 20px;
  }

  #right_menu .logout a {
    color: red;
    font-size: 18px;
    font-weight: bold;
    text-decoration: none;
  }

  #right_menu .logout a:hover {
    text-decoration: underline;
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

<header id="topbar">
  <div id="logo">
    <a href="index.php"><img src="ui/images/logo.png" alt="Farmnet Kenya Logo"></a>
  </div>

  <nav id="main_nav">
    <a href="index.php" class="nav-link" data-page="home">Home</a>
    <a href="chats.php" class="nav-link" data-page="chats">Chats</a>
    <a href="contacts.php" class="nav-link" data-page="contacts">Contacts</a>
    <a href="profile.php" class="nav-link" data-page="profile">Profile</a>
  </nav>

  <div id="menu_icon">&#9776;</div>
</header>

<aside id="right_menu">
  <div>
    <h2>Menu</h2>
    <ul>
      <?php if ($userRole === 'farmer'): ?>
        <li><a href="farmer_history.php">History</a></li>
        <br>
        <li><a href="contact_us.php">Contact Us</a></li>
        <br>
        <li><a href="change_password.php">Change Password</a></li>

        <?php elseif ($userRole === 'agronomist'): ?>
        
        <li><a href="agronomist_history.php">History</a></li>
        <br>
        <li><a href="contact_us.php">Contact Us</a></li>
        <br>
        <li><a href="change_password.php">Change Password</a></li>
      <?php endif; ?>
    </ul>
  </div>
  <div class="logout">
    <a href="#" id="logout_link">LOGOUT</a>
  </div>
</aside>

<script>
  document.addEventListener("DOMContentLoaded", () => {

    
    // Logout confirmation
    const logoutLink = document.getElementById("logout_link");
        if (logoutLink) {
        logoutLink.addEventListener("click", function (e) {
            e.preventDefault();
            const confirmLogout = confirm("Are you sure you want to logout?");
            if (confirmLogout) {
            window.location.href = "logout.php";
            }
        });
        }

    const menuIcon = document.getElementById('menu_icon');
    const rightMenu = document.getElementById('right_menu');

    menuIcon.addEventListener('click', () => {
      rightMenu.classList.toggle('visible');
    });

    document.addEventListener('click', (e) => {
      if (!rightMenu.contains(e.target) && e.target !== menuIcon) {
        rightMenu.classList.remove('visible');
      }
    });

    // Highlight active nav link
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('#main_nav a').forEach(link => {
      if (link.getAttribute('href') === currentPage) {
        link.classList.add('active');
      }
    });
  });
</script>
