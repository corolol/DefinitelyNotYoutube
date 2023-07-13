function search() {
    var input = document.getElementById('searchInput');
    window.location.href = `/search?s=${encodeURIComponent(input.value)}`
    // console.log(`/search?s=${encodeURIComponent(input.value)}`);
}


window.addEventListener('load', () => {
    document.getElementById('searchInput').addEventListener('keypress', (e) => {
        if (e.key === "Enter") search();
    });
    document.getElementById('search').addEventListener('click', search);
});