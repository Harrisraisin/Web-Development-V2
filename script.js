var settingsMenu = document.querySelector(".settings-menu");


function settingsMenuToggle(){
    settingsMenu.classList.toggle("settings-menu-height");
}

// -----------dark mode button------------

var darkBtn = document.getElementById("dark-btn");

darkBtn.onclick = function(){
    darkBtn.classList.toggle('dark-btn-on');
    document.body.classList.toggle("dark-theme");

    if(document.body.classList.contains("dark-theme")) {
        document.cookie = "theme=dark;path=/;max-age=31536000"; // 1 year
    } else {
        document.cookie = "theme=light;path=/;max-age=31536000";
    }
}

// Check theme on load
window.onload = function() {
    if(document.cookie.includes('theme=dark')) {
        darkBtn.classList.add('dark-btn-on');
        document.body.classList.add("dark-theme");
    }
}
