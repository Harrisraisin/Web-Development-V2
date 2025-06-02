<?php
require_once 'config.php';
requireLogin();

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile page - SocialBook - Easy Tutorials</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/c4254e24a8.js" crossorigin="anonymous"></script>
</head>
<body>
    
    <nav>
        <div class="nav-left">
            <a href="index.html"><img src="images/logo2.png" class="logo"></a>
        <ul>
           
            <li><img src="images/notification.png"></li>
            <li><img src="images/inbox.png"></li>
            <li><img src="images/video.png"></li>
        </ul>
        </div>
        
        <div class="nav-right">
            <div class="search-box">
                <img src="images/search.png">
                <input type="text" placeholder="Search">
            </div>
            <div class="nav-user-icon online" onclick="settingsMenuToggle()">
                <img src="images/profile-pic.png">
            </div>
            
        </div>
        <!-- ------dropdown-settings-menu--------- -->
        <div class="settings-menu">
            <div id="dark-btn">
                <span></span>
            </div>
            <div class="settings-menu-inner">
                <div class="user-profile">
                    <img src="images/profile-pic.png">
                    <div>
                        <p>John Nicholson</p>
                        <a href="profile.html">See your profile</a>
                    </div>
                </div>
                <hr>
                <div class="user-profile">
                    <img src="images/feedback.png">
                    <div>
                        <p>Give Feedback</p>
                        <a href="">Help us improve the new deisgn</a>
                    </div>
                </div>
                <hr>
                <div class="settings-links">
                    <img src="images/setting.png" class="settings-icon">
                    <a href="">Settings & Privacy <img src="images/arrow.png" width="10px"></a>
                </div>
                <div class="settings-links">
                    <img src="images/help.png" class="settings-icon">
                    <a href="">Help & Support <img src="images/arrow.png" width="10px"></a>
                </div>
                <div class="settings-links">
                    <img src="images/display.png" class="settings-icon">
                    <a href="">Display & Accessibility <img src="images/arrow.png" width="10px"></a>
                </div>
                <div class="settings-links">
                    <img src="images/logout.png" class="settings-icon">
                    <a href="">Logout <img src="images/arrow.png" width="10px"></a>
                </div>
            </div>
            
        </div>
    </nav>

    <div class="profile-container">
        <img src="images/cover.png" class="cover-img">
        <div class="profile-details">
            <div class="pd-left">
                <div class="pd-row">
                    <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>" class="pd-image">
                    <div>
                        <h3><?php echo htmlspecialchars($user['full_name']); ?></h3>
                        <p><?php echo htmlspecialchars($user['bio'] ?? ''); ?></p>
                        <img src="images/member-1.png">
                        <img src="images/member-2.png">
                        <img src="images/member-3.png">
                        <img src="images/member-4.png">
                    </div>
                </div>
            </div>
            <div class="pd-right">

                <button type="button"><img src="images/add-friends.png"> Friend</button>
                <button type="button"><img src="images/message.png"> Message</button>
                <br>
                <a href=""><img src="images/more.png"></a>
            </div>
        </div>

        <div class="profile-info">
            <div class="info-col">

                <div class="profile-intro">
                    <h3>Intro</h3>
                    <p class="intro-text">Believe in yourself and you can do unbelievable things. <img src="images/feeling.png"></p>
                    <hr>
                    <ul>
                        <li><img src="images/profile-job.png"> Director at 99media Ltd</li>
                        <li><img src="images/profile-study.png"> Studied at Amity University</li>
                        <li><img src="images/profile-study.png"> Went to DPS Delhi</li>
                        <li><img src="images/profile-home.png"> Lives in Bangalore, India</li>
                        <li><img src="images/profile-location.png"> From Bangalore, India</li>
                    </ul>
                </div>

                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Photos</h3>
                        <a href="">All Photos</a>
                    </div>
                    
                    <div class="photo-box">
                        <div><img src="images/photo1.png"></div>
                        <div><img src="images/photo2.png"></div>
                        <div><img src="images/photo3.png"></div>
                        <div><img src="images/photo4.png"></div>
                        <div><img src="images/photo5.png"></div>
                        <div><img src="images/photo6.png"></div>
                    </div>
                </div>

                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Friends</h3>
                        <a href="">All Friends</a>
                    </div>
                    <p>120 (10 mutual)</p>
                    <div class="friends-box">
                        <div><img src="images/member-1.png"> <p>Joseph N</p></div>
                        <div><img src="images/member-2.png"> <p>Nathan N</p></div>
                        <div><img src="images/member-3.png"> <p>George D</p></div>
                        <div><img src="images/member-4.png"> <p>Francis L</p></div>
                        <div><img src="images/member-5.png"> <p>Anthony E</p></div>
                        <div><img src="images/member-6.png"> <p>Michael A</p></div>
                        <div><img src="images/member-7.png"> <p>Edward M</p></div>
                        <div><img src="images/member-8.png"> <p>Bradon C</p></div>
                        <div><img src="images/member-9.png"> <p>James Doe</p></div>
                    </div>
                </div>

                



            </div>



            <div class="post-col">
                <div class="write-post-container">
                    <div class="user-profile">
                        <img src="images/profile-pic.png">
                        <div>
                            <p>John Nicholson</p>
                            <small>Public <i class="fas fa-caret-down"></i></small>
                        </div>
                    </div>
        
                    <div class="post-input-container">
                        <textarea rows="3" placeholder="What's on your mind, Jack?"></textarea>
                        <div class="add-post-links">
                            <a href="#"><img src="images/live-video.png"> Live Video</a>
                            <a href="#"><img src="images/photo.png"> Photo/Video</a>
                            <a href="#"><img src="images/feeling.png"> Feeling/Activity</a>
                        </div>
                    </div>
                </div>
                <div class="post-container">
                    <div class="post-row">
                        <div class="user-profile">
                            <img src="images/profile-pic.png">
                            <div>
                                <p>John Nicholson</p>
                                <span>June 24 2021, 13:40 pm</span>
                            </div>
                        </div>
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </div>
                    <p class="post-text">Subscribe <span>@Easy Tutorials</span> YouTube channel to watch more videos on website developement and UI designs. <a href="">#EasyTutorials</a> <a href="">#YouTubeChannel</a></p>
                    <img src="images/feed-image-1.png" class="post-img">
                    <div class="post-row">
                        <div class="activity-icons">
                            <div><img src="images/like-blue.png"> 120</div>
                            <div><img src="images/comments.png"> 45</div>
                            <div><img src="images/share.png"> 20</div>
                        </div>
                        <div class="post-profile-icon">
                            <img src="images/profile-pic.png"> <i class="fas fa-caret-down"></i>
                        </div>
                    </div>
        
                </div>
        
                <div class="post-container">
                    <div class="post-row">
                        <div class="user-profile">
                            <img src="images/profile-pic.png">
                            <div>
                                <p>John Nicholson</p>
                                <span>June 24 2021, 13:40 pm</span>
                            </div>
                        </div>
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </div>
                    <p class="post-text">Like and share this video with friends, tag <span>@Easy Tutorials</span> facebook page on your post. Ask you doubts in the comments <a href="">#EasyTutorials</a> <a href="">#Subscribe</a></p>
                    <img src="images/feed-image-2.png" class="post-img">
                    <div class="post-row">
                        <div class="activity-icons">
                            <div><img src="images/like.png"> 120</div>
                            <div><img src="images/comments.png"> 45</div>
                            <div><img src="images/share.png"> 20</div>
                        </div>
                        <div class="post-profile-icon">
                            <img src="images/profile-pic.png"> <i class="fas fa-caret-down"></i>
                        </div>
                    </div>
        
                </div>
        
                <div class="post-container">
                    <div class="post-row">
                        <div class="user-profile">
                            <img src="images/profile-pic.png">
                            <div>
                                <p>John Nicholson</p>
                                <span>June 24 2021, 13:40 pm</span>
                            </div>
                        </div>
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </div>
                    <p class="post-text">Like and share this video with friends, tag <span>@Easy Tutorials</span> facebook page on your post. Ask you doubts in the comments <a href="">#EasyTutorials</a> <a href="">#Subscribe</a></p>
                    <img src="images/feed-image-3.png" class="post-img">
                    <div class="post-row">
                        <div class="activity-icons">
                            <div><img src="images/like.png"> 120</div>
                            <div><img src="images/comments.png"> 45</div>
                            <div><img src="images/share.png"> 20</div>
                        </div>
                        <div class="post-profile-icon">
                            <img src="images/profile-pic.png"> <i class="fas fa-caret-down"></i>
                        </div>
                    </div>
        
                </div>
        
                <div class="post-container">
                    <div class="post-row">
                        <div class="user-profile">
                            <img src="images/profile-pic.png">
                            <div>
                                <p>John Nicholson</p>
                                <span>June 24 2021, 13:40 pm</span>
                            </div>
                        </div>
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </div>
                    <p class="post-text">Like and share this video with friends, tag <span>@Easy Tutorials</span> facebook page on your post. Ask you doubts in the comments <a href="">#EasyTutorials</a> <a href="">#Subscribe</a></p>
                    <img src="images/feed-image-4.png" class="post-img">
                    <div class="post-row">
                        <div class="activity-icons">
                            <div><img src="images/like.png"> 120</div>
                            <div><img src="images/comments.png"> 45</div>
                            <div><img src="images/share.png"> 20</div>
                        </div>
                        <div class="post-profile-icon">
                            <img src="images/profile-pic.png"> <i class="fas fa-caret-down"></i>
                        </div>
                    </div>
        
                </div>
        
                <div class="post-container">
                    <div class="post-row">
                        <div class="user-profile">
                            <img src="images/profile-pic.png">
                            <div>
                                <p>John Nicholson</p>
                                <span>June 24 2021, 13:40 pm</span>
                            </div>
                        </div>
                        <a href="#"><i class="fas fa-ellipsis-v"></i></a>
                    </div>
                    <p class="post-text">Like and share this video with friends, tag <span>@Easy Tutorials</span> facebook page on your post. Ask you doubts in the comments <a href="">#EasyTutorials</a> <a href="">#Subscribe</a></p>
                    <img src="images/feed-image-5.png" class="post-img">
                    <div class="post-row">
                        <div class="activity-icons">
                            <div><img src="images/like.png"> 120</div>
                            <div><img src="images/comments.png"> 45</div>
                            <div><img src="images/share.png"> 20</div>
                        </div>
                        <div class="post-profile-icon">
                            <img src="images/profile-pic.png"> <i class="fas fa-caret-down"></i>
                        </div>
                    </div>
        
                </div>
                
                <button type="button" class="load-more-btn">Load More</button>

               </div>
<!-- ----------right sidebar----------- -->
               <div class="right-sidebar">
                   <div class="sidebar-title">
                       <h4>Events</h4>
                       <a href="#">See All</a>
                   </div>

                   <div class="event">
                       <div class="event-left">
                            <h3>18</h3>
                            <span>March</span>
                       </div>
                       <div class="event-right">
                           <h4>Social Media</h4>
                           <p><i class="fas fa-map-marker-alt"></i> Willson Tech Park</p>
                           <a href="#">More Info</a>
                       </div>
                   </div>
                   <div class="event">
                    <div class="event-left">
                         <h3>22</h3>
                         <span>March</span>
                    </div>
                    <div class="event-right">
                        <h4>Mobile Marketing</h4>
                        <p><i class="fas fa-map-marker-alt"></i> Willson Tech Park</p>
                        <a href="#">More Info</a>
                    </div>
                </div>

                <div class="sidebar-title">
                    <h4>Advertisement</h4>
                    <a href="#">Close</a>
                </div>

                <img src="images/advertisement.png" class="sidebar-ad">

                <div class="sidebar-title">
                    <h4>Conversation</h4>
                    <a href="#">Hide Chat</a>
                </div>

                <div class="online-list">
                    <div class="online"><img src="images/member-1.png"></div>
                    <p>Alison Mina</p>
                </div>
                <div class="online-list">
                    <div class="online"><img src="images/member-2.png"></div>
                    <p>Jackson Aston</p>
                </div>
                <div class="online-list">
                    <div class="online"><img src="images/member-3.png"></div>
                    <p>Samona Rose</p>
                </div>
                

               </div>
            </div>
            <div class="footer">
                <p>Copyright 2021 - Easy Tutorials YouTube Channel</p>
            </div>

            <script src="script.js"></script>
        </body>
        </html>