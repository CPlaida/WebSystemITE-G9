<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>St. Peter Hospital Management System</title>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    body {
        background: linear-gradient(120deg, #f0f8ff, #e6f7ff);
        overflow-x: hidden;
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
    }
    header.scrolled nav ul li a {
        color: #fff;
    }
    nav ul li a:hover {
        color: #ffdd57;
    }

    /* Hero */
    .hero {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        padding: 150px 50px;
        min-height: 100vh;
        background: linear-gradient(120deg, #f9fcff, #e3f7ff);
        animation: bgShift 10s infinite alternate ease-in-out;
    }
    @keyframes bgShift {
        0% { background-position: left; }
        100% { background-position: right; }
    }
    .hero-text {
        max-width: 550px;
        animation: fadeInLeft 1s ease forwards;
    }
    .hero-text small {
        color: #0077b6;
        font-weight: bold;
        font-size: 14px;
    }
    .hero-text h1 {
        font-size: 44px;
        margin: 15px 0;
        color: #222;
        line-height: 1.2;
    }
    .hero-text p {
        color: #555;
        font-size: 16px;
        margin-bottom: 25px;
    }
    .btn {
        background: #0077b6;
        color: white;
        padding: 12px 28px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: bold;
        box-shadow: 0 5px 15px rgba(0, 119, 182, 0.4);
        transition: all 0.3s ease;
    }
    .btn:hover {
        background: #005f8a;
        transform: scale(1.05);
        box-shadow: 0 10px 25px rgba(0, 95, 138, 0.5);
    }

    /* Hero Image */
    .hero-img {
        position: relative;
        width: 470px;
        height: 300px;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        animation: float 4s ease-in-out infinite;
    }
    .hero-img img {
        position: absolute;
        width: 100%;
        height: 100%;
        object-fit: cover;
        opacity: 0;
        transition: opacity 1s ease-in-out;
    }
    .hero-img img.active {
        opacity: 1;
    }
    @keyframes float {
        0%,100% { transform: translateY(0px); }
        50% { transform: translateY(-15px); }
    }

    /* About */
    .about {
        padding: 80px 50px;
        text-align: center;
        background: white;
        animation: fadeInUp 1.5s ease;
    }
    .about h2 {
        font-size: 32px;
        color: #0077b6;
        margin-bottom: 20px;
    }
    .about p {
        font-size: 18px;
        color: #555;
        max-width: 800px;
        margin: auto;
        line-height: 1.6;
    }

    /* Services */
    .services {
        background: #f0f8ff;
        padding: 80px 50px;
        text-align: center;
    }
    .services h2 {
        font-size: 32px;
        color: #0077b6;
        margin-bottom: 40px;
    }
    .service-container {
        display: flex;
        justify-content: center;
        flex-wrap: wrap;
        gap: 30px;
    }
    .service-card {
        background: rgba(255,255,255,0.8);
        backdrop-filter: blur(10px);
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        width: 280px;
        transition: 0.4s ease;
        transform: translateY(20px);
        opacity: 0;
    }
    .service-card.visible {
        transform: translateY(0);
        opacity: 1;
    }
    .service-card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 25px rgba(0, 119, 182, 0.3);
    }
    .service-card img {
        width: 60px;
        margin-bottom: 15px;
    }
    .service-card h3 {
        font-size: 1.4em;
        color: #0077b6;
        margin-bottom: 10px;
    }
    .service-card p {
        font-size: 0.95em;
        color: #555;
    }

    @keyframes fadeInLeft {
        from { opacity: 0; transform: translateX(-50px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
</head>
<body>

<header id="navbar">
    <div class="logo">St. Peter Hospital Inc.</div>
    <nav>
        <ul>
            <li><a href="<?= site_url('/') ?>" class="active">Home</a></li>
            <li><a href="<?= site_url('services') ?>">Services</a></li>
            <li><a href="<?= site_url('doctors') ?>">Doctors</a></li>
            <li><a href="<?= site_url('contact') ?>">Contact</a></li>
            <li><a href="<?= site_url('login') ?>">Login</a></li>
        </ul>
    </nav>
</header>

<section class="hero">
    <div class="hero-text">
        <small>Your Partner in Quality Healthcare</small>
        <h1>Innovating Hospital Services for a Healthier Future</h1>
        <p>
            Welcome to St. Peter Hospital Management System a next-generation platform that connects patients,
            doctors, and administrators in one seamless system. We streamline hospital operations, minimize waiting times,
            and provide powerful tools to help our medical teams focus on what truly matters: saving lives.
        </p>
        <a href="#about" class="btn">Explore HMS</a>
    </div>
    <div class="hero-img">
        <img src="http://www.rmmc.edu.ph/images/library-building-one.png" class="active" alt="Hospital 1">
        <img src="https://i.ytimg.com/vi/EQQclSX18Vg/maxresdefault.jpg" alt="Hospital 2">
        <img src="http://www.rmmc-mi.edu.ph/img/rmmc6.jpg" alt="Hospital 3">
        <img src="http://rmmc.edu.ph/images/dr._rex_bacarra_2024.jpg" alt="Hospital 4">
    </div>
</section>

<section id="about" class="about">
    <h2>About HMS</h2>
    <p>
        St. Peter Hospital Management System is built to transform healthcare operations.
        It brings together patients, doctors, and administrators into one connected platform,
        ensuring efficiency, transparency, and improved patient care.
        With cutting-edge technology, we aim to reduce waiting times, optimize workflows,
        and give healthcare professionals more time to focus on saving lives.
    </p>
</section>

<section id="services" class="services">
    <h2>Our Services</h2>
    <div class="service-container">
        <div class="service-card">
            <img src="https://cdn-icons-png.flaticon.com/512/2966/2966487.png" alt="Patient Management">
            <h3>Patient Management</h3>
            <p>Track patient records, medical history, and appointments efficiently.</p>
        </div>
        <div class="service-card">
            <img src="https://cdn-icons-png.flaticon.com/512/3209/3209265.png" alt="Doctor Scheduling">
            <h3>Doctor Scheduling</h3>
            <p>Organize doctors' shifts and availability in real time.</p>
        </div>
        <div class="service-card">
            <img src="https://cdn-icons-png.flaticon.com/512/1256/1256650.png" alt="Billing & Payments">
            <h3>Billing & Payments</h3>
            <p>Secure and fast billing system for patients and insurance processing.</p>
        </div>
    </div>
</section>

<script>
    window.addEventListener("scroll", function() {
        const navbar = document.getElementById("navbar");
        if (window.scrollY > 50) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }

        document.querySelectorAll(".service-card").forEach(card => {
            if (card.getBoundingClientRect().top < window.innerHeight - 50) {
                card.classList.add("visible");
            }
        });
    });

    let slideIndex = 0;
    const slides = document.querySelectorAll('.hero-img img');
    function showSlides() {
        slides.forEach(slide => slide.classList.remove('active'));
        slideIndex = (slideIndex + 1) % slides.length;
        slides[slideIndex].classList.add('active');
    }
    setInterval(showSlides, 3000);
</script>

</body>
</html>
