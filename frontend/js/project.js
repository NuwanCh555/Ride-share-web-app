document.addEventListener('DOMContentLoaded', () => {
    const menuIcon = document.querySelector('.menu-icon');
    const navMenu = document.querySelector('nav ul');

    if (menuIcon) {
        menuIcon.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // Close menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.navbar') && navMenu.classList.contains('active')) {
            navMenu.classList.remove('active');
        }
    });

    // Dynamic Vehicles Fetching
    const vehiclesContainer = document.querySelector('.events');
    if (vehiclesContainer && window.location.pathname.endsWith('index.html')) {
        fetch('../backend/get_vehicles.php')
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success' && data.data.length > 0) {
                    vehiclesContainer.innerHTML = ''; // Clear hardcoded HTML
                    data.data.forEach(vehicle => {
                        const card = `
                            <a href="${vehicle.page_url}" class="event" data-id="${vehicle.id}">
                                <img src="${vehicle.image_url}" alt="${vehicle.name}">
                                <div class="event-content">
                                    <p>${vehicle.category}</p>
                                    <span class="btn">View Details</span>
                                </div>
                            </a>
                        `;
                        vehiclesContainer.innerHTML += card;
                    });
                }
            })
            .catch(err => console.error("Error loading vehicles: ", err));
    }

    // Async Login Handling
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', async (e) => {
            // 1. MUST be the very first line to stop the page from refreshing!
            e.preventDefault();
            
            const btn = loginForm.querySelector('button[type="submit"]') || loginForm.querySelector('.btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Processing...';
            btn.style.opacity = '0.7';
            btn.disabled = true; // Prevent double clicks that cancel requests

            const formData = new FormData(loginForm);
            
            try {
                // 2. Point to the correct backend PHP path
                const res = await fetch('../backend/login.php', { 
                    method: 'POST', 
                    body: formData 
                });
                
                const data = await res.json();
                
                if (data.status === 'success') {
                    alert(data.message || 'Login successful!');
                    if (data.role && data.role.toLowerCase() === 'driver') {
                        window.location.href = 'driver_dashboard.html';
                    } else {
                        window.location.href = 'index.php';
                    }
                } else {
                    alert(data.message || 'Invalid credentials');
                }
            } catch (err) {
                // 3. Robust error catch
                console.error('Fetch error:', err);
                alert('Network Error - Please check your connection or console.');
            } finally {
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
                btn.disabled = false;
            }
        });
    }

    // Async Register Handling
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = registerForm.querySelector('.btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Processing...';
            btn.style.opacity = '0.7';

            const formData = new FormData(registerForm);
            try {
                const res = await fetch('../backend/register.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                alert(data.message);
                if (data.status === 'success') {
                    window.location.href = 'login.html';
                }
            } catch (err) {
                alert('An error occurred. Please try again.');
            } finally {
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
            }
        });
    }

    // Async Booking Handling
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = bookingForm.querySelector('.btn');
            const originalText = btn.innerHTML;
            btn.innerHTML = 'Processing...';
            btn.style.opacity = '0.7';

            const formData = new FormData(bookingForm);
            // Append a dummy vehicle_id based on the page (in production, pass via hidden input)
            formData.append('vehicle_id', 1); // Mock ID for demonstration

            try {
                const res = await fetch('../backend/book_vehicle.php', { method: 'POST', body: formData });
                const data = await res.json();
                
                alert(data.message);
                if (data.status === 'success') {
                    bookingForm.reset();
                } else if (data.message.includes('logged in')) {
                    window.location.href = 'login.html';
                }
            } catch (err) {
                alert('An error occurred. Please try again.');
            } finally {
                btn.innerHTML = originalText;
                btn.style.opacity = '1';
            }
        });
    }
});