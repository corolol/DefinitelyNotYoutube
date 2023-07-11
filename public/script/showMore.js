function toggleShowMore() {
  var text = document.getElementById("description-text");
  var toggle = document.getElementById("description-toggle");
  text.classList.toggle("show-all");

  var verb = "";
  if (text.classList.contains("show-all")) verb = "less";
  else verb = "more";

  toggle.innerText = `Show ${verb}`;
}

window.addEventListener("load", () => {
  var toggle = document.getElementById("description-toggle");
  toggle.addEventListener("click", toggleShowMore);
});
