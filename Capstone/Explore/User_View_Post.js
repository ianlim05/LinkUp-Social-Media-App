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
            window.history.pushState({}, "", "searchuser.php");
        }, 500);
    });
  
    
    overlay.addEventListener("click", function (event) {
        if (event.target === overlay) { // Ensure only background click closes it
            overlay.style.opacity = "0";
            setTimeout(() => {
                overlay.style.visibility = "hidden";
                window.history.pushState({}, "", "searchuser.php");
            }, 500);
        }
    });
  
    // Add comment form submission handler
  
  
  
  // Made by EtishaGarg (n.d.)  EtishaGarg. (n.d.). GitHub - EtishaGarg/twitter-like-animation. GitHub. https://github.com/EtishaGarg/twitter-like-animation?tab=readme-ov-file
  
  function loadPosts(search = '') {
      $("#postList").html('<div class="loading">Loading...</div>');
      $.ajax({
          url: "fetch_posts.php",
          type: "GET",
          data: { search: search },
          dataType: 'json',
          success: function(posts) {
              let output = `<div class="post-container">`;
  
              if (!posts || posts.length === 0) {
                  output = `
                      <div class="no-posts-message">
                          <p>No posts found</p>
                      </div>`;
              } else {
                  posts.forEach(post => {
                      output += `
                          <div class="post-card" data-post-id="${post.PostID}">
                              <div class="post-image-container">
                                  <img src="../!! Images/${post.fileImage}" 
                                      alt="Post Image" 
                                      class="post-img"
                                      onerror="this.src='../!! Images/DefaultPost.png'">
                              </div>
                              <div class="post-footer">
                                  <div class="user-info">
                                      <div class="user-profile">
                                          <img src="../!! Images/${post.profile_picture}" 
                                              alt="User Avatar" 
                                              class="user-avatar"
                                              onerror="this.src='../!! Images/DefaultUser.jpg'">
                                          <span class="post-username">${post.username}</span>
                                      </div>
                                      <div class="post-title-container">
                                          <h3 class="posts-title">${post.titleText}</h3>
                                      </div>
                                  </div>
                              </div>
                          </div>`;
                  });
              }
              output += `</div>`;
              $("#postList").html(output).show();
              $("#userList").hide();
          },
          error: function(xhr, status, error) {
              console.error("Error:", error);
              $("#postList").html("<p>Error loading posts.</p>").show();
          }
      });
  }
  
  // Add this click handler
  $(document).on('click', '.post-card', function() {
      const postId = $(this).data('post-id');
      // Update URL without navigating
      window.history.pushState({}, "", `?id=${postId}`);
      
      // Show overlay using View_Post.js functionality
      const overlay = document.getElementById("overlay1");
      overlay.style.visibility = "visible";
      overlay.style.opacity = "1";
      
      // Fetch and display post details
      $.ajax({
          url: 'fetch_post_details.php',
          type: 'GET',
          data: { post_id: postId },
          success: function(data) {
              console.log('Received data:', data);
              
              if (!data.success) {
                  console.error('Error:', data.error);
                  alert('Error: ' + data.error);
                  return;
              }
              
              const post = data.post;
              
              // Update like button state
              const $likeBtn = $('#like-btn');
              $likeBtn.attr('data-post-id', post.PostID);
              
              // Set initial like state
              if (data.is_liked) {
                  $likeBtn.addClass('liked');
              } else {
                  $likeBtn.removeClass('liked');
              }
              
              // Update overlay content
              $('.imgContainer img').attr('src', `../!! Images/${post.fileImage}`);
              $('.posts-title').text(post.titleText);
              $('.post-text').text(post.captionText);
              
              // Update comments
              let commentHTML = '';
              if (data.comments && data.comments.length > 0) {
                  data.comments.forEach(comment => {
                      commentHTML += `
                          <div class="commentItem">
                              <p><strong>${comment.username}</strong>: ${comment.comment}</p>
                              <small>${comment.date}</small>
                          </div>`;
                  });
              } else {
                  commentHTML = '<p class="noComments">Start The Comments!</p>';
              }
              $('.commentList').html(commentHTML);
              
              // Update form post ID
              $('input[name="post_id"]').val(postId);
          },
          error: function(xhr, status, error) {
              console.error('Error fetching post details:', error);
          }
      });
  });
  
  // Add this new event delegation handler after the existing code
  $(document).on('click', '#like-btn', function(e) {
      e.preventDefault();
      e.stopPropagation();  // Prevent event bubbling
      const $thisButton = $(this);
      const postId = $thisButton.attr('data-post-id');
      console.log("Attempting to like post:", postId);
  
      if (!postId) {
          console.error("No post ID found");
          return;
      }
  
      $.ajax({
          url: "handle_like.php",
          type: "POST",
          data: { post_id: postId },
          dataType: 'json',
          success: function(response) {
              console.log("Server response:", response);
              if (response.success) {
                  $thisButton.toggleClass("liked");
                  // Update like count if it exists
                  const $likeCount = $thisButton.siblings('.like-count');
                  if ($likeCount.length) {
                      $likeCount.text(response.like_count);
                  }
              } else {
                  console.error("Like error:", response.error);
                  alert(response.error || "Error processing like");
              }
          },
          error: function(xhr, status, error) {
              console.error("Ajax error:", error);
              alert("Error processing like. Please try again.");
          }
      });
  });
  });
  