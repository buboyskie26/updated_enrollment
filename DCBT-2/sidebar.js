let btn = document.querySelector("#tab-btn");
      let sidebar = document.querySelector(".sidebar");
      let selectionBtn = document.querySelectorAll(".selection-btn");

      btn.onclick = function () {
        sidebar.classList.toggle("active");
        selectionBtn.forEach((button) => {
          button.classList.toggle("active");
        });
      };