function search(sec) {
    var input = document.getElementById(`searchInput${sec ? "Sec" : ""}`);
    window.location.href = `/search?s=${encodeURIComponent(input.value)}`
    // console.log(`/search?s=${encodeURIComponent(input.value)}`);
}


window.addEventListener('load', () => {
    document.getElementById('searchInput').addEventListener('keypress', (e) => {
        if (e.key === "Enter") search();
    });
    document.getElementById('searchInputSec').addEventListener('keypress', (e) => {
        if (e.key === "Enter") search(true);
    });

    document.getElementById('search').addEventListener('click', () => {search(false)});
    document.getElementById('searchSec').addEventListener('click', () => {search(true)});
});