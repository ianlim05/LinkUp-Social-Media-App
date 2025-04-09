document.addEventListener("DOMContentLoaded", function () {
  const overlay = document.getElementById("overlay1");
  const closeButton = document.querySelector(".xButton1"); 
  const urlParams = new URLSearchParams(window.location.search);
  
  
  if (urlParams.has("id")) {
      overlay.style.visibility = "visible";
      overlay.style.opacity = "1";
  }

 
  document.addEventListener("click", function (event) {
      if (event.target.closest(".hidden-link")) { // Check if clicked element is inside the post link


          // Show overlay
          overlay.style.visibility = "visible";
          overlay.style.opacity = "1";
      }
  });

  
  closeButton.addEventListener("click", function (event) {
      event.preventDefault();
      overlay.style.opacity = "0";
      setTimeout(() => {
          overlay.style.visibility = "hidden";

          // Removes `id' from URL after closing the overlay
          window.history.pushState({}, "", "ViewPost.php");
      }, 500);
  });

  
  overlay.addEventListener("click", function (event) {
      if (event.target === overlay) { // Ensure only background click closes it
          overlay.style.opacity = "0";
          setTimeout(() => {
              overlay.style.visibility = "hidden";

              
              window.history.pushState({}, "", "ViewPost.php");
          }, 500);
      }
  });
});


// Made by EtishaGarg (n.d.)  EtishaGarg. (n.d.). GitHub - EtishaGarg/twitter-like-animation. GitHub. https://github.com/EtishaGarg/twitter-like-animation?tab=readme-ov-file

document.addEventListener("DOMContentLoaded", function () {
    const likeBtn = document.getElementById("like-btn");

    if (likeBtn) {
        likeBtn.addEventListener("click", function () {
            this.classList.toggle("liked"); // Keeps animation behavior

            const postId = this.getAttribute("data-post-id");
            const userId = this.getAttribute("data-user-id");

            console.log("Post ID:", postId);
            console.log("User ID:", userId);

            if (!postId || !userId) {
                alert("You must be logged in to like this post.");
                return;
            }

            // Send data to PHP
            fetch("ViewPost.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `post_id=${postId}&user_id=${userId}`
            })
            .then(response => response.text())
            .then(data => {
                console.log("Server response:", data); // Debugging log
            })
            .catch(error => console.error("Error:", error));
        });
    }
});
