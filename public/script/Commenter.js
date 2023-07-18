class Commenter {
  constructor(videoUUID) {
    this.videoUUID = videoUUID;
  }

  async post(content, successCallback) {
    var result = await fetch("/comment/post", {
      method: "post",
      body: JSON.stringify({
        content: content,
        videoUUID: this.videoUUID,
      }),
      headers: {
        "Content-Type": "application/json",
      },
    });
    if (result.status === 200) {
      var json = await result.json();
      successCallback(json);
    }
  }

  async update(id, content, successCallback) {
    var result = await fetch("/comment/update", {
        method: "put",
        body: JSON.stringify({
          content: content,
          commentID: id,
        }),
        headers: {
          "Content-Type": "application/json",
        },
      });
      if (result.status === 200) successCallback();
  }

  async delete(id, successCallback) {
    var result = await fetch("/comment/delete", {
      method: "delete",
      body: JSON.stringify({
        commentID: id,
      }),
      headers: {
        "Content-Type": "application/json",
      },
    });
    if (result.status === 200) successCallback();
  }
}
