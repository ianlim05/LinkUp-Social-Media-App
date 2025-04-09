document.addEventListener("DOMContentLoaded", function () {
    const contentBox = document.getElementById("content-box");
    // Variable contentbox used for dynamically updating the content displayed in setting panels.

    // THEY ARE ALL ON CLIENT SIDED FOR TESTING, THESE WILL BE CHANGED WHEN DOING DATABASE !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

    // Screen Time Variables ============================================================
    let screenTimeLimit = 0; // Total screen time limit in minutes
    let remainingTime = 0; // Remaining screen time in minutes
    let timer = null; // To store the countdown timer

    // Content Box Variable ============================================================
    const settingsContent = {
        // Option - Profile
        "profile-settings": `
            <div class="profile-settings">
                <h3>Profile Settings</h3>
                <form id="profile-form">
                    <div id="current-profile-pic"></div>
                    <label for="profile-picture">Profile Picture:</label>
                    <input type="file" id="profile-picture" accept="image/*">
                    
                    <label for="name">Username:</label>
                    <input type="text" id="name" placeholder="Enter your username">
                    
                    <label for="bio">Bio:</label>
                    <textarea id="bio" placeholder="Enter your bio"></textarea>
                    
                    <button type="submit">Save Changes</button>
                </form>
            </div>`
        ,
        // Option - Visibility
        "manage-visibility": `
            <div class="visibility-toggle">
                <h3>Manage Visibility</h3>
                <p id="current-visibility">Current Visibility: </p>
                <label for="visibility">Profile Visibility:</label>

                <!-- Dropdown menu for visibility settings -->
                <select id="visibility">
                    <option value="public">Public</option>
                    <option value="private">Private</option>
                </select>
                
                <!-- Save button to update visibility -->
                <button onclick="saveVisibility()">Save</button>
            </div>
        `,
        // Option - Block Users
        "block-users": `
            <div class="blocked-users-list">
                <h3>Blocked Users</h3>
                <ul id="blocked-users-list">
                    <!-- Blocked users will be dynamically populated here -->
                </ul>
            </div>
        `,
        // Option - Screen-time
        "screen-time": `
            <div class="screen-time-setup">
                <h3>Screen Time Reminders</h3>
                <label for="hours">Hours:</label>
                <input type="number" id="hours" min="0">

                <label for="minutes">Minutes:</label>
                <input type="number" id="minutes" min="0" max="59">

                <p id="saved-time">Saved Time: </p>

                <button onclick="saveScreenTime()">Save</button>
                <button onclick="startScreenTime()">On</button>
                <button onclick="stopScreenTime()">Off</button>
                <p id="screen-time-status">Status: Off</p>
            </div>
        `
    };
    
    // Add click event listeners to each setting option in the menu
    document.querySelectorAll(".settings-menu li[data-option]").forEach(item => {
        item.addEventListener("click", function () {
            const option = this.getAttribute("data-option");
            
            // Remove active class from all menu items
            document.querySelectorAll(".settings-menu li").forEach(menuItem => {
                menuItem.classList.remove('active');
            });
            
            // Add active class to clicked item
            this.classList.add('active');
            
            contentBox.innerHTML = settingsContent[option] || "<p>Select a setting option</p>";

            // If visibility option is selected
            if (option === "manage-visibility") {
                getCurrentVisibility();
            }

            // If screen time reminder option is selected
            if (option === "screen-time") {
                fetchSavedScreenTime();
            }

            // If block-users option is selected
            if (option === "block-users") {
                fetchBlockedUsers();
            }

            // If profile settings option is selected
            if (option === "profile-settings") {
                // First fetch the current profile data
                fetch("../Setting/Backend/fetch_profile.php")
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Set current values
                            document.getElementById("name").value = data.data.username || '';
                            document.getElementById("bio").value = data.data.bio || '';
                            
                            // Show current profile picture if it exists
                            if (data.data.profile_picture) {
                                const currentPicDiv = document.getElementById("current-profile-pic");
                                const img = document.createElement('img');
                                img.src = `../!! Images/${data.data.profile_picture}`;
                                img.style.width = '200px';
                                img.style.height = '200px';
                                img.style.objectFit = 'cover';
                                img.style.marginBottom = '10px';
                                img.style.borderRadius = '50%';
                                img.style.border = '2px solid #4a90e2';
                                img.style.boxShadow = '0 2px 4px rgba(0, 0, 0, 0.1)';
                                
                                // Style the container div
                                currentPicDiv.style.textAlign = 'center';
                                currentPicDiv.style.marginBottom = '20px';
                                
                                // Style the file input
                                const fileInput = document.getElementById('profile-picture');
                                fileInput.style.width = '100%';
                                fileInput.style.marginBottom = '20px';
                                
                                currentPicDiv.innerHTML = '';
                                currentPicDiv.appendChild(img);
                            }
                        }
                    })
                    .catch(error => {
                        console.error("Error fetching profile:", error);
                    });

                // Handle form submission
                const profileForm = document.getElementById("profile-form");
                if (profileForm) {
                    profileForm.addEventListener("submit", function (event) {
                        event.preventDefault();
                        
                        // Create FormData object
                        const formData = new FormData();
                        
                        // Add form fields to FormData
                        const name = document.getElementById("name").value;
                        const bio = document.getElementById("bio").value;
                        const profilePicture = document.getElementById("profile-picture").files[0];
                        
                        formData.append("username", name);
                        formData.append("bio", bio);
                        if (profilePicture) {
                            formData.append("profile_picture", profilePicture);
                        }
                        
                        // Send data to backend
                        fetch("../Setting/Backend/update_profile.php", {
                            method: "POST",
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.status === 'success') {
                                // Update header username and profile picture
                                const headerUsername = document.querySelector('.h-span');
                                const headerProfilePic = document.querySelector('.h-profile img');
                                
                                if (headerUsername) {
                                    headerUsername.textContent = data.newUsername;
                                }
                                
                                if (headerProfilePic && data.newProfilePicture) {
                                    headerProfilePic.src = data.newProfilePicture;
                                }
                                
                                alert("Profile updated successfully!");
                                window.location.reload();
                            } else {
                                alert("Error: " + data.message);
                            }
                        })
                        .catch(error => {
                            console.error("Error:", error);
                            alert("An error occurred while updating the profile");
                        });
                    });
                }
            }
        });
    });

    // Function to handle unblocking users
    window.unblockUser = function(username) {
        alert(`Unblocked ${username}`);
        // Add logic to unblock the user
    };

    // Save Screen Time Logics ==============================================================
    // Function to save the screen time reminder
    window.saveScreenTime = function() {
        // Get the hours and minutes from the input fields
        const hours = parseInt(document.getElementById('hours').value) || 0;
        const minutes = parseInt(document.getElementById('minutes').value) || 0;
    
        // Validate hours and minutes
        if (hours < 0 || hours > 24) {
            alert("Hours must be between 0 and 24.");
            return;
        }
        if (minutes < 0 || minutes > 59) {
            alert("Minutes must be between 0 and 59.");
            return;
        }
    
        // Convert to total minutes
        const totalMinutes = (hours * 60) + minutes;
    
        // Validate total time (must be between 1 minute and 24 hours)
        if (totalMinutes < 1 || totalMinutes > 1440) { // 1440 minutes = 24 hours
            alert("Total time must be between 00:01:00 and 24:00:00.");
            return;
        }
    
        // Create a FormData object to send the data
        const formData = new FormData();
        formData.append("screen_time_reminder", totalMinutes); // Add the total minutes to the form data
    
        // Debug: Log the FormData values
        for (let [key, value] of formData.entries()) {
            console.log(key, value);
        }
    
        // Send a POST request to update_screen_time.php using Fetch API
        fetch("../Setting/Backend/update_time_reminder.php", {
            method: "POST", // Use POST method
            body: formData // Send the form data
        })
        .then(response => {
            return response.text();
        })
        .then(data => {
            // Check if the update was successful
            if (data === "success") {
                alert("Screen time reminder saved successfully!"); // Show success message
            } else {
                alert("Failed to save screen time reminder: " + data); // Show error message
            }
        })
        .catch(error => {
            // Handle any errors that occur during the request
            console.error("Error:", error); // Log the error to the console
            alert("An error occurred while saving the screen time reminder. Please check the console for details.");
        });
    };

    // Function to start the screen time reminder
    window.startScreenTime = function() {
        // Clear the previous timer if it exists
        if (timer) {
            clearInterval(timer);
        }
    
        // Fetch the saved screen time reminder from the database
        fetch("../Setting/Backend/fetch_screen_time.php")
            .then(response => response.text())
            .then(data => {
                if (data && data !== "00:00:00") { // Check if data is valid and not default
                    // Split the time into hours, minutes, and seconds
                    const [hours, minutes] = data.split(':').map(Number);
    
                    // Debug: Log the parsed hours and minutes
                    console.log("Hours:", hours);
                    console.log("Minutes:", minutes);
    
                    // Check if hours and minutes are valid numbers
                    if (isNaN(hours) || isNaN(minutes)) {
                        alert("Invalid screen time reminder format. Please set a valid time.");
                        return;
                    }
    
                    // Calculate total minutes from the saved value
                    const totalMinutes = (hours * 60) + minutes;
    
                    // Check if the total time is valid
                    if (totalMinutes < 1 || totalMinutes > 1440) { // 1440 minutes = 24 hours
                        alert("Invalid screen time reminder. Please set a valid time.");
                        return;
                    }
    
                    // Save the remaining time to localStorage
                    localStorage.setItem("remainingTime", totalMinutes);
    
                    // Start the countdown
                    startCountdown(totalMinutes);
    
                    // Update the status display immediately
                    const statusElement = document.getElementById('screen-time-status');
                    if (statusElement) {
                        statusElement.textContent = `Status: On (${Math.floor(totalMinutes / 60)}h ${totalMinutes % 60}m remaining)`;
                    }
                } else {
                    alert("No screen time reminder found. Please set a screen time limit first.");
                }
            })
            .catch(error => {
                console.error("Error fetching screen time:", error);
                alert("An error occurred while fetching the screen time reminder. Please check the console for details.");
            });
    };
    
    function startCountdown(totalMinutes) {
        let remainingTime = totalMinutes;
    
        // Clear the previous timer if it exists
        if (timer) {
            clearInterval(timer);
        }
    
        // Start a new timer
        timer = setInterval(() => {
            remainingTime--;
    
            // Update localStorage with the new remaining time
            localStorage.setItem("remainingTime", remainingTime);
    
            // Update the status display (only if the element exists)
            const statusElement = document.getElementById('screen-time-status');
            if (statusElement) {
                statusElement.textContent = `Status: On (${Math.floor(remainingTime / 60)}h ${remainingTime % 60}m remaining)`;
            }
    
            if (remainingTime <= 0) {
                clearInterval(timer);
                alert("Screen time limit reached!");
                localStorage.removeItem("remainingTime"); // Clear localStorage
    
                // Update the status display (only if the element exists)
                if (statusElement) {
                    statusElement.textContent = "Status: Off";
                }
            }
        }, 60000); // Update every minute (60,000 milliseconds)
    }

    // Function to stop the screen time reminder (client-side logic)
    window.stopScreenTime = function() {
        // Clear the timer if it exists
        if (timer) {
            clearInterval(timer);
            timer = null; // Reset the timer variable
        }
    
        // Clear localStorage
        localStorage.removeItem("remainingTime");
    
        // Update the status display
        const statusElement = document.getElementById('screen-time-status');
        if (statusElement) {
            statusElement.textContent = "Status: Off";
        }
    };

    // Function to fetch the screen time reminder from database and display to it.
    window.fetchSavedScreenTime = function() {
        fetch("../Setting/Backend/fetch_screen_time.php")
            .then(response => response.text())
            .then(data => {
                if (data && data !== "00:00:00") {
                    // Split the time into hours, minutes, and seconds
                    const [hours, minutes] = data.split(':').map(Number);

                    // Display the saved time in the <p> element
                    const savedTimeElement = document.getElementById('saved-time');
                    if (savedTimeElement) {
                        savedTimeElement.textContent = `Saved Time: ${hours}h ${minutes}m`;
                    }
                } else {
                    // If no saved time is found, display a default message
                    const savedTimeElement = document.getElementById('saved-time');
                    if (savedTimeElement) {
                        savedTimeElement.textContent = "Saved Time: Not set";
                    }
                }
            })
            .catch(error => console.error("Error fetching screen time:", error));
    };

    // Visibility Save Logic ================================================================
    // Function to save visibility settings
    window.saveVisibility = function() {
        // Get the selected visibility value from the dropdown
        const visibility = document.getElementById("visibility").value;

        // Create a FormData object to send the data
        const formData = new FormData();
        formData.append("visibility", visibility); // Add the visibility value to the form data

        // Send a POST request to update_visibility.php using Fetch API
        fetch("../Setting/Backend/update_visibility.php", {
            method: "POST", // Use POST method
            body: formData // Send the form data
        })
        .then(response => {
            return response.text();
        })
        .then(data => {
            // Check if the update was successful
            if (data === "success") {
                alert("Visibility settings updated successfully!"); // Show success message
            } else {
                alert("Failed to update visibility settings: " + data); // Show error message
            }
        })
        .catch(error => {
            // Handle any errors that occur during the request
            console.error("Error:", error); // Log the error to the console
            alert("An error occurred while updating visibility settings. Please check the console for details.");
        });
    };

    // Function to get current visibility setting
    window.getCurrentVisibility = function() {
        fetch("../Setting/Backend/fetch_visibility.php")
        .then(response => response.text())
        .then(data => {
            if (data === "public" || data === "private") {
                // Update the dropdown value
                const visibilitySelect = document.getElementById("visibility");
                if (visibilitySelect) {
                    visibilitySelect.value = data;
                }

                // Display the current visibility in the <p> element
                const currentVisibilityElement = document.getElementById('current-visibility');
                if (currentVisibilityElement) {
                    currentVisibilityElement.textContent = `Current Visibility: ${data}`;
                }
            }
        })
        .catch(error => console.error("Error:", error));
    }

    // Function to fetch and display blocked users
    window.fetchBlockedUsers = function() {
        fetch("../Setting/Backend/fetch_blocked_users.php")
            .then(response => response.text())
            .then(data => {
                const blockedUsersList = document.getElementById('blocked-users-list');
                blockedUsersList.innerHTML = ''; // Clear the list
    
                if (data) {
                    // Split the comma-separated string into an array
                    const blockedUsers = data.split(',');
    
                    // Populate the list with blocked users
                    blockedUsers.forEach(user => {
                        const [username, userId] = user.split(':'); // Split "username:id"
    
                        const listItem = document.createElement('li');
                        listItem.textContent = username;
    
                        // Add an unblock button
                        const unblockButton = document.createElement('button');
                        unblockButton.textContent = 'Unblock';
                        unblockButton.onclick = () => unblockUser(userId); // Pass the user ID to unblock
    
                        listItem.appendChild(unblockButton);
                        blockedUsersList.appendChild(listItem);
                    });
                } else {
                    console.error("No blocked users found.");
                }
            })
            .catch(error => console.error("Error fetching blocked users:", error));
    };
    
    // Function to handle unblocking users
    window.unblockUser = function(blockedUserId) {
        const formData = new FormData();
        formData.append('blockedUserId', blockedUserId);
    
        fetch("../Setting/Backend/unblock_user.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data === "User unblocked successfully") {
                alert(data); // Show success message
                fetchBlockedUsers(); // Refresh the blocked users list
            } else {
                alert(data); // Show error message
            }
        })
        .catch(error => console.error("Error unblocking user:", error));
    };
});