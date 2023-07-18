function showButtons() {
  var buttonsDiv = document.getElementById("commentButtons");
  buttonsDiv.classList.add("d-flex");
  buttonsDiv.classList.remove("d-none");
}

function hideButtons() {
  var buttonsDiv = document.getElementById("commentButtons");
  buttonsDiv.classList.add("d-none");
  buttonsDiv.classList.remove("d-flex");
}

function handleChange(e) {
  var postButton = document.getElementById("commentPostButton");
  if (
    e.target.innerText.trim().length > 0 &&
    postButton.classList.contains("disabled")
  )
    postButton.classList.remove("disabled");

  if (
    e.target.innerText.trim().length === 0 &&
    !postButton.classList.contains("disabled")
  )
    postButton.classList.add("disabled");
}

function cancel() {
  commentInput.innerHTML = "";
  commentInput.blur();
  hideButtons();
}

function formatDate(date) {
    console.log(date);
    var dd = date.getDate();
    dd = (dd>9 ? '' : '0') + dd;
    
    var mm = date.getMonth() + 1;
    mm = (mm>9 ? '' : '0') + mm;

    var yyyy = date.getFullYear();

    var hh = date.getHours();
    hh = (hh>9 ? '' : '0') + hh;

    var min = date.getMinutes();
    min = (min>9 ? '' : '0') + min;

    return `${dd}-${mm}-${yyyy} ${hh}:${min}`;
}

function showComment(comment) {
    console.log(comment);
    var commentElement = document.createElement('div');
    commentElement.classList.add(...['d-flex', 'flex-column', 'comment']);

    var infoElement = document.createElement('div');
    infoElement.classList.add(...['info', 'd-flex']);

    var authorSpan = document.createElement('span');
    authorSpan.classList.add(...['author', 'fw-bold']);

    var authorLink = document.createElement('a');
    authorLink.href = `/channel?u=${comment.username}`;
    authorLink.innerText = comment.username;

    authorSpan.appendChild(authorLink);

    var dateSpan = document.createElement('span');
    dateSpan.classList.add(...['date']);
    dateSpan.innerText = formatDate(new Date(comment.date.timestamp*1000));

    infoElement.appendChild(authorSpan);
    infoElement.appendChild(dateSpan);

    var commentContentElement = document.createElement('div');
    commentContentElement.classList.add(...['comment-content', 'py-1']);
    commentContentElement.innerText = comment.content;

    commentElement.appendChild(infoElement);
    commentElement.appendChild(commentContentElement);


    var commentsContainer = document.getElementById("comments");
    commentsContainer.insertBefore(commentElement, commentsContainer.firstChild);
}

window.addEventListener("load", () => {
  var commentInput = document.getElementById("commentInput");
  var cancelButton = document.getElementById("cancelCommentButton");
  var postButton = document.getElementById("commentPostButton");

  commentInput.addEventListener("focus", showButtons);

  commentInput.addEventListener("input", handleChange);
  commentInput.addEventListener("copy", handleChange);
  commentInput.addEventListener("paste", handleChange);
  commentInput.addEventListener("cut", handleChange);

  cancelButton.addEventListener("click", cancel);

  postButton.addEventListener("click", () => {
    commenter.post(commentInput.innerText, (json) => {
      cancel();
      showComment(json);
    });
  });
});
