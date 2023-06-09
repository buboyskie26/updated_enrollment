<?php 

    require_once('../includes/config.php');
 
    // require_once('./classes/HomePageEnroll.php');

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta
      name="viewport"
      content="width=device-width, inital-scale=1, shrink-to-fit=no"
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Chakra+Petch:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,700&family=Lato:wght@100;300;700&display=swap"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
      integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N"
      crossorigin="anonymous"
    />

    <link rel="stylesheet" href="../assets/css/mainWeb.css" />

    <title>Daehan College of Business and Technology</title>
  </head>
  <body>
    <nav class="navbar navbar-expand-lg navbar-light">
      <a class="navbar-brand" href="/DCBT-2/Main.html">
        <img src="/DCBT-2/img/DCBT-Logo.jpg" alt="DCBT-Logo" />
      </a>
      <button
        class="navbar-toggler"
        type="button"
        data-toggle="collapse"
        data-target="#navbarSupportedContent"
        aria-controls="navbarSupportedContent"
        aria-expanded="false"
        aria-label="Toggle navigation"
      >
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav ml-auto">
          <li class="nav-item">
            <a class="nav-link" href="#"><span>ADMISSIONS</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><span>ACADEMICS</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#"><span>ABOUT</span></a>
          </li>
          <li class="nav-item">
            <a class="nav-link" id="show-login"><span>LOGIN</span></a>
          </li>
        </ul>
      </div>
    </nav>

    <div class="online-application">
      <div class="row-content">
        <h3>Online Application</h3>
        <p>
          Lorem ipsum dolor sit amet consectetur adipisicing elit. Saepe, nam
          modi autem adipisci natus quidem rem assumenda ex suscipit fugit amet
          nemo voluptatum recusandae officia, minus non nihil, fuga unde.
        </p>
      </div>
      <div class="row-content">
        <h4>Choose enrollment type</h4>

        <div class="col-btn">
            <a href="index.php">
            <button type="button" class="type-btn">
                New student
            </button>

            <!-- <button type="button" class="type-btn" onclick="new_student()">
                New student
            </button> -->

            </a>
         
          <button type="button" class="type-btn">Old student</button>
        </div>
      </div>

      <div class="row-content">
        <h4>Or</h4>

        <div class="col-btn">
          <button type="button" class="type-btn">
            Check/edit submitted form
          </button>
        </div>
      </div>
    </div>

    <footer>
      <div class="contact">
        <h4>DAEHAN COLLEGE OF BUSINESS AND TECHNOLOGY</h4>
        <p>Nicanor Reyes Street, Sampaloc, Manila</p>
        <p>Tel No: +63(2)-87777-338</p>
        <p>Terms and condition | Privacy Policy</p>
      </div>
      <div class="copyright">
        <h4>Copyright © 2019. All Rights Reserved</h4>
      </div>
    </footer>

    <div class="popup" id="login-form">
      <div class="close-btn">&times;</div>
      <div class="form">
        <h2>Log-in</h2>
        <p>Log-in with your school email</p>

        <div class="form-element">
          <label for="email">Email</label>
          <input type="text" id="email" />
        </div>
        <div class="form-element">
          <a class="forgot-email">Forgot email?</a>
        </div>
        <div class="form-element">
          <label for="password">Password</label>
          <input type="text" id="password" />
        </div>
        <div class="form-element">
          <a class="forgot-password">Forgot password?</a>
        </div>
        <div class="form-element">
          <button type="button">Confirm</button>
        </div>
      </div>
    </div>

    <script>
      document
        .querySelector("#show-login")
        .addEventListener("click", function () {
          document.querySelector("#login-form").classList.add("active");
        });
      document
        .querySelector(".popup .close-btn")
        .addEventListener("click", function () {
          document.querySelector("#login-form").classList.remove("active");
        });
    </script>
    <script>

      function new_student() {
        window.location.href =
          "/DCBT-2/enrollment-page-main/new-student-form/Student-form.html";
      }

    </script>
    <script
      src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"
      integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj"
      crossorigin="anonymous"
    ></script>
    <script
      src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"
      integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct"
      crossorigin="anonymous"
    ></script>
  </body>
</html>
