// Main JavaScript file for Social Media Website

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializePostForm();
    initializeLikes();
    initializeComments();
    initializeLocationPicker();
});

// Post Form Handler
function initializePostForm() {
    const postForm = document.getElementById('postForm');
    if (postForm) {
        postForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('ajax/create_post.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    // Clear form
                    this.reset();
                    // Reload page to show new post
                    location.reload();
                } else {
                    alert(result.message || 'Error creating post');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error creating post');
            }
        });
    }
}

// Like/Unlike Post
function toggleLike(postId) {
    fetch('ajax/like_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ post_id: postId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            const likeBtn = document.querySelector(`[data-post-id="${postId}"] .btn-like`);
            const likeCount = document.querySelector(`[data-post-id="${postId}"] .like-count`);
            
            if (data.liked) {
                likeBtn.classList.add('liked');
            } else {
                likeBtn.classList.remove('liked');
            }
            
            if (likeCount) {
                likeCount.textContent = data.like_count + ' likes';
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

// Initialize Like Buttons
function initializeLikes() {
    document.querySelectorAll('.btn-like').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.closest('.post').dataset.postId;
            toggleLike(postId);
        });
    });
}

// Show Comments
function showComments(postId) {
    const commentsSection = document.querySelector(`[data-post-id="${postId}"] .comments-section`);
    if (commentsSection.style.display === 'none') {
        commentsSection.style.display = 'block';
        loadComments(postId);
    } else {
        commentsSection.style.display = 'none';
    }
}

// Load Comments
function loadComments(postId) {
    fetch(`ajax/get_comments.php?post_id=${postId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const commentsList = document.querySelector(`[data-post-id="${postId}"] .comments-list`);
                commentsList.innerHTML = data.comments.map(comment => `
                    <div class="comment">
                        <img src="images/${comment.profile_pic}" alt="${comment.username}" class="comment-profile-pic">
                        <div class="comment-content">
                            <strong>${comment.full_name}</strong>
                            <p>${comment.comment}</p>
                            <span class="comment-time">${comment.created_at}</span>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => console.error('Error:', error));
}

// Post Comment
function postComment(postId) {
    const commentInput = document.querySelector(`[data-post-id="${postId}"] .comment-input`);
    const comment = commentInput.value.trim();
    
    if (!comment) return;
    
    fetch('ajax/comment_post.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            post_id: postId,
            comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            commentInput.value = '';
            loadComments(postId);
        } else {
            alert(data.message || 'Error posting comment');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Initialize Comments
function initializeComments() {
    document.querySelectorAll('.btn-comment').forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.closest('.post').dataset.postId;
            showComments(postId);
        });
    });
}

// Location Picker
let map, marker, autocomplete;

function initializeLocationPicker() {
    const locationSearch = document.getElementById('locationSearch');
    if (!locationSearch || !google) return;
    
    // Initialize autocomplete
    autocomplete = new google.maps.places.Autocomplete(locationSearch);
    
    // Initialize map
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 0, lng: 0 },
        zoom: 13
    });
    
    marker = new google.maps.Marker({
        map: map,
        draggable: true
    });
    
    // Listen for place selection
    autocomplete.addListener('place_changed', function() {
        const place = autocomplete.getPlace();
        
        if (!place.geometry) return;
        
        const location = place.geometry.location;
        map.setCenter(location);
        marker.setPosition(location);
        
        // Update hidden fields
        document.getElementById('location_lat').value = location.lat();
        document.getElementById('location_lng').value = location.lng();
        document.getElementById('location_name').value = place.formatted_address;
    });
    
    // Listen for marker drag
    marker.addListener('dragend', function() {
        const position = marker.getPosition();
        document.getElementById('location_lat').value = position.lat();
        document.getElementById('location_lng').value = position.lng();
        
        // Reverse geocode to get address
        const geocoder = new google.maps.Geocoder();
        geocoder.geocode({ location: position }, function(results, status) {
            if (status === 'OK' && results[0]) {
                document.getElementById('location_name').value = results[0].formatted_address;
                document.getElementById('locationSearch').value = results[0].formatted_address;
            }
        });
    });
}

// Toggle Location Picker
function toggleLocationPicker() {
    const picker = document.getElementById('locationPicker');
    if (picker.style.display === 'none') {
        picker.style.display = 'block';
        
        // Get user's current location
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                };
                map.setCenter(pos);
                marker.setPosition(pos);
            });
        }
    } else {
        picker.style.display = 'none';
    }
}

// Report User
function reportUser(userId) {
    const reason = prompt('Please provide a reason for reporting this user:');
    if (!reason) return;
    
    fetch('ajax/report_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            reported_user_id: userId,
            reason: reason
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('User has been reported. Thank you for helping keep our community safe.');
        } else {
            alert(data.message || 'Error reporting user');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Block User
function blockUser(userId) {
    if (!confirm('Are you sure you want to block this user?')) return;
    
    fetch('ajax/block_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            blocked_user_id: userId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Error blocking user');
        }
    })
    .catch(error => console.error('Error:', error));
}

// Send Message
function sendMessage(receiverId) {
    const messageInput = document.getElementById('messageInput');
    const message = messageInput.value.trim();
    
    if (!message) return;
    
    fetch('ajax/send_message.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({ 
            receiver_id: receiverId,
            message: message
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageInput.value = '';
            loadMessages(receiverId);
        } else {
            alert(data.message || 'Error sending message');
        }
    })
    .catch(error => console.error('Error:', error));
}