$(document).ready(function () {
});

$("#showpw").click(function() {
    if (this.checked) {
      $('#password').attr("type", "text");
    } else {
      $('#password').attr("type", "password");
    }
  });