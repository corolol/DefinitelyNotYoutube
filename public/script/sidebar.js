function sidebarOn() {
    var sidebar = document.getElementById("sidebarSm")
    sidebar.classList.remove("hidden");
    sidebar.classList.add("shown");
}

function sidebarOff() {
    var sidebar = document.getElementById("sidebarSm")
    sidebar.classList.add("hidden");
    sidebar.classList.remove("shown");
}

window.addEventListener('resize', () => {
    if (window.innerWidth > 575) sidebarOff();
})