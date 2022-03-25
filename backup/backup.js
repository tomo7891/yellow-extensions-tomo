let checkbox_all = document.querySelector("#all");
let checkbox_list = document.querySelectorAll(".backup");

let checkbox_all2 = document.querySelector("#all_content");
let checkbox_list2 = document.querySelectorAll(".backup_content");

let checkbox_all3 = document.querySelector("#all_media");
let checkbox_list3 = document.querySelectorAll(".backup_media");

let checkbox_all4 = document.querySelector("#all_system");
let checkbox_list4 = document.querySelectorAll(".backup_system");

checkbox_all.addEventListener('change', change_all);
checkbox_all2.addEventListener('change', change_all2);
checkbox_all3.addEventListener('change', change_all3);
checkbox_all4.addEventListener('change', change_all4);


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
}

function change_all2() {
  if (checkbox_all2.checked) {
    for (let i in checkbox_list2) {
      if (checkbox_list2.hasOwnProperty(i)) {
        checkbox_list2[i].checked = true;
      }
    }
  } else {
    for (let i in checkbox_list2) {
      if (checkbox_list2.hasOwnProperty(i)) {
        checkbox_list2[i].checked = false;
      }
    }
  }
}

function change_all3() {
  if (checkbox_all3.checked) {
    for (let i in checkbox_list3) {
      if (checkbox_list3.hasOwnProperty(i)) {
        checkbox_list3[i].checked = true;
      }
    }
  } else {
    for (let i in checkbox_list3) {
      if (checkbox_list3.hasOwnProperty(i)) {
        checkbox_list3[i].checked = false;
      }
    }
  }
}

function change_all4() {
  if (checkbox_all4.checked) {
    for (let i in checkbox_list4) {
      if (checkbox_list4.hasOwnProperty(i)) {
        checkbox_list4[i].checked = true;
      }
    }
  } else {
    for (let i in checkbox_list4) {
      if (checkbox_list4.hasOwnProperty(i)) {
        checkbox_list4[i].checked = false;
      }
    }
  }
}