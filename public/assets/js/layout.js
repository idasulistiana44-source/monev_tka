document.addEventListener('DOMContentLoaded', function() {
    const sidebarToggle = document.getElementById('sidebarToggle');
    const notificationButton = document.getElementById('notificationButton');
    const notificationMenu = document.getElementById('notificationMenu');
    const profileButton = document.getElementById('profileButton');
    const userMenu = document.getElementById('userMenu');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            document.body.classList.toggle('sidebar-collapsed');
        });
    }

    if (notificationButton && notificationMenu) {
        notificationButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            notificationMenu.classList.toggle('show');
            if (userMenu) {
                userMenu.classList.remove('show');
            }
        });
    }

    if (profileButton && userMenu) {
        profileButton.addEventListener('click', function(event) {
            event.preventDefault();
            event.stopPropagation();
            userMenu.classList.toggle('show');
            if (notificationMenu) {
                notificationMenu.classList.remove('show');
            }
        });
    }
    document.addEventListener('click', function(event) {
        if (notificationMenu && notificationButton && !notificationMenu.contains(event.target) && !notificationButton.contains(event.target)) {
            notificationMenu.classList.remove('show');
        }

        if (userMenu && profileButton && !userMenu.contains(event.target) && !profileButton.contains(event.target)) {
            userMenu.classList.remove('show');
        }
    });
    function showModalAlert(selector,message,type='error') {
        const alert=$(selector);
        const icon=type==='success'?'fa-check-circle':'fa-exclamation-circle';
        alert.removeClass('success error');
        alert.addClass(type);
        alert.html('<i class="fas '+icon+'"></i><span>'+message+'</span>');
        alert.addClass('show');
    }
    function hideModalAlert(selector) {
        $(selector).removeClass('show').html('');
    }
});

$(document).on('keydown', 'input[type="number"]', function(e) {
  const invalidKeys = ['e', 'E', '-', '+', '.', ',', 'ArrowUp', 'ArrowDown'];
  if (invalidKeys.includes(e.key)) {
    e.preventDefault();
  }
});
$(document).on('wheel', 'input[type="number"]', function(e) {
  $(this).blur(); 
});
$(document).on('input', 'input[type="number"]', function() {
  this.value = this.value.replace(/[^0-9]/g, '');
});

if (sidebarToggle) {
    sidebarToggle.addEventListener('click', function(event) {
        event.preventDefault();
        event.stopPropagation();

        if (window.innerWidth <= 991.98) {
            document.querySelector('.app-sidebar').classList.toggle('show');
        } else {
            document.body.classList.toggle('sidebar-collapsed');
        }
    });
}