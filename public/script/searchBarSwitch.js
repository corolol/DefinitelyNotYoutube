function getTopbars() {
  return [
    document.getElementById("defaultTopbar"),
    document.getElementById("searchTopbar"),
  ];
}

function showSearchTopbar() {
  var [main, search] = getTopbars();

  sidebarOff();

  main.classList.remove("visible");
  main.classList.add("hidden");

  search.classList.remove("hidden");
  search.classList.add("visible");

  document.getElementById("searchInputSec").focus();
}

function showMainTopbar() {
  var [main, search] = getTopbars();

  search.classList.remove("visible");
  search.classList.add("hidden");

  main.classList.remove("hidden");
  main.classList.add("visible");
}

window.addEventListener('resize', () => {
    if (window.innerWidth > 575) showMainTopbar();
})