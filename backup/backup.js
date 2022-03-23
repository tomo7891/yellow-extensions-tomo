let checkbox_all = document.querySelector("#all");
let checkbox_list = document.querySelectorAll(".backup");

checkbox_all.addEventListener('change', change_all);


function change_all() {

  if (checkbox_all.checked) {
    for (let i in checkbox_list) {
      if (checkbox_list.hasOwnProperty(i)) {
        checkbox_list[i].checked = true;
      }
    }
    
  } else {
    for (let i in checkbox_list) {
      if (checkbox_list.hasOwnProperty(i)) {
        checkbox_list[i].checked = false;
      }
    }
    
  }

};
