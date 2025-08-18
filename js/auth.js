document.addEventListener('DOMContentLoaded', function() {
    // Tab switching
    const tabs = document.querySelectorAll('.tab-btn');
    const loginForm = document.getElementById('loginForm');
    let currentTab = 'patient';

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            // Remove active class from all tabs
            tabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            this.classList.add('active');
            currentTab = this.getAttribute('data-tab');
        });
    });

    // Form submission
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        // Basic validation
        if (!username || !password) {
            alert('Please fill in all fields');
            return;
        }

        // Here you would typically make an API call to your backend
        console.log(`Logging in as ${currentTab} with:`, { username, password });
        
        // For demo purposes, redirect based on user type
        // In a real application, you would handle the login response from your server
        switch(currentTab) {
            case 'patient':
                window.location.href = '../patient/dashboard.html';
                break;
            case 'doctor':
                window.location.href = '../doctor/dashboard.html';
                break;
            case 'admin':
                window.location.href = '../admin/dashboard.html';
                break;
        }
    });
});
