window.addEventListener("load", () => {
  var confirmDeletionModal = document.getElementById("confirmDeletionModal");
  confirmDeletionModal.addEventListener("show.bs.modal", function (event) {
    // Button that triggered the modal
    var button = event.relatedTarget;
    
    var title = button.getAttribute("data-bs-title");
    var uuid = button.getAttribute("data-bs-uuid");

    var titleSpan = confirmDeletionModal.querySelector("#videoTitle");
    var deleteButton = confirmDeletionModal.querySelector("#deleteButton");

    titleSpan.innerText = title;
    console.log(deleteButton);
    deleteButton.onclick = () => {window.location.href = `/video/remove?v=${uuid}`};
  });
});
