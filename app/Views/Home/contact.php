<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - St. Peter Hospital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        header {
            position: fixed;
            top: 0;
            width: 100%;
            padding: 15px 50px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            backdrop-filter: blur(15px);
            background: rgba(255, 255, 255, 0.4);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            z-index: 1000;
            transition: 0.3s ease;
        }
        
        header.scrolled {
            background: rgba(0, 119, 182, 0.85);
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        }
        
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #0077b6;
            text-decoration: none;
        }
        
        header.scrolled .logo {
            color: white;
        }
        
        nav ul {
            list-style: none;
            display: flex;
            gap: 30px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: 0.3s ease;
            padding: 8px 15px;
            border-radius: 5px;
        }
        
        header.scrolled nav ul li a {
            color: #fff;
        }
        
        nav ul li a:hover, nav ul li a.active {
            color: #ffdd57;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 200px 50px 100px;
            min-height: 40vh;
            background: linear-gradient(120deg, #f9fcff, #e3f7ff);
            text-align: center;
            animation: bgShift 10s infinite alternate ease-in-out;
            color: #333;
        }
        
        @keyframes bgShift {
            0% { background-position: left; }
            100% { background-position: right; }
        }
        
        .hero h1 {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #0077b6;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px 4rem;
        }
        
        .section-title {
            text-align: center;
            margin: 3rem 0;
            color: #0077b6;
        }
        
        .contact-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 40px;
            margin: 50px 0;
        }
        
        .contact-info {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: 0.4s ease;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .contact-info.visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        .contact-info h3 {
            color: #0077b6;
            margin-bottom: 1.5rem;
            font-size: 1.5rem;
        }
        
        .contact-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }
        
        .contact-icon {
            background: #e6f2ff;
            color: #0077b6;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .contact-text h4 {
            color: #333;
            margin-bottom: 0.3rem;
        }
        
        .contact-text p, .contact-text a {
            color: #666;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .contact-text a:hover {
            color: #0077b6;
        }
        
        .contact-form {
            background: rgba(255,255,255,0.8);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: 0.4s ease;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .contact-form.visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #555;
            font-weight: 500;
        }
        
        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: rgba(255,255,255,0.9);
        }
        
        .form-control:focus {
            outline: none;
            border-color: #0077b6;
            box-shadow: 0 0 0 3px rgba(0, 119, 182, 0.2);
            background: white;
        }
        
        textarea.form-control {
            min-height: 150px;
            resize: vertical;
        }
        
        .btn {
            background: #0077b6;
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 119, 182, 0.4);
            width: 100%;
            margin-top: 10px;
        }
        
        .btn:hover {
            background: #005f8a;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 95, 138, 0.5);
        }
        
        .map-container {
            margin: 60px 0;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: 0.4s ease;
            transform: translateY(20px);
            opacity: 0;
        }
        
        .map-container.visible {
            transform: translateY(0);
            opacity: 1;
        }
        
        .map-container iframe {
            width: 100%;
            height: 400px;
            border: none;
        }
        
        @media (max-width: 768px) {
            header {
                padding: 15px 20px;
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .hero {
                padding: 150px 20px 80px;
                min-height: auto;
            }
            
            .container {
                padding: 40px 20px;
            }
            
            .contact-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header id="navbar">
        <div class="logo">St. Peter Hospital Inc.</div>
        <nav>
            <ul>
                <li><a href="<?= site_url('/') ?>">Home</a></li>
                <li><a href="<?= site_url('services') ?>">Services</a></li>
                <li><a href="<?= site_url('doctors') ?>">Doctors</a></li>
                <li><a href="<?= site_url('contact') ?>" class="active">Contact</a></li>
                <li><a href="<?= site_url('login') ?>">Login</a></li>
            </ul>
        </nav>
    </header>
    
    <section class="hero">
        <div>
            <h1>Contact Us</h1>
            <p>We're here to help and answer any questions you might have</p>
        </div>
    </section>
    
    <div class="container">
        <div class="contact-container">
            <div class="contact-info">
                <h3>Get in Touch</h3>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Our Location</h4>
                        <p>Ramon Magsaysay Memorial Colleges <br>South cotabato, G,S,C, Philippines 9500</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Phone Number</h4>
                        <p>Main: (63) 0978150583<br>Emergency: (63) 2311600073</p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Email Us</h4>
                        <p>General Inquiries: <a href="Ramon_magsaysay_memorial_colleges@stpeterhospital.com">Ramon_magsaysay_memorial_colleges@stpeterhospital.com</a><br>
                        Appointments: <a href="Msplaida@stpeterhospital.com">Msplaida@stpeterhospital.com</a></p>
                    </div>
                </div>
                
                <div class="contact-item">
                    <div class="contact-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="contact-text">
                        <h4>Opening Hours</h4>
                        <p>Monday - Friday: 8:00 AM - 8:00 PM<br>
                        Saturday: 9:00 AM - 5:00 PM<br>
                        Sunday: Emergency Only</p>
                    </div>
                </div>
            </div>
            
            <div class="contact-form">
                <h3>Send Us a Message</h3>
                <form id="contactForm">
                    <div class="form-group">
                        <label for="name">Full Name</label>
                        <input type="text" id="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject</label>
                        <input type="text" id="subject" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Your Message</label>
                        <textarea id="message" class="form-control" required></textarea>
                    </div>
                    
                    <button type="submit" class="btn">Send Message</button>
                </form>
            </div>
        </div>
        
        <div class="map-container">
            <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.9999999999995!2d125.1649139!3d6.1255584!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x32f79fb36d7e26c3%3A0xf4423a697beb0e59!2sRamon%20Magsaysay%20Memorial%20Colleges!5e0!3m2!1sen!2sph!4v1234567890123!5m2!1sen!2sph&markers=color:red%7C6.1255584,125.1871936&z=17" style="border:0; width:100%; height:100%;" allowfullscreen="" loading="lazy"></iframe>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2023 St. Peter Hospital. All Rights Reserved.</p>
    </footer>
    
    <script>
        document.getElementById('contactForm').addEventListener('submit', function(e) {
            e.preventDefault();
            alert('Thank you for your message! We will get back to you soon.');
            this.reset();
        });
        
        window.addEventListener('scroll', function() {
            const header = document.querySelector('header');
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
            
            animateOnScroll();
        });
                function animateOnScroll() {
            const elements = document.querySelectorAll('.contact-info, .contact-form, .map-container');
            
            elements.forEach(element => {
                const elementTop = element.getBoundingClientRect().top;
                const windowHeight = window.innerHeight;
                
                if (elementTop < windowHeight - 100) {
                    element.classList.add('visible');
                }
            });
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            if (window.scrollY > 50) {
                document.querySelector('header').classList.add('scrolled');
            }
            
            animateOnScroll();
        });
    </script>
</body>
</html>
