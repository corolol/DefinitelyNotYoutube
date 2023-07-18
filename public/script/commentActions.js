function removeComment(id) {
  console.log(id);
  let commentElement = document.getElementById(`comment-${id}`);
  commenter.delete(id, () => {
    commentElement.remove();
  });
}

function enterCommentEdit(id) {
  let commentEditButtons = document.getElementById(`commentEditButtons-${id}`);
  commentEditButtons.classList.add("d-flex");
  commentEditButtons.classList.remove("d-none");

  let commentActions = document.getElementById(`commentActions-${id}`);
  commentActions.classList.add("d-none");
  commentActions.classList.remove("d-flex");

  let commentContetnt = document.getElementById(`commentContent-${id}`);
  let commentEditField = document.getElementById(`commentEdit-${id}`);

  commentContetnt.classList.add("d-none");
  commentEditField.classList.add("d-block");
  commentEditField.classList.remove("d-none");
  commentEditField.focus();
}

function leaveCommentEdit(id) {
  let commentEditButtons = document.getElementById(`commentEditButtons-${id}`);
  commentEditButtons.classList.add("d-none");
  commentEditButtons.classList.remove("d-flex");

  let commentActions = document.getElementById(`commentActions-${id}`);
  commentActions.classList.add("d-flex");
  commentActions.classList.remove("d-none");

  let commentContetnt = document.getElementById(`commentContent-${id}`);
  let commentEditField = document.getElementById(`commentEdit-${id}`);

  commentContetnt.classList.remove("d-none");
  commentEditField.classList.remove("d-block");
  commentEditField.classList.add("d-none");

  commentEditField.innerText = commentContetnt.innerText;
}

function swapContetnt(id, content) {
    document.getElementById(`commentContent-${id}`).innerText = content;
}

function handleEditFieldChange(e, id) {
    console.log()
    var saveButton = document.getElementById(`commentUpdateButton-${id}`);
    if (
      e.target.innerText.trim().length > 0 &&
      saveButton.classList.contains("disabled")
    )
      saveButton.classList.remove("disabled");
  
    if (
      e.target.innerText.trim().length === 0 &&
      !saveButton.classList.contains("disabled")
    )
      saveButton.classList.add("disabled");
}

window.addEventListener("load", () => {
  var deleteButtons = document.getElementsByClassName("commentDelete");

  for (let element of deleteButtons) {
    let id = element.dataset["commentId"];
    if (id) {
      element.addEventListener("click", () => {
        removeComment(id);
      });
    }
  }

  var editButtons = document.getElementsByClassName("commentEdit");

  for (let element of editButtons) {
    let id = element.dataset["commentId"];
    if (id) {
      element.addEventListener("click", () => {
        enterCommentEdit(id);
      });
    }
  }

  var editCancelButtons = document.getElementsByClassName('cancel-comment-edit');
  
  for (let element of editCancelButtons) {
    let id = element.dataset["commentId"];
    if (id) {
      element.addEventListener("click", () => {
        leaveCommentEdit(id);
      });
    }
  }

  var commentUpdateSaveButtons = document.getElementsByClassName('comment-update-save');

  for (let element of commentUpdateSaveButtons) {
    let id = element.dataset["commentId"];
    if (id) {
      element.addEventListener("click", () => {
        let newContent = document.getElementById(`commentEdit-${id}`).innerText;
        commenter.update(id, newContent, () => {
            swapContetnt(id, newContent);
            leaveCommentEdit(id);
        });
      });
    }
  }

  var commentEditFields = document.getElementsByClassName('comment-edit');

  for (let element of commentEditFields) {
    let id = element.dataset["commentId"];
    if (id) {
        let handle = (e) => {
            handleEditFieldChange(e, id);
        };
        element.addEventListener('input', handle);
        element.addEventListener('copy', handle);
        element.addEventListener('paste', handle);
        element.addEventListener('cut', handle);
    }
  }
});
